# sync-login
写在最前面，之前帮忙写个第三方登录类，找了下开源项目，好像没有好的类库正常调用使用，所以将自己修改的ThinkOauth更新到这里。  


这是一个第三方登录类，可以对sdk的使用情况，进行封装多种第三方授权登录

## 目录 
> **[Composer安装](#composer安装)**  
> **[key密钥配置](#key密钥配置)**  
> **[获取登录跳转授权地址](#获取登录跳转授权地址)**  
> **[授权后回调处理](#授权后回调处理)**  
> **[Example完整Demo](#Example完整Demo)**  
> **[目前已测试通过](#目前已测试通过)**  
> **[联系作者](#联系作者)**

## Composer安装
``` base
$ composer require liaodeity/sync-login
```

## key密钥配置
配置名称必须大写，名称与src/sdk的类名相同  
如src/sdk/QqSDK.class.php的配置名前缀为QQ  

``` php
return [
    'QQ_KEY'       => '',//QQ互联key
    'QQ_SECRET'    => '',//QQ互联secret
    'SINA_KEY'     => '',//新浪key
    'SINA_SECRET'  => '',//新浪secret
    'CALLBACK_URL' => 'http://www.example.com/example.php'//回调地址，测试为当前地址
];
```

## 获取登录跳转授权地址
设置好配置后，获取授权地址，自行进行调整操作即可
``` php
//获取登录类型
$type = isset($_GET['type']) ? trim ($_GET['type']) : 'qq';
try {
    $sns = ThinkOauth::getInstance ($type, self::config ());
    //TODO 使用跳转函数处理跳转
    echo $sns->getRequestCodeURL ();
} catch (Exception $e) {
    // TODO 异常报错处理
    var_dump ($e->getMessage ());
}
```

## 授权后回调处理
接收参数，调用CallBack进行处理，将返回授权后获取的信息。  
``` php
//获取传递参数值
$request = $_REQUEST;
$config  = [];//配置
try {
    $syncLoginModel = new CallBack();
    $userInfo       = $syncLoginModel->init ($config, $request);
    // @TODO 处理登录业务逻辑
    var_dump ($userInfo);
} catch (Exception $e) {
    // TODO 异常报错处理
    var_dump ($e->getMessage ());
}
```

## Demo Example
设置一个完整的demo调用方法
``` php 
<?php

use Liaodeity\SyncLogin\CallBack;
use Liaodeity\SyncLogin\ThinkOauth;

//如果使用composer将不用进行手动导入类
require_once '../src/ThinkOauth.php';
require_once '../src/SyncLoginModel.php';
require_once '../src/CallBack.php';


error_reporting (E_ALL ^ E_NOTICE);

/**
 * 本类为测试类，其中的一些接收参数未进行过滤等，请测试安全性后再部署正式
 * Class Example
 */
class Example
{
    /**
     * 授权地址类
     */
    public static function login ()
    {
        //获取登录类型
        $type = isset($_GET['type']) ? trim ($_GET['type']) : 'qq';
        try {
            $sns = ThinkOauth::getInstance ($type, self::config ());
            //TODO 使用跳转函数处理跳转
            echo $sns->getRequestCodeURL ();
        } catch (Exception $e) {
            // TODO 异常报错处理
            var_dump ($e->getMessage ());
        }
    }

    //默认配置类
    public static function config ()
    {
        return [
            'QQ_KEY'       => '',//QQ互联key
            'QQ_SECRET'    => '',//QQ互联secret
            'SINA_KEY'     => '',//新浪key
            'SINA_SECRET'  => '',//新浪secret
            'CALLBACK_URL' => 'http://www.example.com/example.php'//回调地址，测试为当前地址
        ];
    }

    /**
     * 回调处理类
     */
    public static function callback ()
    {
        //获取传递参数值
        $request = $_REQUEST;
        $config  = self::config ();
        try {
            $syncLoginModel = new CallBack();
            $userInfo       = $syncLoginModel->init ($config, $request);
            // @TODO 处理登录业务逻辑
            var_dump ($userInfo);
        } catch (Exception $e) {
            // TODO 异常报错处理
            var_dump ($e->getMessage ());
        }
    }
}

//初始化调用
if (isset($_GET['code'])) {
    //回调地址需带code
    Example::callback ();
} else {
    Example::login ();
}

```

## 目前已测试通过
* QQ互联
* 新浪微博

其他的由于没有申请key密钥，所以没办法测试，敬请谅解。但应该均可正常调用，如有问题请联系

## 联系作者 
联系邮箱：[liaodeity@gmail.com](mailto:liaodeity@gmail.com)  


本项目基于ThinkOauth.class.php，来源于优秀的开源代码，感谢作者以及参与者，感谢[ThinkPHP](http://www.thinkphp.cn) 