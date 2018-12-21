<?php

namespace app\api\controller\v3\oauth;

use think\facade\Request;
use app\api\controller\v3\Api;
use app\api\controller\v3\oauth\Auth;
// use app\api\model\AdminConfig as MainModel;

class Token extends Api
{
    //function initialize()
    //{
    //    $this->model = new MainModel();
    //}

    public function index($grant_type = null, $username = null, $password = null, $client_id = null, $scope = "read")
    {
        $auth = new Auth();
        switch($grant_type) {
            case 'password': 
            $result = $auth->getTokenByPassword($username, $password, $client_id, $scope);
            break;
            default: $this->sentData([
                'error' => 'invalid_grant',
                'errMsg' => 'Bad grant type or not set.'
            ], 400);
            break;
        }
        
        $statusCode = isset($result['access_token']) ? 200 : 400;
        return $this->responseData($result, $statusCode);
    }

    public function read($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function save()
    {
        $post = input('post.');

        $auth = new Auth();
        switch($post['grant_type']) {
            case 'password': 
            $result = $auth->getTokenByPassword($post['username'], $post['password'], $post['client_id'], $post['scope']);
            break;
            default: $this->sentData(['errMsg' => 'Bad grant type or not set.'], 400);
            break;
        }
        
        $statusCode = isset($result['access_token']) ? 200 : 400;
        return $this->responseData($result, $statusCode);
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