<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;
use app\api\model\AdminConfig as MainModel;

class Config extends Api
{
    function initialize()
    {
        $this->model = new MainModel();
    }

    public function index()
    {
        $this->model->where('status', 1)->order('updatedAt desc');
        return $this->getList();
    }

    public function read($id)
    {
        return $this->getItem(['name' => $id]);
    }

    public function save()
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    /**
     * @param string $id - conofig_name
     */
    public function update($id)
    {
        $putValue = input('put.value');
        is_array($putValue) && $putValue = json_encode($putValue);
        return $this->updateItem(['name' => $id], ['value' => $putValue]);
    }

    public function delete($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }
}