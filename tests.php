<?php
/**
 * Created by PhpStorm.
 * User: gui
 * Email:liaodeity@foxmail.com
 * Date: 2017/9/18
 */

require_once 'src/ThinkOauth.php';
require_once 'src/SyncLoginModel.php';
require_once 'src/CallBack.php';

class tests
{
    public static function login()
    {
        $type = 'tencent';
        $sns  = \Liaodeity\SyncLogin\ThinkOauth::getInstance ($type, self::config ());
        echo $sns->getRequestCodeURL ();
        //TODO 使用跳转函数处理跳转
    }

    public static function config()
    {
        return [
            'TENCENT_KEY'    => '11',
            'TENCENT_SECRET' => '2222',
            'CALLBACK_URL'   => 'http://ww.jianbaizhan.com/callback/synclogin'
        ];
    }

    public static function callback()
    {
        $request = $_REQUEST;
        $syncLoginModel  = new \Liaodeity\SyncLogin\CallBack();
        $userInfo        = $syncLoginModel->init (self::config (), $request);
        // @TODO 处理登录业务逻辑

    }
}

//tests::login();
tests::callback ();