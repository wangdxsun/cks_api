<?php
namespace Admin\Model;



class KcodeProduceModel extends  BaseModel{

    public static $table = ['pn_type', 'channel', 'relation'];

    public static function getProductImportListData($page = null){

        return BaseModel::getListData(
            [
                'table'=>self::$table[2],
                'where' => ['status' => 0],
                'page' => $page = !empty($page) ? $page : 0
            ]
        );

    }

}