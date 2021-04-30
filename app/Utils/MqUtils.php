<?php


namespace App\Utils;


use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use App\Model\MysqlModel\MqPracticeModel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MqUtils
{
    /**
     * @var AMQPStreamConnection
     */
    private $mqConnect;

    public function __construct()
    {

        if ($this->mqConnect instanceof AMQPStreamConnection){
            return $this->mqConnect;
        }

        $this->mqConnect = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));

        return  $this->mqConnect;
    }

    public function getInstance()
    {
        // RabbitMQ 连接实例
        return $this->mqConnect;
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }

    /**
     * 生产者发布消息
     * @param string|string $exchangeName
     * @param string $queueName
     * @param string $body
     * @param string|string $exchangeType
     * @return string
     * @throws \Exception
     */
    public function manualPush(string $exchangeName = 'default', string $queueName, string $body, string $exchangeType = 'direct')
    {
        try {
            // 创建通道
            $channel = $this->getInstance()->channel();
            /**
             * 创建交换机(Exchange)
             * name: vckai_exchange// 交换机名称
             * type: direct        // 交换机类型，分别为direct/fanout/topic，参考另外文章的Exchange Type说明。
             * passive: false      // 如果设置true存在则返回OK，否则就报错。设置false存在返回OK，不存在则自动创建
             * durable: false      // 是否持久化，设置false是存放到内存中的，RabbitMQ重启后会丢失
             * auto_delete: false  // 是否自动删除，当最后一个消费者断开连接之后队列是否自动被删除
             */
            $channel->exchange_declare($exchangeName, 'direct', false, false, false); //声明初始化交换机
            /**
             * 创建队列(Queue)
             * name: hello         // 队列名称
             * passive: false      // 如果设置true存在则返回OK，否则就报错。设置false存在返回OK，不存在则自动创建
             * durable: true       // 是否持久化，设置false是存放到内存中的，RabbitMQ重启后会丢失
             * exclusive: false    // 是否排他，指定该选项为true则队列只对当前连接有效，连接断开后自动删除
             *  auto_delete: false // 是否自动删除，当最后一个消费者断开连接之后队列是否自动被删除
             */
            $channel->queue_declare($queueName, false, true, false, false); //声明初始化一条队列
            // 绑定消息交换机和队列
            $channel->queue_bind($queueName, $exchangeName, 'mq-test'); //将队列与某个交换机进行绑定，并使用路由关键字 可以为空

            /**
             * 创建AMQP消息类型
             * delivery_mode 消息是否持久化
             * AMQPMessage::DELIVERY_MODE_NON_PERSISTENT  不持久化
             * AMQPMessage::DELIVERY_MODE_PERSISTENT      持久化
             */
            $msg = new AMQPMessage($body,
                [
                    'content_type' => 'text/plain',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ]
            );
            /**
             * 发送消息
             * msg: $msg                // AMQP消息内容
             * exchange: vckai_exchange // 交换机名称
             * queue: hello             // 队列名称
             */
            $channel->basic_publish($msg, $exchangeName, $queueName);


        } catch (\Exception $e) {
            return $e->getMessage();
        }

        $channel->close();
        $this->getInstance()->close();


        return 'success';
    }

    /**
     * @param string $queueName
     * @return string
     */
    public function autoAckConsumer(string $queueName)
    {
        try
        {
            $channel = $this->getInstance()->channel();


            $channel->queue_declare($queueName, false, true, false, false);

            echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

            //回调函数处理消息逻辑
            $callback = function($msg){
                echo " [x] Received ", $msg->body, "\n";
                sleep(substr_count($msg->body, '.'));
                echo " [x] Done", "\n";
                //此前处理业务逻辑监听如果没有失败再ack 否则做消息补偿机制待研究
                $user = new MqPracticeModel();
                $user->insert(['test_name' => rand(100000, 999999)]);

                if ($msg->body === 'quit') {
                    //队列取消
                    $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
                    //重回队列 自己手动加测试的
//                    $msg->delivery_info['channel']->basic_nack($msg->delivery_info['consumer_tag']);
                }
                //此步骤ack掉之后消息队列才会被消费删除 // 手动确认ack，确保消息已经处理
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

            };

            // 设置消费者（Consumer）客户端同时只处理一条队列
            // 这样是告诉RabbitMQ，再同一时刻，不要发送超过1条消息给一个消费者（Consumer），直到它已经处理了上一条消息并且作出了响应。这样，RabbitMQ就会把消息分发给下一个空闲的消费者（Consumer）。
            $channel->basic_qos(null, 1, null);
            /**
             * queue: hello               // 被消费的队列名称
             * consumer_tag: consumer_tag // 消费者客户端身份标识，用于区分多个客户端
             * no_local: false            // 这个功能属于AMQP的标准，但是RabbitMQ并没有做实现
             * no_ack: true               // 收到消息后，是否不需要回复确认即被认为被消费 no_ack true代表自动确认（ack）  false代表需要ack
             * exclusive: false           // 是否排他，即这个队列只能由一个消费者消费。适用于任务不允许进行并发处理的情况下
             * nowait: false              // 不返回执行结果，但是如果排他开启的话，则必须需要等待结果的，如果两个一起开就会报错
             * callback: $callback        // 回调逻辑处理函数
             */
            $channel->basic_consume($queueName, '', false, false, false, false, $callback);

            while(count($channel->callbacks)) {
                $channel->wait();
            }

            $channel->close();
            $this->getInstance()->close();
        }catch(\Exception $e)
        {
            echo $e->getMessage();
        }

        return 'successs';

    }

}
