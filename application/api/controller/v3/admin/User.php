<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;
use app\api\model\AdminUser as MainModel;

class User extends Api
{
    function initialize()
    {
        $this->Auth();
        $this->model = new MainModel();
    }

    public function index()
    {
        $query = $this->model->field('id, uid, username, group, last_login_ip, last_login_time, nickname, status, create_time, update_time');
        return $this->bindModel($query)->getList();
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