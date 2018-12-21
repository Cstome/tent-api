# TentSYS v3 Developments Document

> Created: 2018-11-22 13:43:17 by Cstom
>
> Version: 3.1.1
>
> Last Update: 2018-11-22 13:43:56

## Project Description
A RESTful API system base ThinkPHP.

- API Authorization
- Resource ORM
- RESTful style practict
- Additional debug info

## Module dependencies
ThinkPHP v5.1.27 (LTS)

## Get Started

```sh
# After cloned the project:

# Install dependencies
$ composer install

# Quick start PHP build-in http server and specific ./public/ as document root directory.
$ php -S 0.0.0.0:80 -t ./public/
```

## Directory

~~~
www  WEB部署目录（或者子目录）
├─application                     应用目录 （可修改，模块功能主要在该目录开发）
│  ├─common                       公共模块目录（可以更改）
│  ├─api                          API 模块目录
│  │  ├─common.php                模块函数文件
│  │  ├─controller                API控制器，以目录作版本控制
│  │  │  ├─v2                     v2 版本API（deprecate）
│  │  │  └─v3                     v3 版本API
│  │  │    ├─Api.php              v3 版本API基类，所有API继承该类
│  │  │    ├─oauth                Oauth认证模块
│  │  │    │  ├─Auth.php          API权限认证模块
│  │  │    │  └─Token.php         Access Token 生成与验证模块
│  │  │    └─module               API模块目录，不同模块分目录存放，目录名称使用小写加下划线
│  │  │       └─controller.php    API资源控制器，以PascalCase命名
│  │  └─model                     模型目录，所有模型存放在该目录下，模型直接继承 `think\Model` 
└─ ...                            其他目录同ThinkPHP一致
~~~

> 目录及文件命名规范遵照ThinkPHP的规范。

## Route and URL

The route in `{root}/route/route.php`  had register a global route rule: 

```php
Route::resource('api/:version/:module/:controller','api/:version.:module.:controller');
```

You can visit link `{host}/api/v3/module/controller` by `GET` or `POST` method, or visit link `{host}/api/v3/module/controller/:id` by `PUT` or `DELETE` method.

### Method and Route Relationship of Resource Controller

| Request Method | Route Rule    | Related method in controller |
| -------------- | ------------- | ---------------------------- |
| GET            | blog          | index                        |
| GET            | blog/create   | create                       |
| POST           | blog          | save                         |
| GET            | blog/:id      | read                         |
| GET            | blog/:id/edit | edit                         |
| PUT            | blog/:id      | update                       |
| DELETE         | blog/:id      | delete                       |

TentSYS using Resource Route of ThinkPHP as resource mapping, see the details about using resource route in [资源控制器](https://www.kancloud.cn/manual/thinkphp5_1/353984) .

## Controller

All API controller extend `app\api\controller\v3\Api` .

### API Authorization

The API base class `app\api\controller\v3\Api`  has included `Auth` class `app\api\controller\v3\oauth\Auth` . API doesn't check authorization by default, you just need to call `$this->auth()` to check api authorization and get the user info. 

You can put `$this->auth()` in the method you need: 

```php
public function index()
{
    $this->Auth();
    return $this->responseData($this->Auth()->getUserInfo());
}
```

Or if all method required authorization, you can put it in `initialize` method of controller:

```php
function initialize()
{
    $this->Auth();
}
```

 `$this->auth()` will return an instance of Auth class, you can get user info by:

```php
$this->Auth()->getUserInfo(); //Array return
```

When access token is invalid(authorize fail) , system will respond an error with status code `401`: 

```json
{
    "errMsg": "Unauthorized, login required."
}
```

Authorization process will check access control by default, you can disable it by declare in API property:

```php
<?php
namespace app\api\controller\v3\admin;
use app\api\controller\v3\Api;
class User extends Api
{
    //Disable access control checking.
    protected $checkAccessControl = false;
}
```

If the user can not access the API, system will respond an error with status code `403` : 

```json
{
    "errMsg": "Unauthorized operation."
}
```

### Defined Main Model (Deprecated)

> This method has deprecated.
>
> The model of ThinkPHP 5.1 return an instance of each chain operation([链式操作](https://www.kancloud.cn/manual/thinkphp5_1/354005)), the method above was unavailable, using `bindModel` replace.

```php
<?php
namespace app\api\controller\v3\admin;
use app\api\controller\v3\Api;
use app\api\model\AdminGroup as MainModel; //Require a MainModel

class Group extends Api
{
    function initialize()
    {
        $this->model = new MainModel(); //Defined a MainModel
    }

    public function index()
    {
        //Case in ThinkPHP 5
        $this->model->where('status', 1); //Operate MainModel
        //Case in ThinkPHP 5.1
        $this->model = $this->model->where('status', 1);
        
        return $this->getList();
    }
    
    //... Any other method...
}
```

### Bind Main Model

Operating properties of controller class directly is not standard and elegance.  Use `bindModel` method to set main model for resource operation.

```php
<?php

namespace app\api\controller\v3\jobdata;
use app\api\controller\v3\Api;
use app\api\model\JobData as MainModel;

class Job51 extends Api
{
    function initialize()
    {
        $this->bindModel(new MainModel()); //Bind a mian model instances.
    }

    public function index()
    {
        //You can still operate main model by $this->model
        $query = $this->model->order('updatedAt desc');
        //Or using another model:
        $query = new OtherModel();
        
        //The target resource model depend on the latest bind Model.
        return $this->bindModel($query)->getList();
    }
    
    //... Any other method...
}
```

### Set Allow Fields

You can declare `$allowCreateFields` and `$allowUpdateFields` in each API class. Those two properties is default with value `true` , and it's recommend to set it for each API to keep system security.

```php
<?php

namespace app\api\controller\v3\admin;

use app\api\controller\v3\Api;

class User extends Api
{
    protected $allowCreateFields = ['username', 'password'];
    protected $allowUpdateFields = ['password', 'nickname', 'group'];
}
```

## Database

Each database has those 4 fields by default:

- `id` 
- `status` 0: disable, 1: normal, -1: deleted
- `create_time` int unixtimetamp
- `update_time` int unixtimetamp

Example:

```sql
CREATE TABLE `admin_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `some_field` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `create_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Reserved Params

Some keyword is reserved for url query string.

#### List API

- `page` int
- `pageSize` int
- `search` string

## License

MIT