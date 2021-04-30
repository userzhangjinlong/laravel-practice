<?php


namespace App\Model\MysqlModel;


use Illuminate\Database\Eloquent\Model;

class MqPracticeModel extends Model
{
    protected $table = 'mq_practice';

    protected $fillable = ['test_id','test_name'];

    public $timestamps = false;

}
