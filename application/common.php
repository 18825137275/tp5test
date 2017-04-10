<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 无限遍历数组，遍历多维数组，组成一个一维数组$brr
 * @param  [type] $arr  多维数组
 * @param  [type] &$brr 传入空数组，用来接收新组成的一维数组
 * @return [type]       [description]
 * 控制器调用方法：
 * foreach_array($arr, $brr);//一定要传递第二个参数，第二个参数即为返回的一维数组，即可直接使用
 * dump($brr);
 * 原理：利用函数的引用传递，把累计返回值传递到第二个参数上，
 *         调用的时候在第二个参数上传入一个空数组即可输出变化后的结果
 */
function foreach_array($arr, &$brr = array())
{
    if (is_array($arr)) {
        foreach ($arr as $value) {
            foreach_array($value, $brr);
        }
    } else {
        $brr[] = $arr;
    }
}

/**
 * 无限遍历文件夹：将所有文件的绝对路径组成一个数组$files
 * @param  [type] $dir    文件夹路径 如：$dir = 'd:/wamp/www/chongxin/php';
 * @param  array  &$files 传入空数组，用来接收新组成的一维数组
 * @return [type]         [description]
 * 控制器调用方法：
 * scan_dir_file('d:/wamp/www/chongxin/php', $brr);//一定要传递第二个参数，第二个参数即为返回的一维数组，即可直接使用
 * dump($brr);
 * 原理：利用函数的引用传递，把累计返回值传递到第二个参数上，
 *         调用的时候在第二个参数上传入一个空数组即可输出变化后的结果
 */
function scan_dir_file($dir, &$files = array())
{
    if (is_dir($dir)) {
        foreach (scandir($dir) as $file) {
            //去除.和..目录，否则会无限遍历上级目录而导致超时
            if ($file != '.' && $file != '..') {
                scan_dir_file($dir . '/' . $file, $files);
            }
        }
    } else {
        $files[] = $dir;
        // 如果只需要文件名可以用以下方法
        // $files[] = basename($dir);
    }
}

/**
 * 计数器：数字/图片计数器，bug：最大限制整型2147483648
 * @param  boolean $pic 当为true的时候为图片计数器
 * @return [type]       数字计数器时返回值为整数，图片计数器时为数组
 * 经过C层模板赋值后，V层用volist循环输出图片即可，如：
 * {volist name="numcounters" id="numcounter"}
<img src="__PUBLIC__/common/counter/image/{$numcounter}.jpg" />
{/volist}
 */
function num_counter($pic = false)
{
    $numUrl = 'common/counter/num.txt';
    if (!@$numTxt = fopen($numUrl, 'r')) {
        // return '文件不存在！';
        $num = 0;
    } else {
        //避免num.txt为空（0个字符）, 且定义自适应读取大小限制
        $size = filesize($numUrl) == 0 ? 100 : (int) filesize($numUrl) + 1000;
        if (!$num = (int) fgets($numTxt, $size)) {
            $num = 0;
        }
    }
    fclose($numTxt);

    $num++;
    $numTxt2 = fopen($numUrl, 'w');
    fwrite($numTxt2, $num);
    fclose($numTxt2);

    //输出数字计数器
    if (!$pic) {
        return $num;
    }

    //输出图片计数器的数值,$numArr数组
    return $numArr = str_split($num);
}

/**
 * 替换字符串的所有空白符
 * @param  [type] $value 要处理的字符串
 * @param  [type] $blank 用来替换空白符的字符
 * @return [type]        返回处理之后的字符串
 */
function replace_blank($value, $blank)
{
    return preg_replace('/\s+/', $blank, $value);
}

/**
 * 过滤HTML标签：把标签转换为HTML实例
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function replace_html($str)
{
    return htmlspecialchars($str);
}

/**
 * 判断短信内容：只有一个签名且后置
 * @param  [type] $str      [description]
 * @param  [type] &$matches 引用传递所有匹配项，dump($matches)结果如下：
 * array (size=3)
0 => string '中[文abc字符52Kjj【验证码】' (length=36)
1 => string '中[文abc字符52Kjj' (length=21)
2 => string '【验证码】' (length=15)
3 => string '验证码' (length=9)
 * @return [type]           匹配成功返回int 1，匹配失败返回int 0
 */
function check_sms($str, &$matches = array())
{
    return preg_match('/^([^【|】]+)(【([^【|】]+)】)$/', $str, $matches);
}

/**
 * 前置签名
 * @param  [type] $value [description]
 * @return [type]        匹配成功返回处理过的结果;匹配失败返回FALSE
 * preg_replace的第二个参数可以用$1$2...或者\1\2表示子模式的序号
 */
function pre_sms_sign($str)
{
    $pattern = '/^([^【|】]+)(【[^【|】]+】)$/';
    return preg_replace($pattern, '$2$1', $str);
}

