<?php
/**
 * Created by PhpStorm.
 * User: gui
 * Email:liaodeity@foxmail.com
 * Date: 2017/9/18
 */

use Liaodeity\SyncLogin\CallBack;
use Liaodeity\SyncLogin\ThinkOauth;

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

