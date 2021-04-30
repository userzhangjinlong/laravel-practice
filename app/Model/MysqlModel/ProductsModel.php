<?php


namespace App\Model\MysqlModel;


use Illuminate\Database\Eloquent\Model;

class ProductsModel extends Model
{
    protected $table = 'products';

    protected $fillable = ['type','category_id','title','long_title','description','image','on_sale','rating','sold_count','review_count','price'];

    /**
     * 定义es搜索需要的字段存入
     * @return array
     */
    public function toEsArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'long_title' => $this->long_title,
            'description' => $this->description
        ];
    }

}
