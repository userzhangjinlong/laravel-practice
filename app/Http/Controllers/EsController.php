<?php


namespace App\Http\Controllers;


use App\Model\MysqlModel\ProductsModel;
use App\Utils\EsUtils;
use Illuminate\Http\Request;

class EsController extends Controller
{
    /**
     * @var EsUtils
     */
    private $esUtil;

    public function __construct()
    {
        $this->esUtil = new EsUtils();
    }

    /**
     * 设置数据库
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function setIndex(Request $request)
    {
        //创建索引文本
        $body = [
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0
            ],
            'mappings' => [
                '_source' => [
                    'enabled' => true
                ],
                'properties' => [
                    'id' => [
                        'type' => 'long'
                    ],
                    'type' => [
                        'type' => 'text',
                        'analyzer' => 'ik_max_word',
                        'search_analyzer' => 'ik_smart'
                    ],
                    'title' => [
                        'type' => 'text',
                        'analyzer' => 'ik_max_word',
                        'search_analyzer' => 'ik_smart'
                    ],
                    'long_title' => [
                        'type' => 'text',
                        'analyzer' => 'ik_max_word',
                        'search_analyzer' => 'ik_smart'
                    ],
                    'description' => [
                        'type' => 'text',
                        'analyzer' => 'ik_max_word',
                        'search_analyzer' => 'ik_smart'
                    ]
                ],
            ]
        ];
        $res = $this->esUtil->buildIndex('goods', $body);

        return response($res, 200);
    }

    /**
     * 批量添加/编辑文档
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addEsData()
    {
//        $productModel = new ProductsModel();
        // 初始化请求体
        $req = ['body' => []];
        $products = ProductsModel::get(['id','type','title','long_title','description'])->toArray();
        // 遍历商品
        foreach ($products as $data) {
            // 将商品模型转为 Elasticsearch 所用的数组

            $req['body'][] = [
                'index' => [
                    '_index' => 'goods',
                    '_id'    => $data['id'],
                ],
            ];
            $req['body'][] = $data;
        }
        try {
            // 使用 bulk 方法批量创建
            $res =$this->esUtil->createMultiData($req);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }

        return response($res, 200);
    }

    /**
     * 新增/编辑文档
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addOneData()
    {
        $products = ProductsModel::where('id', 2)->first(['id','type','title','long_title','description'])->toArray();
        $data = [
            'index' => 'goods',
            'id' => $products['id'],
            'body' => $products,
        ];

        $res =$this->esUtil->createOneData($data);

        return response($res, 200);
    }

    /**
     * 删除单条文档
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteData()
    {
        $where = [
            'index' => 'goods',
            'id' => 1,
        ];

        $res =$this->esUtil->deleteEsData($where);

        return response($res, 200);
    }

    /**
     * 批量删除多条文档
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteMultiEsData()
    {
        $where['body'][] = [
            'delete' => [
                '_index' => 'goods',
                '_id'    => 1,
            ],
        ];
        $where['body'][] = [
            'delete' => [
                '_index' => 'goods',
                '_id'    => 2,
            ],
        ];

        $res =$this->esUtil->deleteMultiEsData($where);

        return response($res, 200);
    }


    /**
     * 获取指定一个值
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getFilterData()
    {

        $res =$this->esUtil->find('goods', ['id' => 1],'id,title');

        return response($res, 200);
    }


    /**
     * 关键词分页搜索
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getFilterPageData()
    {
        $res =$this->esUtil->searchKeywordsPageData('goods', [],'id,title', 0, 20, '影驰 Gamer DDR4-2133 8G ');

        return response($res, 200);
    }

}
