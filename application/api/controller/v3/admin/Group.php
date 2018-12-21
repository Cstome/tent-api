<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;
use app\api\model\AdminGroup as MainModel;

class Group extends Api
{
    function initialize()
    {
        $this->Auth();
        $this->model = new MainModel();
    }

    public function index()
    {
        $this->model->where('status', 1);
        return $this->getList();
    }

    public function read($id)
    {
        return $this->getItem(['id' => $id]);
    }

    public function save()
    {
        $post = input('post.');
        return $this->createItem($post);
    }

    public function update($id)
    {
        $data = input('put.');
        return $this->updateItem(['id' => $id], $data);
    }

    public function delete($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }
}