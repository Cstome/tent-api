<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;
use app\api\model\AdminUser as MainModel;
use think\facade\Request;

class Profile extends Api
{
    protected $checkAccessControl = false;
    
    protected $allowUpdateFields = ['nickname', 'mobile', 'email'];

    function initialize()
    {
        $this->model = new MainModel();
    }

    public function index()
    {
        $this->Auth();
        MainModel::where('id', $this->userInfo['id'])
            ->update([
                'last_login_time' => time(),
                'last_login_ip' => Request::ip()
            ]);

        // Remove password
        if(isset($this->userInfo['password'])) {
            unset($this->userInfo['password']);
        }

        return $this->responseData($this->userInfo);
    }

    public function read($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function save()
    {
        $data = input('post.');
        $id = $this->Auth()->getUserInfo()['id'];
        return $this->updateItem(['id' => $id], $data);
    }

    public function update($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function delete($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }
}