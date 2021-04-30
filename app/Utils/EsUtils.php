<?php


namespace App\Utils;


use Elasticsearch\ClientBuilder;
use http\Encoding\Stream;

class EsUtils
{
    /**
     * @var ClientBuilder
     */
    private $es;

    public function __construct()
    {
        if ($this->es instanceof ClientBuilder){
            return $this->es;
        }
        $this->es = new ClientBuilder();
        return  $this->es;
    }

    /**
     * 建立es连接
     * @return \Elasticsearch\Client
     */
    public function buildConnect()
    {
        return $this->es->create()->setHosts(config('database.elasticsearch.hosts'))->build();
    }

    /**
     *建立es Index（即es数据库）
     * @param string $indexName
     * @param array $body
     * @return array|void
     */
    public function buildIndex(string $indexName, array $body)
    {
        $hasIndex = $this->buildConnect()->indices()->exists(['index' => $indexName]);
        if ($hasIndex){
            return ;
        }

        return $this->buildConnect()->indices()->create([
            'index' => $indexName,
            'body' => $body
        ]);
    }

    /**
     * 新增或批量编辑es数据
     * @param string $indexName
     * @param array $data
     * @return array
     */
    public function createMultiData(array $data)
    {
        return $this->buildConnect()->bulk($data);
    }

    /**
     * 新增或修改单条数据
     * @param array $data
     * @return array|callable
     */
    public function createOneData(array $data)
    {
        return $this->buildConnect()->index($data);
    }

    /**
     * 删除单条数据
     * @param array $where
     * @return array|callable
     */
    public function deleteEsData(array $where)
    {
        return $this->buildConnect()->delete($where);
    }

    /**
     * 批量删除多条数据
     * @param array $where
     * @return array|callable
     */
    public function deleteMultiEsData(array $where)
    {
        return $this->buildConnect()->bulk($where);
    }

    /**
     * 获取es单条数据
     * @param string $indexName
     * @param array $filter
     * @return array|callable
     */
    public function find(string $indexName, array $filter, string $field = '')
    {
        $where = [
            'index' => $indexName,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['match' => $filter],
                        ],
                    ],
                ],
                "_source" => !empty($field) ? explode(',', $field) : [], //筛选指定值

            ],
        ];

        $data = $this->buildConnect()->search($where);
        if (!empty($data)){
           $result = [
               'count' => $data['hits']['total']['value'],
               'data' => $data['hits']['hits'][0]['_source'],
           ];
        }else{
            $result = [
                'count' => 0,
                'data' => [],
            ];
        }

        return $result;
    }

    /**
     * 查询指定条件数据(一般用于关键字搜索)
     * @param array $where
     * @return array|callable
     */
    public function searchKeywordsPageData(string $indexName, array $filter, string $field='', int $page=0, int $num = 20, string $keywords)
    {
        $where = [
            'index' => $indexName,
            'from' => $page,   ##从第二个开始
	        'size' => $num,    ##返回2个
            'body' => [
                'query' => [
                    'bool' => [
//                        'filter' => [
//                            ['match' => $filter],
//                        ],
                        'must' => [
                            'multi_match' => [
                                'query'  => $keywords,
                                'fields' => [
                                    'title^3',
                                    'long_title^2',
                                    'description',
                                ],
                            ],
                        ],

                    ],

                ],

                "_source" => !empty($field) ? explode(',', $field) : [], //筛选指定值
                'sort' => ['id' => 'desc'],//排序

            ],
        ];
        return $this->buildConnect()->search($where);
    }

}
