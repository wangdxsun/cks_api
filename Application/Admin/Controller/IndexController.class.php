<?php

namespace Admin\Controller;
use Admin\Model\BaseModel;

/**
 * 用于用户的登录验证控制器类
 * 
 * @author jan
 *
 */
class IndexController extends BaseController
{

    private $table = ['user', 'admin', 'role', 'article', 'affix', 'comment', 'acomment'];

    private $echartsName = ['用户', '评论'];

    private $echartsType = 'line';

    /**
     * 后台登录
     */
    public function __construct() {
        parent::__construct();


    }


    public function login() {

        !empty($_SESSION['adminInfo']) ? $this->redirect('index'):$this->display();
    }

    /**
     * 后台首页
     */
    public function index(){

        $this->display();
    }

    /*
     * 首页main
     */

    public function main(){

        //用户总数
        $this->assign('userCount', $this->getCount($this->table[0]));

        //今日新增
        $this->assign('userTodayCount', $this->getCount($this->table[0], ['create_time' => ['between', [startTime(), endTime()]]]));

        //文章、公告浏览量
        $this->assign('viewCount', $this->getSum($this->table[3], 'views') + $this->getSum($this->table[4], 'views'));

        //文章、公告评论总量
        $this->assign('commentCount', $this->getCount($this->table[4]) + $this->getCount($this->table[5]));

        //echart图表日期
        $sevenDays = $this->getDateFromRange(date("Y-m-d",strtotime('-6 day')), date("Y-m-d",time()));

        //一周日期
        $this->assign('sevenDays', json_encode($sevenDays));

        //一周每天的用户数
        $this->assign('sevenDaysUserIncNum', $this->getDayIncNum($this->echartsName[0], $sevenDays, $this->table[0]));

        //一周内的每天评论数量
        $this->assign('sevenDaysCommentIncNum', $this->getDayIncNum($this->echartsName[1], $sevenDays, [$this->table[5], $this->table[6]]));

        //echart设置信息
        $this->assign('echartsName', json_encode($this->echartsName));

        //最受欢迎文章
        $this->assign('mostLikeArticle', $this->getMostArticle('likes'));

        //最多浏览
        $this->assign('mostViewArticle', $this->getMostArticle('views'));

        //系统信息
        $this->assign('systemInfo', $this->getSystemInfo());

        $this->display();
    }

    /**
     * 用户信息验证
     */
    public function checkUnamePsw()
    {
        $condition = [
            'table'=> $this->table[1],
            'where' => ['uname' => ['eq', $_POST['uname'] ]]
        ];


        $userInfo = BaseModel::getDbData($condition, false);

        if (empty($userInfo)) {
            die('用户名或密码错误，请正确填写');
        } else {
            //验证密码
            if(password_verify($_POST['password'], $userInfo['password'])){
                $_SESSION['adminInfo'] = $userInfo;
                $_SESSION['menuInfo'] = BaseModel::isAdmin() ? BaseModel:: getAllMenu(false) : BaseModel:: getUserMenu($userInfo['role_id']);
                $_SESSION['adminInfo']['role'] = BaseModel::getDbData([
                    'table'=> $this->table[2],
                    'where' => ['id' => $userInfo['role_id']],
                    'fields' => ['role_name']
                ], false);

                M($this->table[1])->where($condition['where'])->setField('last_time',date('Y-m-d H:i:s'));
                echo 's';
            }else{
                die('用户名或密码错误，请正确填写');
            }
        }
    }

    //修改密码数据整合
    public function checkPost($postData){

        $pdata = json_decode($postData['data'],true);

        return  [
                    'table' => $this->table[1],
                    'id' => BaseModel::uid(),
                    'tag' => 'edit',
                    'data' => [
                        'operator' =>  BaseModel::uid(),
                        'password' => password_hash(trim($pdata['pwd']), PASSWORD_BCRYPT)
                    ],
                ];

    }

    //获取总数
    private function getCount($table, $where=null){
        return BaseModel::getCount([
            'table' => $table,
            'where' => $where
        ]);
    }


    //获取总和
    private function getSum($table, $field){

        return BaseModel::getSum([
            'table' => $table,
            'field' => $field
        ]);
    }

    //获取一周内每一天的用户增长---画图
    private function getDayIncNum($name, $date, $table){

        $data = [];
        $data['name'] = $name;
        $data['type'] = $this->echartsType;
        $data['data'] = [];

        foreach($date as $val)

            if($table == $this->table[0])

                array_push($data['data'], $this->getCount($table, [
                    'create_time' => [
                        'between', [startTime(strtotime($val)), endTime(strtotime($val)) ]
                    ]
                ])?:0);

            else
                array_push($data['data'], ($this->getCount($table[0], [
                    'create_time' => [
                        'between', [startTime(strtotime($val)), endTime(strtotime($val)) ]
                    ]
                ]) + $this->getCount($table[1], [
                    'create_time' => [
                        'between', [startTime(strtotime($val)), endTime(strtotime($val)) ]
                    ]
                ])) ? : 0);

        return json_encode($data);
    }

    //最受欢迎文章
    private function getMostArticle($field){

         return BaseModel::getDbData([
            'table' => $this->table[3],
            'where' => [$field => M($this->table[3])->max($field)]
        ], false);

    }

    /**
     * 用户退出
     */
    public function logout()
    {
        unset($_SESSION['adminInfo']);
        $this->redirect('login');

    }

    /**
     * 获取指定日期段内每一天的日期
     * @param Date $startdate 开始日期
     * @param Date $enddate  结束日期
     * @return Array
     */
    private  function getDateFromRange($startdate, $enddate){

        $stimestamp = strtotime($startdate);

        $etimestamp = strtotime($enddate);

        // 计算日期段内有多少天
        $days = ($etimestamp-$stimestamp)/86400+1;

        // 保存每天日期
        $date = [];

        for($i=0; $i<$days; $i++)

            $date[] = date('Y-m-d', $stimestamp+(86400*$i));

        return $date;
    }

    //获取系统信息
    private function  getSystemInfo(){

        return [

            '版本信息' => 'Page__'.C('version'),
            '网站域名' => $_SERVER['HTTP_HOST'],
            '服务器IP' => getServerIp(),
            '系统信息' => php_uname(),
            'PHP版本' => phpversion(),
            '数据库版本' => mysql_get_server_info()
        ];

    }
}