/**
 * 获取客户端真实IP
 * @return [type] [description]
 * 1、因为HTTP_X_FORWARDED_FOR容易被客户伪装修改成任意值，对系统有危险，所以考虑弃用
 * 2、REMOTE_ADDR获取客户端ip，但当客户使用代理服务器是，获取到的是代理ip
 */
function get_client_ip()
{
    $unknown = 'unknown';
    // 考虑弃用
    // if (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), $unknown)) {
    //     $ip = getenv('HTTP_X_FORWARDED_FOR');
    // }
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), $unknown)) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), $unknown)) {
        $ip = getenv('REMOTE_ADDR');
    }

    if (false !== strpos($ip, ',')) {
        $ip = reset(explode(',', $ip));
    }
    return $ip;
}

/**
 * 获取一个随机字符串
 * @param  [type] $length 随机数的位数
 * @return [type]         [description]
 */
function get_rand_number($length)
{
    $result = '';
    $str    = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghijkmnpqrstuvwxyz';

    for ($i = 1; $i <= $length; $i++) {
        $result .= $str[rand(0, strlen($str) - 1)];
    }

    return $result;
}

/**
 * 网页爬虫
 * @param  [type] $url 要爬的url地址
 * @return [type]      [description]
 */
function web_spider($url)
{
    $curl = curl_init(); //初始化curl
    curl_setopt($curl, CURLOPT_URL, $url); //设置访问网页的url
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 0); //执行之后不直接显示html代码
    $https = preg_match('/^https:[^https:]+$/', $url); //匹配成功返回true
    //判断是否为https的url
    if ($https) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //终止从服务端进行验证
    }
    $output = curl_exec($curl); //执行，获得返回值
    curl_close($curl); //关闭curl
    return $output;
}

/**
 * HTTP POST/GET 调用接口
 * @param  [type]  $url    调用地址
 * @param  boolean $params 调用参数
 * @param  boolean $isget  是否get方式，默认为post方式
 * @param  boolean $xml    是否支持提交xml格式的数据，默认为不支持
 * @return [type]          成功返回数据，失败返回false
 */
function http_post_get($url, $params = false, $isget = false, $xml = false)
{
    $httpInfo = array();

    //如果参数为数组，生成urlencoded之后的请求字符串(中文/特殊字符会被编码)(a=***&b=***)
    if (is_array($params)) {
        $params = http_build_query($params);
    }

    $ch = curl_init(); //curl初始化

    curl_setopt($ch, CURLOPT_TIMEOUT, 15); //设置curl允许执行的最长秒数
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //直接返回数据

    //添加对https的支持
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //终止从服务端进行验证

    //当$xml为true时支持提交xml格式的数据（设置头信息支持xml）
    if ($xml) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-type: text/xml;charset=utf-8"]);
    }

    //判断是post还是get方式调用
    if (!$isget) {
        curl_setopt($ch, CURLOPT_POST, true); //post提交
        curl_setopt($ch, CURLOPT_URL, $url); //调用地址
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params); //传递POST参数
    } else {
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }

    $result = curl_exec($ch);

    if ($result === false) {
        // return "cURL Error:" . curl_error($ch);
        return false;
    }

    //获取cURL连接资源句柄的信息
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
    curl_close($ch);

    return $result;
}


/*  中国移动：134、135、136、137、138、139、150、151、152、157(TD)、158、159、182、183、184、187、178、188、147（数据卡号段） 、1705（虚拟运营商移动号段）
 *  中国联通：130、131、132、145(数据卡号段)155、156、176、185、186、1709（虚拟运营商联通号段）
 *  中国电信：133、153、177、180、181、189、（1349卫通）、1700（虚拟运营商电信号段）
 */
/*
一、淘宝网API
API地址： http://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=18825137275
参数：tel：手机号码      返回：JSON
二、拍拍API
API地址： http://virtual.paipai.com/extinfo/GetMobileProductInfo?mobile=18825137275&amount=10000&callname=getPhoneNumInfoExtCallback
参数：mobile：手机号码; callname：回调函数;  amount：未知（必须）      返回：JSON
三、财付通API
API地址： http://life.tenpay.com/cgi-bin/mobile/MobileQueryAttribution.cgi?chgmobile=18825137275
参数：chgmobile：手机号码       返回：xml
四、百付宝API
API地址： https://www.baifubao.com/callback?cmd=1059&callback=phone&phone=18825137275
参数：phone：手机号码; callback：回调函数; cmd：未知（必须）      返回：JSON
五、115API
API地址： http://cz.115.com/?ct=index&ac=get_mobile_local&callback=jsonp1333962541001&mobile=18825137275
参数：mobile：手机号码; callback：回调函数       返回：JSON

返回值：
__GetZoneResult_ = {
mts:'1882513',
province:'广东',
catName:'中国移动',
telString:'18825137275',
areaVid:'30517',
ispVid:'3236139',
carrier:'广东移动'
}
 */
/**
 * 获取手机号码的归属地和运营商
 * @param  [type] $phone 手机号码
 * @return [type]        成功返回数组(归属地，运营商);失败返回false
 */
