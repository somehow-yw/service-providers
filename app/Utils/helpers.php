<?php

use App\Http\Middleware\WeChat;
use App\Models\AuthenticatedUser;

if (!function_exists('place_replace')) {
    /**
     * @param string $str
     * @param string $replace
     *
     * @return string
     */
    function place_replace($str, $replace = 'X')
    {
        //得到小数点前的字符串
        $newStr = strstr($str, '.', true);

        if (strlen($newStr) == 1) {
            return 'X';
        } else {
            //去掉最后一位,得到临时字符串
            $newStr = substr($newStr, 0, -1);

            //将替换的字符串拼装在后面
            return $newStr . $replace;
        }
    }
}

if (!function_exists('getServiceProvider')) {
    /**
     * 获取全局的serviceProvider实例
     *
     * @return \Zdp\ServiceProvider\Data\Models\ServiceProvider | null
     */
    function getServiceProvider()
    {
        return config(WeChat::SERVICE_PROVIDER_KEY);
    }
}

if (!function_exists('isSpOwner')) {
    /**
     * 是否是商户所有者
     *
     * @return boolean
     */
    function isSpOwner()
    {
        return config(\App\Http\Middleware\WeChatOAuth::IS_OWNER_KEY);
    }
}

if (!function_exists('isSpMember')) {
    /**
     * 是否是商户成员
     *
     * @return boolean
     */
    function isSpMember()
    {
        return config(\App\Http\Middleware\WeChatOAuth::IS_MEMBER_KEY);
    }
}

if (!function_exists('getWeChatAccount')) {
    /**
     * 获取全局的wechatAccount 实例
     *
     * @return \Zdp\ServiceProvider\Data\Models\WechatAccount |null
     */
    function getWeChatAccount()
    {
        return config(WeChat::WECHAT_ACCOUNT_KEY);
    }
}

if (!function_exists('getWeChatOAuthUser')) {
    /**
     * 获取全局的 wechatOAuthUser 实例
     *
     * @return \Overtrue\Socialite\UserInterface |null
     */
    function getWeChatOAuthUser()
    {
        if (config('wechat.enable_mock')) {
            return session('wechat.oauth_user');
        } else {
            $host = explode('.', request()->getHttpHost());
            $subDomain = array_first($host);
            $wechatCacheKey =
                'wechat-oauth-user:' . $subDomain . '-' . session_id();

            return cache($wechatCacheKey);
        }
    }
}

if (!function_exists('getUser')) {
    /**
     * 获取全局的 用户 实例
     *
     * @return \App\Models\User |null
     */
    function getUser()
    {
        return app(AuthenticatedUser::class)->getUser();
    }
}

if (!function_exists('resolveSource')) {
    /**
     * 解析出子域名 如 cd.test.zdongpin.com => cd
     *
     * @return string
     */
    function resolveSource()
    {
        $host = explode('.', request()->getHttpHost());
        $subDomain = array_first($host);

        return $subDomain;
    }
}

if (!function_exists('array_key_by_merge')) {
    /**
     * 主要针对Collection keyBy后的merge
     *
     * 将 array1:
     *  [
     *      135 => [
     *          "goods_id" => 135
     *          "buy_num" => 10
     *         ]
     *     133 =>  [
     *         "goods_id" => 133
     *         "buy_num" => 10
     *      ]
     * ]
     *
     * array 2:
     * [
     *      135 => [
     *          "goods_id" => 135
     *          "goods_price" => 11.5
     *         ]
     *     133 =>  [
     *         "goods_id" => 133
     *         "goods_price" => 12.1
     *      ]
     * ]
     *
     * merge 为
     *
     *  [
     *      [
     *          "goods_id" => 135
     *          "buy_num" => 10
     *          "goods_price" => 11.5
     *         ]
     *     [
     *         "goods_id" => 133
     *         "buy_num" => 10
     *         "goods_price" => 12.1
     *      ]
     * ]
     *
     * @param $array1
     * @param $array2
     *
     * @return array
     */
    function array_key_by_merge($array1, $array2)
    {
        $mergeFunc = function ($array1, $array2) {
            $returnArr = [];
            foreach ($array1 as $key => $value) {
                if (array_key_exists($key, $array2)) {
                    $mergeValue = array_merge($value, $array2[$key]);
                    array_push($returnArr, $mergeValue);
                } else {
                    array_push($returnArr, $value);
                }
            }

            return $returnArr;
        };

        if (count($array1) > count($array2)) {
            return $mergeFunc($array1, $array2);
        } else {
            return $mergeFunc($array2, $array1);
        }
    }
}

if (!function_exists('generate_wechat_template')) {
    /**
     * 生成微信模板
     *
     * @see config/wechat_template.php
     *
     * @param $key string 可以是以下几种 ['new_order','buyers_cancel_order','order_succeed','order_shipments']
     * @param $dataArr
     *
     * @return array
     */
    function generate_wechat_template($key, $dataArr)
    {
        $template = config('wechat_template.' . $key);
        $template['template_data'] = $dataArr;

        return $template;
    }
}

if (!function_exists('setTemplateOpenId')) {
    /**
     * 设置每个模板的openid
     *
     * @param $template
     * @param $openId
     */
    function setTemplateOpenId(&$template, $openId)
    {
        $template['template_data']['touser'] = $openId;
    }
}

if (!function_exists('setTemplateUrl')) {
    /**
     * 设置每个模板的url
     *
     * @param $template
     * @param $subDomain string 主域名 如 hello.dongpin.me => hello
     */
    function setTemplateUrl(&$template, $subDomain)
    {
        $urlPath = array_get($template, 'template_data.url');

        if ($urlPath != '#') {
            $template['template_data']['url'] =
                sprintf(env('SERVICE_PROVIDER_DOMAIN'), $subDomain) . $urlPath;
        }
    }
}

if (!function_exists('getUrl')) {
    /**
     * 设置每个模板的url
     *
     * @param $subDomain string 主域名 如 hello.dongpin.me => hello
     * @param $urlPath   string 路由
     *
     * @return string
     */
    function getUrl($subDomain, $urlPath = '')
    {
        return sprintf(env('SERVICE_PROVIDER_DOMAIN'), $subDomain) . $urlPath;
    }
}

if (!function_exists('fileLogWrite')) {
    /**
     * 记录信息到指定的文件
     *
     * @param        $messages
     * @param string $logPath
     */
    function fileLogWrite($messages, $logPath)
    {
        $dirPath = dirname($logPath);
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
        $date = date('Y-m-d H:i:s');
        $lineFeed = PHP_EOL;
        $messages = "{$lineFeed}[{$date}]-----{$lineFeed}{$messages}";
        file_put_contents($logPath, $messages, FILE_APPEND);
    }
}