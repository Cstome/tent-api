<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        return json([
            'name' => 'TentSYS',
            'version' => '3.0.2',
            'desc' => 'Powerful REST API system.'
        ]);
    }
}
