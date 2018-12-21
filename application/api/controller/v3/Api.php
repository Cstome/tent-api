<?php
/**
 * API BASE SYSTEM
 * Created: 2018-10-18 16:51:54 by icsd
 * Version: 3.1.0 Beta
 * Last Update: 2018-11-22 17:25:05
 */

namespace app\api\controller\v3;

use think\Controller;
use think\Response;
use app\api\controller\v3\oauth\Auth;

class Api extends Controller {

    //Defined the class main opeartion model.
    protected $model;

    //API access control toggle.
    protected $checkAccessControl = true;

    //Defined allow fields.
    protected $allowCreateFields = true;
    protected $allowUpdateFields = true;

    //Set allow search, setting by $this->searchFields($fields)
    protected $allowSearchFields;

    //!Deprecate! Fetch user info after call $this->auth()
    protected $userInfo;
    //The instance of Auth class, avaliable after call $this->auth()
    protected $authInstance;

    //Return last SQL in response body, enable by $this->getLastSql()
    protected $getLastSql = false;

    //Add debug info in response body, enable by $this->debug($debugInfo)
    protected $debug = [];

    //Return the response JSON, but not sent, enable by $this->fetchResponse()
    protected $fetchResponse = false;

    public function auth() {
        $this->authInstance = new Auth();
        $userInfo = $this->authInstance->getUserInfo();
        
        if (isset($userInfo['id'])) {
            $this->userInfo = $userInfo;
        } else {
            $this->responseData(['errMsg' => 'Unauthorized, login required.'], 401);
        }

        if ($this->checkAccessControl) {
            $allowAccess = $this->authInstance->checkAccessControl($userInfo['group']);
            !$allowAccess && $this->responseData(['errMsg' => 'Unauthorized operation.'], 403);
        }

        return $this->authInstance;
    }

    public function getLastSql() {
        $this->getLastSql = true;
        return $this;
    }

    /**
     * @param mixed $debugInfo
     * @return $this
     */
    public function debug($debugInfo) {
        array_push($this->debug, $debugInfo);
        return $this;
    }

    public function fetchResponse() {
        $this->fetchResponse = true;
        return $this;
    }

    /**
     * @param object $queryInst - ThinkPHP Db or Model instance.
     */
    public function bindModel($queryInst) {
        $this->model = $queryInst;
        return $this;
    }

    /**
     * Set search fields.
     * @param string $fields - Format like 'm.nickname|m.username|m.email|m.mobile'
     */
    public function searchFields($fields) {
        $this->allowSearchFields = $fields;
        return $this;
    }

    /**
     * getList - Return the list of MainModel
     * @return array
     */
    public function getList()
    {
        $pageSize = (input('get.pageSize')) ? (int)input('get.pageSize'):10;
        $page = (input('get.page')) ? (int)input('get.page'):1;

        // Add search condition
        $search = input('get.search');
        !empty($search && $this->allowSearchFields) &&  $this->model = $this->model->where($this->allowSearchFields, 'like', '%'.$search.'%');

        $list = $this->model->paginate($pageSize, false, [
            'page' => $page
        ]);

        //Convert object to array.
        $list = json_decode(json_encode($list), true);

        $res =[
            'totalCount' => $list['total'],
            'pageSize' => $pageSize,
            'page' => $page,
            'totalPage' => $list['last_page'],
            'list' => $list['data']
        ];

        return $this->responseData($res);
    }

    /**
     * @param array $data
     */
    public function createItem ($data) {

        $action = $this->model->allowField($this->allowCreateFields)->save($data);

        if($action == true){
            $res = ['result' => 'OK', 'id' => $this->model -> id];
            return $this->responseData($res,200);
        }else{
            $res = ['result' => false, 'errMsg' => $this->model -> getDbError()];
            return $this->responseData($res,500);
        }
    }

    public function getItem ($where = []) {

        $action = $this->model->where($where)->find();

        if ($action) {
            return $this->responseData($action,200);
        } elseif ($action == null) {
            $res = ['result' => false, 'errMsg' => 'Item not exist.'];
            return $this->responseData($res,404);
        } else {
            $res = ['result' => false, 'errMsg' => $action->getDbError()];
            return $this->responseData($res,500);
        }
    }

    public function updateItem ($where, $data) {

        $action = $this->model->allowField($this->allowUpdateFields)->save($data, $where);

        $res = ['result' => 'OK', 'updateCount' => $action];
        return $this->responseData($res,200);
    }

    public function deleteItem ($where, $isTrue = false) {

        if ($isTrue) {
            $action = $this->model->where($where)->delete();
        } else {
            $action = $this->model->save(['status' => -1], $where);
        }

        if ($action === false) {
            $res = ['result' => false, 'errMsg' => $action->getDbError()];
            return $this->responseData($res,500);
        } else {
            $res = ['result' => 'OK', 'deleteCount' => $action];
            return $this->responseData($res,200);
        }
    }

    /**
     * all request will call this function to return the data to client.
     * @param array $array - return json body
     * @param int $code - HTTP Status Code
     * @param array $header
     */
    public function responseData($array, $code = 200, $header = []) {
        if ($this->getLastSql == true) {
            $array['lastSql'] = $this->model->getLastSql();
        }

        if ($this->debug != []) {
            $array['debugInfo'] = $this->debug;
        }

        $result = $this->fetchResponse ? $array : $this->sentData($array, $code, $header);

        //Reset fetch response status.
        $this->fetchResponse = false;

        return $result;
    }

    /**
     * sentData - sent the response body and exit the program.
     * @param array $array
     * @param int $code
     * @param array $header
     */
    public function sentData($array, $code = 200, $header = []) {

        http_response_code($code);

        $header['Content-Type'] = 'application/json; charset=utf-8';

        header('Access-Control-Allow-Origin:*');

        foreach ($header as $name => $val) {
            if (is_null($val)) {
                header($name);
            } else {
                header($name . ':' . $val);
            }
        }

        exit(json_encode($array,JSON_UNESCAPED_UNICODE));

    }
}