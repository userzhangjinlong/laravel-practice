<?php


namespace App\Http\Controllers;


use App\Model\MongoModel\UserModel;

class MongodbController extends Controller
{

    public function insert()
    {

    }

    public function mongoDo()
    {
        $userData = UserModel::all()->toArray();
//        $userData = UserModel::create(['id' => 1,'title' => 'The Fault in Our Stars']);
//        $userData = UserModel::where('id', 1)->update(['title' => '修改为最新值']);
//        $userData = UserModel::where('id', 1)->delete();

        return response($userData, 200);
    }
}
