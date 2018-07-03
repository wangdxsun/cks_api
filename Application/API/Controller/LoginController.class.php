<?php

namespace API\Controller;

use Think\Controller;
use API\Common\Curl;
use API\Controller\BaseController;
/**
 * 登录类
 * 
 * @author yy
 *
 */
class LoginController extends Controller
{
    //初始操作
    public function _initialize()
    {
        $this->allowOrigin(); 
        $is_sign = $this->verifyEncryptSign();
        if ($is_sign) {
            return json_encode(array('error' => 1,'message' => '验证失败'),JSON_UNESCAPED_UNICODE);
        }
    }
    public function allowOrigin(){
        header("Access-Control-Allow-Origin: *");
        //header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); 
        //header("Content-type: text/json; charset=utf-8");
    }
    //邮箱手机号验证
    public function checkEmailOrPhone($account_number){
        $preg_email='/^[a-zA-Z0-9]+([-_.][a-zA-Z0-9]+)*@([a-zA-Z0-9]+[-.])+([a-z]{2,5})$/ims';
        $preg_phone='/^1[34578]\d{9}$/ims';
        if(preg_match($preg_email,$account_number)){
            $code = 'mailaddress';
        }
        elseif(preg_match($preg_phone,$account_number)){
            $code = 'phonenumber';
        }else{
            $code = false;
        }

        return $code;
    }

    //响应前台的请求--验证签名
    public function verifyEncryptSign(){

        //验证身份
        $timeStamp = $_POST['timeStamp'];
        $randomStr = $_POST['randomStr'];
        $signature = $_POST['signature']; // $signature 客户端请求地址中携带的签名,与服务端生成的签名进行比对
        //根据客户端请求过来的数据生成的签名 与$signature 进行对比
        return $this -> arithmetic($timeStamp,$randomStr) != $signature ? 1 : 0;

    }

    /**
     * @param $timeStamp 时间戳
     * @param $randomStr 随机字符串
     * @return string 返回签名
     */
    private function arithmetic($timeStamp, $randomStr){

        $arr = [
          'timeStamp' => $timeStamp,
          'randomStr' => $randomStr,
          'token' => C('token')
        ];
        return md5(implode("", $arr));

        //转换成大写
        //return strtoupper(md5(sha1(implode($arr))));
    }


    /**
        @功能:获取云账号验证码
        @param:yy
        @date:2018-06-30
    **/
    public function verificationCode(){
        
        // authorizationcode   授权码 string  通过授权码接口获得
        // mailaddress 邮箱号 string  邮箱号与手机号二选一
        // phonenumber 手机号 string  邮箱号与手机号二选一
        // verificationtype    验证码类型   string  0.短信验证码，1.语音验证码

        $data['authorizationcode'] = BaseController::authorization();
        $isEmail = $this->checkEmailOrPhone($_POST['account_number']);
        if($isEmail){
            $data[$isEmail] = $_POST['account_number'];
        }else{
            exit(BaseController::returnMsg(array('error'=>'100')));
        }

        $data['verificationtype'] = 0;
        $params = http_build_query($data);
        $url = C('cloud_url').C('cloud_verificationCode').'?'.$params;
        $res = Curl::curl_get($url);
        exit(BaseController::returnMsg($res));
    }

    /**
        @功能:云账号验登录
        @param:yy
        @date:2018-06-30
    **/
    public function login(){
        // authorizationcode   授权码 string  调用请求授权码接口获得的code值（建议app每次开机都重新请求一下授权码）
        // deviceid    设备标识    string  可选，传的话要对新设备进行手机验证码进行验证。不传则进行正常登陆。
        // mailaddress 邮箱号 string  邮件地址，可选
        // password    密码  string  用户密码 （用户输入的明文密码大写MD5值）由于需要兼容老用户登录，登录环节前端不设有密码校验规则，密码统一由服务端校验。
        // phonenumber 手机号码    string  手机号码 ，可选（邮箱，手机号，用户名不能都为空）
        // username    用户名 string  用户名，可选（邮箱，手机号，用户名不能都为空）
        $data['authorizationcode'] = BaseController::authorization();
        $isEmail = $this->checkEmailOrPhone($_POST['account_number']);
        if($isEmail){
            $data[$isEmail] = $_POST['account_number'];
        }elseif (!empty($_POST['account_number'])) {
            $data['username'] = $_POST['account_number'];
        }
        else{
            exit(BaseController::returnMsg(array('error'=>'100')));
        }
        //登录时以上三个只能一个
        //$data['mailaddress'] = $_POST['account_number'];
        //$data['phonenumber'] = $_POST['account_number'];
        //$data['username'] = $_POST['account_number'];
        $data['password'] = md5($_POST['password']);
        $url = C('cloud_url').C('cloud_login');
        $res = Curl::curl_post($url,$data);
        exit(BaseController::returnMsg($res));
    }

