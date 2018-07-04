<?php
namespace Admin\Model;
use Think\Model;

/**
 * 基类
 *
 * @author jan
 *
 */
class BaseModel extends  Model{

    //const PAGE_SIZE = 3; //分页数据数量

    //@获取指定表中相关数据,分页显示(layer分页)
    public static  function getListData(...$args){
        $arg = $args[0];
        $where = !empty($arg['where']) ? $arg['where'] : null;
        $pnum = !empty($arg['pnum']) ? $arg['pnum'] : C('PAGE_SIZE');
        $fields = !empty($arg['fields']) ? $arg['fields'] : '*';
        $order = !empty($arg['order']) ? $arg['order'] : 'id';
        $page = !empty($arg['page']) ? $arg['page'] : 1;

        $count=M($arg['table'])->where($where)->count();
        //$newPage=new \Think\Page($count,$pnum);
        $data=M($arg['table'])->where($where)->field($fields)->order("$order desc")->page($page,$pnum)->select();
        //$response['page']=$newPage->show();
        $response['pages']=ceil($count/C('PAGE_SIZE'));//总页数
        $response['data']=$data;

        return $response;
    }

    /**
    @获取数据库特定条件的数据,true二维，false一维
    @params []
    @return []
     **/
    public static function getDbData($condition, $tag = true){
        //p($condition);die;
        $condition['where'] = $condition['where'] ? $condition['where'] : null;
        $condition['fields'] = $condition['fields'] ? $condition['fields'] : '*';
        $condition['order'] = $condition['order'] ? $condition['order'] : 'id desc';
        //M($condition['table'])->where($condition['where'])->field($condition['fields'])->order($condition['order'])->select();
        //echo M($condition['table'])->getLastSql();die;


        return $data = $tag
            ? M($condition['table'])->where($condition['where'])->field($condition['fields'])->order($condition['order'])->select()
            : M($condition['table'])->where($condition['where'])->field($condition['fields'])->find();

    }

    //链接查询的列表数据
    public static function joinSecListData(...$args){

        $arg = $args[0];
        $where = !empty($arg['where']) ? $arg['where'] : null;
        $pnum = !empty($arg['pnum']) ? $arg['pnum'] : C('PAGE_SIZE');
        $fields = !empty($arg['fields']) ? $arg['fields'] : '*';
        $order = !empty($arg['order']) ? $arg['order'] : 'id';
        $page = !empty($arg['page']) ? $arg['page'] : 1;

        $count=M($arg['table'])->where($where)->count();
        //$newPage=new \Think\Page($count,$pnum);
        $data = M($arg['table'])->join($arg['joinWhere'])->where($where)->field($fields)->order("$order desc")->page($page,$pnum)->select();
        //$data=M($arg['table'])->where($where)->field($fields)->order("$order desc")->page($page,$pnum)->select();

        //$response['page']=$newPage->show();
        $response['pages']=ceil($count/C('PAGE_SIZE'));//总页数
        $response['data']=$data;
        $response['currentResult']=$page;

        return $response;

    }

    //链接查询的数据
    public static function joinSecDbData($condition){

        $condition['where'] = $condition['where'] ? $condition['where'] : null;
        $condition['fields'] = $condition['fields'] ? $condition['fields'] : '*';
        $condition['order'] = $condition['order'] ? $condition['order'] : 'id desc';

        return M($condition['table'])
            ->join($condition['joinWhere'])
            ->where($condition['where'])
            ->field($condition['fields'])
            ->order($condition['order'])
            ->select();
    }

    //添加数据
    public static function addData($condition){

        return M($condition['table'])->add($condition['data']);
    }

    //批量添加数据
    public static function addAllData($condition){
        return M($condition['table'])->addAll($condition['data']);
    }

    //获取某个字段
    public static function getFieldVal($condition){

        return M($condition['table'])->where($condition['where'])->getField($condition['field']);

    }

    //修改数据
    public static function saveData($condition){

        return M($condition['table'])->where($condition['where'])->save($condition['data']);
    }


