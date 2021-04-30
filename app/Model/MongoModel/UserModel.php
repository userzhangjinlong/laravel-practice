<?php


namespace App\Model\MongoModel;


use Jenssegers\Mongodb\Eloquent\Model as MongoModel;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class UserModel extends MongoModel
{
    use SoftDeletes;
    protected $connection = 'mongodb';  //库名 定义连接 默认mysql 这儿使用mongodb
    protected $collection = 'user';    //文档名

    public function insertOne()
    {

    }

}