    /**
        @功能:云账号验注册
        @param:yy
        @date:2018-06-30
    **/
    public function account(){
        // authorizationcode   授权码 string  调用请求授权码接口获得的code值
        // data    用户属性    array<string>   用来保存每个应用的专有信息，json格式例如：{"address":"上海松江","age":"19","realname":"张三"}
        //     address 地址  string  如：上海市松江区文吉路99号
        //     age 年龄  string  如：19
        //     nickname    昵称  string  如：三
        //     realname    真实姓名    string  如：张三
        //     sex 性别  string  1代表男 ，2代表女
        //     zipcode 邮编  string  如：201600
        //     zone    区域  string  如：松江区
        // deviceid    设备标识    string  可选，如果传，则注册时就记录终端标识，登录时不会再要求手机验证码二次验证了
        // mailaddress 邮箱号 string  邮箱号，可选(不推荐邮箱号注册）
        // password    密码  string  用户密码 （前端校验规则推荐：6-20个字符，可由a-z A-Z 0-9 _ ! # $ * + - . / : ; = ? @ [ ] ^ ` | 组成，除此外为非法字符）
        // phonenumber 手机号码    string  手机号码 ，可选（邮箱，手机号，用户名不能都为空），格式需要做校验
        // registersource  注册源 string  与client_id一致：0：预注册用户标识。1. 老商城，2.保留，3.论坛，4,WEB商城，5.H5商城，6.“斐讯路由APP”IOS版，7.“斐讯路由APP”安卓版 8.云账户web页面 9.路由器app服务器，10.环境猫Andoid app，11.环境猫iOS app，51.mobile运动Android app, 52.mobile健康Android app, 53.mobile运动iOS app, 54.mobile健康iOS app
        // username    用户名 string  用户名，可选（邮箱，手机号，用户名不能都为空）
        // verificationcode    验证码 string  
        $data['authorizationcode'] = BaseController::authorization();
        $isEmail = $this->checkEmailOrPhone($_POST['account_number']);
        if($isEmail){
            $data[$isEmail] = $_POST['account_number'];
        }else{
            exit(BaseController::returnMsg(array('error'=>'100')));
        }
        //注册时邮箱和电话只能传一个
        //$data['mailaddress'] = $_POST['mailaddress'];
        //$data['phonenumber'] = $_POST['phonenumber'];
        //$data['username'] = $_POST['username'];
        $data['password'] = md5($_POST['password']);
        $data['registersource'] = $_POST['registersource'];
        $data['verificationcode'] = $_POST['verificationcode'];
        //$data['data'] = $_POST['data'];
        $url = C('cloud_url').C('cloud_account');
        $res = Curl::curl_post($url,$data);
        exit(BaseController::returnMsg($res));
    }

    /**
        @功能:云账号验注册
        @param:yy
        @date:2018-06-30
    **/
    public function forgetPassword(){
        // authorizationcode   授权码 string  调用请求授权码接口获得的code值（建议app每次开机都重新请求一下授权码）
        // mailaddress 邮箱号 string  
        // newpassword 新密码 string  （用户明文密码的MD5值）前端校验规则：6-20 个字符，可由 a-zA-Z0-9_!#$*+-./:;=?@[]^`| 组成，除此 外为非法字符
        // phonenumber 手机号 string  
        // verificationcode    验证码 string  
        $data['authorizationcode'] = BaseController::authorization();
        $isEmail = $this->checkEmailOrPhone($_POST['account_number']);
        if($isEmail){
            $data[$isEmail] = $_POST['account_number'];
        }else{
            exit(BaseController::returnMsg(array('error'=>'100')));
        }
        //忘记密码时邮箱和电话只能传一个
        //$data['mailaddress'] = $_POST['mailaddress'];
        //$data['phonenumber'] = $_POST['phonenumber'];
        $data['newpassword'] = md5($_POST['newpassword']);
        $data['verificationcode'] = $_POST['verificationcode'];
        $url = C('cloud_url').C('cloud_forgetpassword');
        $res = Curl::curl_post($url,$data);
        exit(BaseController::returnMsg($res));
    }

    /**
        @功能:云账号验注册
        @param:yy
        @date:2018-06-30
    **/
    public function checkPhonenumber(){
        // authorizationcode   授权码 string  调用请求授权码接口获得的code值（建议app每次开机都重新请求一下授权码）
        // mailaddress 邮箱号 string  
        // phonenumber 要被检测的手机号码   string  
        $data['authorizationcode'] = BaseController::authorization();
        $isEmail = $this->checkEmailOrPhone($_POST['account_number']);
        if($isEmail){
            $data[$isEmail] = $_POST['account_number'];
        }else{
            exit(BaseController::returnMsg(array('error'=>'100')));
        }
        $params = http_build_query($data);
        $url = C('cloud_url').C('cloud_checkPhonenumber')."?".$params;
        $res = Curl::curl_get($url,$data);
        exit(BaseController::returnMsg($res));
    }
}
