<?php

namespace app\api\controller\v3\oauth;

use app\api\controller\v3\Api;
use app\api\model\AdminUser;

class Password extends Api
{
    protected $checkAccessControl = false;

    public function index()
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function read($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function save()
    {
        $data = input('post.');
        $id = $this->Auth()->getUserInfo()['id'];
        
        $userInfo = AdminUser::get($id);

        if($userInfo['password'] == $data['origin_passwd']) {
            $userInfo->password = $data['new_passwd'];
            $userInfo->save();
            return $this->responseData(['result' => 'OK']);
        } else {
            return $this->responseData(['result' => false, 'errMsg' => 'Origin password error.'], 400);
        }
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