    //删除数据
    public static function delData($condition){
        return M($condition['table'])->where($condition['where'])->delete();
    }

    //设置某个字段的值
    public static function setFieldVal($condition){

        return M($condition['table'])-> where($condition['where'])->setField($condition['data']);
    }

    //字段自增1
    public static  function setFieldInc($condition){

        return M($condition['table'])->where($condition['where'])->setInc($condition['field'],1);

    }

    //字段自减1
    public static  function setFieldDec($condition){

        return M($condition['table'])->where($condition['where'])->setDec($condition['field'],1);

    }

    //统计--总数
    public static function getCount($condition){
        return M($condition['table'])->where($condition['where'])->count();
    }


    //统计--总和
    public static function getSum($condition) {
        return M($condition['table'])->where($condition['where'])->sum($condition['field']);
    }

    //获取用户拥有的菜单
    public static function getUserMenu($rid){
        //获取拥有的菜单id
        $condition = [
            'table' => 'role',
            'where' => [ 'id' => $rid ],
            //'order' => 'sort',
            //'display' => 1,
            'fields' => 'menu_id'
        ];

        $menuId = self::getDbData($condition, false); //菜单id集合 字符串

        $menuIdArr = explode(',', $menuId['menu_id']);

        $items = [];
        $condition1 = [
            'table' => 'menu',
            'fields' => 'pid, name, route, icon_class',
        ];

        foreach($menuIdArr  as $val){//查找pid ， name进行无限极f分类
            $items[$val]['id'] = $val;
            $condition1['where'] = ['id' => $val, 'display' => 1];
            $data = self::getDbData($condition1, false);
            $items[$val]['pid'] = $data['pid'];
            $items[$val]['name'] = $data['name'];
            $items[$val]['route'] = $data['route'];
        }

        return self::generateTree($items);

    }


    //获取所有菜单
    public static function getAllMenu($show = true){

        $items = [];

        $condition = [
            'table' => 'menu',
            'order' => 'sort',
        ];

        !$show && $condition['where'] = ['display' => 1];

        foreach(self::getDbData($condition) as $val)

            $items[$val['id']] = $val;

        return self::generateTree($items);
    }


    /**
     * @ Purpose:无限极分类
     * @param [] $items 排序后的菜单二维数组
     * e.g. $items = [
                1 => ['id' => 1, 'pid' => 0, 'name' => '安徽省'],
                2 => ['id' => 2, 'pid' => 0, 'name' => '浙江省'],
                3 => ['id' => 3, 'pid' => 1, 'name' => '合肥市'],
                4 => ['id' => 4, 'pid' => 3, 'name' => '长丰县'],
                5 => ['id' => 5, 'pid' => 1, 'name' => '安庆市'],
            ];
     * @return []
     */
    private static function generateTree($items)
    {
        foreach($items as $item)

            $items[$item['pid']]['son'][$item['id']] = &$items[$item['id']];

        return isset($items[0]['son']) ? $items[0]['son'] : [];
    }


    //添加或者编辑
    public static function integrityData($data){

        return $res = $data['tag'] == 'add' ? self::addData($data['data']) : self::saveData($data['data']) ;

    }

    //登录用户的UID
    public static function uid(){
        return $_SESSION['adminInfo']['id'];
    }

    //登录用户的用户名
    public static function username(){
        return $_SESSION['adminInfo']['uname'];
    }

    //登录用户的角色
    public static function role(){
        return $_SESSION['adminInfo']['role_id'];
    }

    //登录用户的角色名字
    public static function roleName(){
        return $_SESSION['adminInfo']['role']['role_name'];
    }

    //是否是超管
    public static function isAdmin(){
        return $_SESSION['adminInfo']['role_id'] == 1 ? true : false;
    }




    /**
    @获取最新的一条数据的id
     **/
    public static function getlastid($table,$where){
        $data=M($table)->where($where)->order("id desc")->limit(1)->select();
        return $data[0]['id'];
    }


}