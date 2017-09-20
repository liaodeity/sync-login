<?php
/**
 * Created by PhpStorm.
 * User: gui
 * Email:liaodeity@foxmail.com
 * Date: 2017/9/18
 */

namespace Liaodeity\SyncLogin;


class CallBack
{
    protected $Token = [];
    /**
     *
     * @param       $config 配置
     * @param array $request 所有传递参数
     * @return mixed
     * @throws \Exception
     */
    public function init($config, $request = [])
    {
        if(empty($request))
            $request = $_REQUEST;
        $code = isset($request['code']) ? $request['code'] : '';
        $type = isset($request['type']) ? $request['type'] : '';
        $sns  = ThinkOauth::getInstance ($type, $config);

        //腾讯微博需传递的额外参数
        $extend = null;
        if ($type == 'tencent') {
            $openid  = isset($request['openid']) ? $request['openid'] : '';
            $openkey = isset($request['openkey']) ? $request['openkey'] : '';
            $extend  = array('openid' => $openid, 'openkey' => $openkey);
        }

        $token          = $sns->getAccessToken ($code, $extend);
        $this->Token = $token;
        $syncLoginModel = new SyncLoginModel();
        if (method_exists ($syncLoginModel, $type)) {
            $user_info = $syncLoginModel->$type($token); //获取传递回来的用户信息
            return $user_info;
        } else {
            throw new \Exception('method not exists');
        }
    }

    public function getToken()
    {
        return $this->Token;
    }
}