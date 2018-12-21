<?php

namespace app\api\utils\Admin;

class Sidemenu
{
    /**
     * Create Auth Menu Tree
     * @param array $menuTree - System side menu tree
     * @param array $authMenuId - Auth menu id of group.
     */
    static function createAuthMenuTree($menuTree, $authMenuId)
    {
        $checkedMenuTree = [];
        foreach($menuTree as $i => $v) {
          if(isset($v['children']) && count($v['children']) > 0) {
            // If has children, loop children.
            $children = self::createAuthMenuTree($v['children'], $authMenuId);
            // If has checked children, set checked children as children of parent,
            // push parent to new menu tree.
            if(count($children) > 0) {
              $v['children'] = $children;
              array_push($checkedMenuTree, $v);
            }
          } else {
            // If no children, check if node id in checkedKey list.
            if (in_array($v['id'], $authMenuId)) {
              array_push($checkedMenuTree, $v);
            }
          }
        }
        return $checkedMenuTree;
    }
}