function phone_number_type($phone)
{
    //方法一：调用接口
    $result  = array();
    $content = mb_convert_encoding(file_get_contents("http://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=" . $phone), 'UTF-8', 'gbk');
    //提取返回值，转换成数组
    if (!preg_match_all("/(\w+):'([^']+)'/", $content, $matches)) {
        return false;
    }

    $result[] = $matches['2']['1']; //归属地
    $result[] = $matches['2']['2']; //运营商

    return $result;

    //方法二：正则判断
    // $yidong   = "/^((134|135|136|137|138|139|147|150|151|152|157|158|159|178|182|183|184|187|188)\d{8}|1705\d{7})$/";
    // $liantong = "/^((130|131|132|145|155|156|176|185|186)\d{8}|1709\d{7})$/";
    // $dianxin  = "/^((133|153|177|180|181|189)\d{8}|1700\d{7})$/";

    // if (preg_match($yidong, $phone)) {return '移动';}
    // if (preg_match($liantong, $phone)) {return '联通';}
    // if (preg_match($dianxin, $phone)) {return '电信';}
    // return '号码错误！';
}

/**
 * 密码加密算法
 * @return [type] [description]
 */
function encrypt_password($password)
{
    return sha1(md5($password) . 'encrptyPassword');
}

/**
 * 最常用的正则表达式匹配方法
 * @param  [type] $str      需要匹配的字符串
 * @param  [type] $type     匹配的类型(如：qq,邮箱,手机号...)
 * @param  [type] &$matches 匹配项
 * @return [type]           [description]
 */
function preg_matches($str, $type, &$matches)
{
    switch ($type) {
        case 'username': //匹配用户账号：字母开头，5-16字节，允许数字/字母/下划线
            return preg_match('/^[a-zA-Z]\w{4,15}$/', $str, $matches);
            break;
        case 'phone': //匹配手机号
            return preg_match('/^((13[0-9]|14[5|7]|15[0|1|2|3|5|6|7|8|9]|18[0-9]|17[6|7|8])\d{8}|(170[0|5|9]\d{7}))$/', $str, $matches);
            break;
        case 'email': //匹配邮箱
            return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $str, $matches);
            break;
        case 'tel': //匹配国内固话：如:0511-4405222 或 021-87888822
            return preg_match('/^\d{3}-\d{8}|\d{4}-\d{7}$/', $str, $matches);
            break;
        case 'qq': //匹配QQ号：QQ号从10000开始
            return preg_match('/^[1-9][0-9]{4,}$/', $str, $matches);
            break;
        case 'chinese': //匹配汉字
            return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str, $matches);
            break;
        case 'url': //匹配url连接
            return preg_match('/^[a-zA-z]+://[^\s]*$/', $str, $matches);
            break;
        case 'idcard': //匹配身份证:15位：dddddd yymmdd xx p 18位：dddddd yyyymmdd xxx y
            return preg_match('/^(^[1-9]\d{7}((0[1-9])|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0[1-9])|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/', $str, $matches);
            break;
    }
}

/**
 * 生成唯一ID
 * @return [type] [description]
 * microtime(true);//当前 Unix 时间戳的微秒数(1489933313.6477)
uniqid();//基于以微秒计的当前时间,生成一个唯一的ID。(13位数:58ce95d31a6ee)
uniqid(microtime(true), true);//参数二为true(23位:1489933779.108358ce95d31a6fd5.97481263),末尾添加额外的熵
 */
function unique_id()
{
    return md5(uniqid(microtime(true), true));
}

/**
 * 下载文件
 * @return [type] [description]
 * V层使用方法：<a href="{:url('Index/upload')}?filename=./uploads/7f5262ac041b8875625c08efab71084f.jpg">通过程序下载22.jpg</a>
 */
function download_file()
{
    if (isset($_GET)) {
        foreach ($_GET as $filename) {
            // content-disposition:attachment;//定义通过附件的形式读取
            // filename=' . basename($filename)//定义保存时的文件名
            header('content-disposition:attachment;filename=' . basename($filename));
            header('content-length:' . filesize($filename)); //获取文件的大小

            readfile($filename); //读取文件内容
        }
    }
}

/**
 * 文件夹不存在则创建
 * @param  [type] $dir $dir为文件夹路径，可以用dirname()取
 * @return [type]      [description]
 * mkdir第三个参数为true的时候允许创建多级文件夹
 */
function make_dir($dir)
{
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

/**
 * 生成log文件：logs/日期时间/控制器/文件.log
 * @param  [type] $data [description]
 * @return [type]       [description]
 * file_put_contents第三个参数表示追加内容，不能用引号
 * "\r\n"--在txt中插入换行符，不能用单引号
 */
function build_log($data)
{
    if (!is_dir($dir = './logs/' . request()->controller())) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($dir . '/' . date('Ymd') . '.txt', $data . "\r\n", FILE_APPEND);
}

/**
 * 获取文件的扩展名
 * @param  [type] $file 文件名
 * @return [type]       返回不带.的扩展名
 */
function extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}