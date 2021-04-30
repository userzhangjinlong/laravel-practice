<?php


namespace App\Http\Controllers;


use App\Enum\EnumMqExchangeType;
use App\Utils\MqUtils;
use Illuminate\Http\Request;

class MqController extends Controller
{
    public function testQueue(Request $request)
    {
        $mqUtils = new MqUtils();
        //生产者发送消息给mq
        $body = ['test_name' => rand(100000, 999999)];
        $queueName = 'mq-test';
        $exchangeName = 'mq-test';
        $res = $mqUtils->manualPush($exchangeName,$queueName, json_encode($body),EnumMqExchangeType::TYPE_DIRECT);

        return $res;
    }

    public function mqConsumer()
    {
        $mqUtils = new MqUtils();
        //消费者消费消息
        $queueName = 'mq-test';
        $res = $mqUtils->autoAckConsumer($queueName);

        return $res;
    }
}
