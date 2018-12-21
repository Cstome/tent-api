<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;
use app\api\model\AdminGroupApiAuth as MainModel;
use think\Validate;

class ApiAuth extends Api
{
    function initialize()
    {
        $this->bindModel(new MainModel());
    }

    public function index($apiId, $groupId)
    {
        return $this->getItem(['api_id' => $apiId, 'group_id' => $groupId]);
    }

    public function read($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function save()
    {
        $post = input('post.');
        $auth = $post['authStatus'];

        $where = [
            'api_id' => $post['api_id'],
            'group_id' => $post['group_id'],
        ];

        $validate = $this->validData();
        $validResult = $validate->check($where);
        if(!$validResult) {
            $this->responseData(['errMsg' => $validate->getError()], 400);
        }

        $data = [
            'api_id' => $post['api_id'],
            'group_id' => $post['group_id'],
            'GET' => $auth['GET'],
            'POST' => $auth['POST'],
            'PUT' => $auth['PUT'],
            'DEL' => $auth['DEL'],
        ];

        $isAuth = $this->model->where($where)->find();

        if($isAuth) {
            $this->model->save($data, $where);
        } else {
            $this->model->save($data);
        }

        return $this->getLastSql()->responseData(['status' => 'OK']);
    }

    public function update($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function delete($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    private function validData() {
        $rule = [
            'api_id' => 'require|number',
            'group_id' => 'require|number',
        ];

        $msg = [
            'api_id.require' => 'API ID 不能为空',
            'group_id.require'     => '授权用户组不能为空',
            'api_id.number' => 'API ID 格式有误',
            'group_id.number'     => '授权用户组ID格式有误'
        ];

        return Validate::make($rule)->message($msg);
    }
}