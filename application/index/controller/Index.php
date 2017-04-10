<?php
namespace app\index\controller;

use think\Controller;
use app\index\model\Teacher;

class Index extends Controller
{
    public function index()
    {
        return extension('./file/www/index.php');
    }

    public function teacher()
    {
        if (file_exists($file = './html/teacher.html')) {
            include_once './html/teacher.html';
        } else {
            if (!is_dir($way = dirname($file))) {
                // return $way;
                mkdir($way, 0777, true);
            }
            $teachers = Teacher::select();

            ob_start();

            $this->assign('teachers', $teachers);
            $return = $this->fetch();

            file_put_contents('./html/teacher.html', $return);
            return $return;
        }
        
    }

    /**
     * 测试公共函数
     * @return [type] [description]
     */
    public function testFunction()
    {
        //测试无限遍历数组
        // $arr = array('aaa', array('bbb', 23, array(22, 'ccc')), 'ddd', array('eee', 888));
        // foreach_array($arr, $brr);
        // dump($brr);
        //
        // $teacher = db('teacher')->select();
        // foreach_array($teacher, $brr);
        // dump($brr);
        // dump($teacher);

        //测试无限遍历文件夹
        // scan_dir_file('d:/wamp/www/chongxin/php/class', $files);
        // dump($files);
        //------------------------------------------------------------

        //测试数字计数器
        // return num_counter();

        //测试图片计数器
        // $this->assign('numcounters', num_counter(true));
        // return $this->fetch();
        //------------------------------------------------------------

        //测试替换字符串的所有空白符
        //返回：__GetZoneResult_={mts:'1882513',province:'广东',catName:'中国移动',telString:'18825137275',areaVid:'30517',ispVid:'3236139',carrier:'广东移动'}
        //       $value = "__GetZoneResult_ = {
        //     mts:'1882513',
        //     province:'广东',
        //     catName:'中国移动',
        //     telString:'18825137275',
        //     areaVid:'30517',
        //     ispVid:'3236139',
        //     carrier:'广东移动'
        // }";
        // return replace_blank($value, '');

        // 测试判断验证码内容，并将签名前置
        // $str = '中[文abc字符52Kjj【验证码】';
        //       dump(check_sms($str, $matches));
        //       dump($matches);
        //       dump(pre_sms_sign($str));
        //
        //测试过滤HTML标签
        //       $str="<a href='http://www.manongjc.com'>码农教程':\"</a>";
        // return replace_html($str);
        //------------------------------------------------------------

        //测试获取客户端真实IP
        // return get_client_ip();

        // 测试获取随机数
        // return get_rand_number(6);
        //------------------------------------------------------------

        //测试获取手机号码归属地和运营商
        // if (!$result = phone_number_type('17095138888')) {
        //     return '未知号码！';
        // }
        // dump($result);

        // //测试加密算法
        // return encrypt_password('password');
        //------------------------------------------------------------

    }

    /**
     * 测试扩展类库
     * @return [type] [description]
     */
    public function testExtend()
    {
        //测试发送短信验证码
        // return SmsCaptcha::rongLianSms('18825137275', '523833');
        // == true ? '发送成功！' : '发送失败！';

        //测试发送语音验证码
        // return SmsCaptcha::rongLianVoice('18825137275', '683458');
        //------------------------------------------------------------

        //测试单/多文件上传
        // dump($_FILES);
        // $file = new UpFile();
        // dump($file->upload());
        // dump($file->error);
        
        //测试获取天气类
        // $weather = new \my\Weather('5fcb76eacfd68cb25f3bc43f2444a134');
        // dump($weather->getWeather('广州'));
    }

}
