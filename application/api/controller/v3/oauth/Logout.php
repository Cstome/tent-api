<?php

namespace app\api\controller\v3\oauth;

use app\api\controller\v3\Api;
use app\api\controller\v3\oauth\Auth;

class Logout extends Api
{
    public function index()
    {
        Auth::Logout();
        return $this->responseData(['status' => 'OK']);
    }

    public function read($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function save()
    {
        Auth::Logout();
        return $this->responseData(['status' => 'OK']);
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