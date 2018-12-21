<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;
use app\api\utils\Admin\Sidemenu as SidemenuUtils;
use app\api\model\AdminGroup as MainModel;

class Sidemenu extends Api
{
    protected $checkAccessControl = false;
    
    function initialize()
    {
        $this->auth();
        $this->model = new MainModel();
    }

    public function index()
    {
        $groupId = $this->userInfo['group'];

        $menuInfo = $this->model
        ->alias('a')
        ->join(['admin_config'=>'c'], 'c.name="side_menu"', 'LEFT')
        ->where('a.id', $groupId)
        ->field('a.*, c.value')
        ->find();

        // Get auth menu id and all menu tree.
        $auth_menu = json_decode($menuInfo['auth_menu'], true);
        $menu_tree = json_decode($menuInfo['value'], true)['menuTree'];

        $authMenuTree = SidemenuUtils::createAuthMenuTree($menu_tree, $auth_menu);

        return $this->responseData($authMenuTree);
    }

    public function read($id)
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
    }

    public function save()
    {
        return $this->sentData(['errMsg' => 'Method Not Allowed'], 405);
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