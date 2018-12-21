<?php
/**
 * API BASE SYSTEM
 * Authorization Class
 * Created: 2018-10-22 15:55:52 by icsd
 * Version: 3.0.2
 * Last Update: 2018-11-16 10:05:46
 */

namespace app\api\controller\v3\oauth;

use think\facade\Request;
use \app\api\model\AdminUser;
use \app\api\model\AdminAuthLog;
use \app\api\model\AdminApi;

class Auth
{
    protected $tokenExpireTime = 86400 * 10; // 10 days

    protected $userInfo;
    
    public function getUserInfo() {

        //Return user info directly if token has checked.
        if($this->userInfo) {
            return $this->userInfo;
        }

        $token = $this->getRequestToken();

        if(!$token) {
            return false;
        }

        $AdminAuthLog = new AdminAuthLog();
        $userInfo = $AdminAuthLog
        ->alias('a')
        ->join(['admin_user'=>'u'], 'a.user_id=u.id', 'LEFT')
        ->where('a.access_token', $token)
        ->where('a.status', 1)
        ->where('a.create_time', '>', time() - $this->tokenExpireTime)
        ->field('u.*, a.access_token')
        ->find();

        $this->userInfo = $userInfo;

        return $userInfo;
    }

    public function checkAccessControl($groupId) {
        $routeInfo = Request::route();
        $method = (Request::method() == 'DELETE') ? 'DEL' : Request::method();

        $apiAccess = AdminApi::alias('a')
        ->join(['admin_group_api_auth'=>'b'], ['b.api_id=a.id', 'b.group_id='.$groupId], 'LEFT')
        ->where([
            'a.version' => $routeInfo['version'],
            'a.module' => $routeInfo['module'],
            'a.controller' => $routeInfo['controller'],
        ])
        ->field('a.*, b.'.$method.' as check_method')
        ->find();
        
        if($apiAccess && $apiAccess['check_method'] == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getTokenByPassword($username = null, $password = null, $client_id = null, $scope = "read") {
        $userInfo = AdminUser::where(['username'=>$username, 'password'=>$password])->find();
        $authLog = [
            'username'  =>  $username,
            'client_id' => $client_id,
            'request_ip' => Request::ip(),
            'scope' => $scope
        ];
        
        if($userInfo) {
            $res = $this->createToken($userInfo, $client_id, $scope);
            $authLog['access_token'] = $res["access_token"];
            $authLog['user_id'] = $userInfo['id'];
        } else {
            $authLog['password'] = $password;
            $res = [
                'errMsg' => 'Invalid username or password.'
            ];
        }

        AdminAuthLog::create($authLog);
        return $res;
    }

    public static function logout() {
        $token = self::getRequestToken();

        if(!$token) {
            return false;
        }
        
        AdminAuthLog::update(['status'  => -1], ['access_token' => $token]);
    }

    private function createToken($userInfo, $client_id = null, $scope = "read") {
        $hashStr = json_encode($userInfo).rand().'加盐'.time();
        $token = sha1($hashStr);

        $res = [
            "access_token" => $token,
            "token_type" => "Bearer",
            "expires_in" => $this->tokenExpireTime,
            "refresh_token" => ""
        ];

        return $res;
    }

    private static function getRequestToken() {
        // Getting header by ThinkPHP will check apache_request_headers() first, but
        // Authorization field doesn't exist in apache_request_headers() in some case.
        // We need to get Authorization header manual, not using ThinkPHP method.
        //$Authorization = Request::header('Authorization');

        // You may need to add 'SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1'
        // to .htaccess file in some apache base server.
		if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$Authorization = $_SERVER['HTTP_AUTHORIZATION'];
		}else if(isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
			$Authorization = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
		}else {
			return false;
        }
        
        if (strlen($Authorization) == 40) {
            return $Authorization;
        } elseif (substr($Authorization, 0, 7) == 'Bearer ') {
            return substr($Authorization, 7);
        } else {
            return false;
        }
    }
}