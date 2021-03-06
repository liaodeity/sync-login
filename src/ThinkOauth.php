<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi.cn@gmail.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
// | ThinkOauth.class.php 2013-02-25
// +----------------------------------------------------------------------
namespace Liaodeity\SyncLogin;

use Exception;

/**
 * 2017-09-18 修改
 * Email:liaodeity@foxmail.com
 * Class ThinkOauth
 * @package Liaodeity\SyncLogin
 */
abstract class ThinkOauth
{
    /**
     * oauth版本
     * @var string
     */
    protected $Version = '2.0';

    /**
     * 申请应用时分配的app_key
     * @var string
     */
    protected $AppKey = '';

    /**
     * 申请应用时分配的 app_secret
     * @var string
     */
    protected $AppSecret = '';

    /**
     * 授权类型 response_type 目前只能为code
     * @var string
     */
    protected $ResponseType = 'code';

    /**
     * grant_type 目前只能为 authorization_code
     * @var string
     */
    protected $GrantType = 'authorization_code';

    /**
     * 回调页面URL  可以通过配置文件配置
     * @var string
     */
    protected $Callback = '';

    /**
     * 获取request_code的额外参数 URL查询字符串格式
     * @var srting
     */
    protected $Authorize = '';

    /**
     * 获取request_code请求的URL
     * @var string
     */
    protected $GetRequestCodeURL = '';

    /**
     * 获取access_token请求的URL
     * @var string
     */
    protected $GetAccessTokenURL = '';

    /**
     * API根路径
     * @var string
     */
    protected $ApiBase = '';

    /**
     * 授权后获取到的TOKEN信息
     * @var array
     */
    protected $Token = null;

    /**
     * 调用接口类型
     * @var string
     */
    private $Type = '';

    protected $Config = [
        //'TENCENT_KEY'    => 'key',
        //'TENCENT_SECRET' => 'secret',
        //'CALLBACK_URL'   => 'http://www.example.com/callback'
    ];

    /**
     * 构造方法，配置应用信息
     * @param array $token
     */
    public function __construct ($token = null, $config = [])
    {
        $this->setConfig ($config);
        //设置SDK类型
        $class      = get_class ($this);
        $this->Type = strtoupper (substr ($class, 0, strlen ($class) - 3));

        //获取应用配置
        $config = $this->Config;
        $type   = strtoupper (substr ($class, 0, strlen ($class) - 3));
        if (empty($config[$type . '_KEY']) || empty($config[$type . '_SECRET'])) {
            throw new Exception('Please configure the APP_KEY and APP_SECRET you requested');
        } else {

            $this->AppKey    = $config[$type . '_KEY'];
            $this->AppSecret = $config[$type . '_SECRET'];
            $this->Token     = $token; //设置获取到的TOKEN
        }
    }

    /**
     * 取得Oauth实例
     * @static
     * @return mixed 返回Oauth
     */
    public static function getInstance ($type, $config, $token = null)
    {
        $name = ucfirst (strtolower ($type)) . 'SDK';
        //判断是否存在SDK的class
        if (!file_exists (__DIR__ . "/sdk/{$name}.class.php")) {
            throw new Exception("{$name}.class.php is not exist");
        }
        require_once "sdk/{$name}.class.php";
        if (class_exists ($name)) {
            return new $name($token, $config);
        } else {
            throw new Exception('CLASS NOT EXIST :' . $name);
        }

    }

    /**
     * 初始化配置
     * 回调地址
     */
    private function config ()
    {
        $url            = parse_url (isset($this->Config['CALLBACK_URL']) ? $this->Config['CALLBACK_URL'] : '');
        $scheme         = isset($url['scheme']) ? $url['scheme'] : '';
        $host           = isset($url['host']) ? $url['host'] : '';
        $path           = isset($url['path']) ? $url['path'] : '';
        $query          = isset($url['query']) ? $url['query'] : '';
        $callback       = $scheme . '://' . $host . $path . '?' . $query . '&type=' . strtolower ($this->Type);
        $this->Callback = $callback;
    }

    public function setConfig ($config = [])
    {
        // to upper
        foreach ($config as $key => &$item) {
            $this->Config[strtoupper ($key)] = $item;
        }
        unset($config);
    }

    /**
     * 请求code
     */
    public function getRequestCodeURL ()
    {
        $this->config ();
        //Oauth 标准参数
        $params = array(
            'client_id'     => $this->AppKey,
            'redirect_uri'  => $this->Callback,
            'response_type' => $this->ResponseType,
        );

        //获取额外参数
        if ($this->Authorize) {
            parse_str ($this->Authorize, $_param);
            if (is_array ($_param)) {
                $params = array_merge ($params, $_param);
            } else {
                throw new Exception('AUTHORIZE Incorrect configuration！');
            }
        }

        return $this->GetRequestCodeURL . '?' . http_build_query ($params);
    }

    /**
     * 获取access_token
     * @param string $code 上一步请求到的code
     */
    public function getAccessToken ($code, $extend = null)
    {
        $this->config ();
        $params = array(
            'client_id'     => $this->AppKey,
            'client_secret' => $this->AppSecret,
            'grant_type'    => $this->GrantType,
            'code'          => $code,
            'redirect_uri'  => $this->Callback,
        );

        $data        = $this->http ($this->GetAccessTokenURL, $params, 'POST');
        if(is_null($extend))
            $extend = [];
        $this->Token = $this->parseToken ($data, $extend);

        return $this->Token;
    }

    /**
     * 合并默认参数和额外参数
     * @param array $params 默认参数
     * @param       array /string $param 额外参数
     * @return array:
     */
    protected function param ($params, $param)
    {
        if (is_string ($param))
            parse_str ($param, $param);

        return array_merge ($params, $param);
    }

    /**
     * 获取指定API请求的URL
     * @param  string $api API名称
     * @param  string $fix api后缀
     * @return string      请求的完整URL
     */
    protected function url ($api, $fix = '')
    {
        return $this->ApiBase . $api . $fix;
    }

    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url 请求URL
     * @param  array $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    protected function http ($url, $params, $method = 'GET', $header = array(), $multi = false)
    {
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch (strtoupper ($method)) {
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query ($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params                   = $multi ? $params : http_build_query ($params);
                $opts[CURLOPT_URL]        = $url;
                $opts[CURLOPT_POST]       = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init ();
        curl_setopt_array ($ch, $opts);
        $data  = curl_exec ($ch);
        $error = curl_error ($ch);
        curl_close ($ch);
        if ($error) throw new Exception('请求发生错误：' . $error);

        return $data;
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    protected function getClientIp ($type = 0, $adv = false)
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode (',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search ('unknown', $arr);
                if (false !== $pos) unset($arr[$pos]);
                $ip = trim ($arr[0]);
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf ("%u", ip2long ($ip));
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);

        return $ip[$type];
    }

    /**
     * 抽象方法，在SNSSDK中实现
     * 组装接口调用参数 并调用接口
     */
    abstract protected function call ($api, $param = '', $method = 'GET', $multi = false);

    /**
     * 抽象方法，在SNSSDK中实现
     * 解析access_token方法请求后的返回值
     */
    abstract protected function parseToken ($result, $extend);

    /**
     * 抽象方法，在SNSSDK中实现
     * 获取当前授权用户的SNS标识
     */
    abstract public function openid ();
}