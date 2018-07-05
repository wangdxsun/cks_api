<?php
namespace Admin\Model;



class KcodeProduceModel extends  BaseModel{

    public static $table = ['pn_type', 'channel', 'relation'];
    public static $filePath = '/Uploads/excel/';

    public static function getProductImportListData($page = null){

        return BaseModel::getListData(
            [
                'table'=>self::$table[2],
                'where' => ['status' => 0],
                'page' => $page = !empty($page) ? $page : 0
            ]
        );

    }

    public static function exportExcel($expTitle, $expCellName, $expTableData){

        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = time();//导出excal 文件名称
        $cellNum = count($expCellName);//有多少列
        $dataNum = count($expTableData);//有多少行
        vendor("PHPExcel.PHPExcel");//引入PHPExcel文件
        ini_set("memory_limit", "1024M");
        $objPHPExcel = new \PHPExcel();//实例化PHPExcel类库，相当于新建一个Excel表
        $cellName = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O',
            'P','Q','R','S','T','U','V','W','X','Y','Z', 'AA','AB','AC','AD','AE',
            'AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT',
            'AU','AV','AW','AX','AY','AZ'];
        //在第二行插入每列的标题
        for($i=0;$i<$cellNum;$i++)
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'1', $expCellName[$i][1]);
        //从第三行开始插入数据
        for($i=0;$i<$dataNum;$i++)
            for($j=0;$j<$cellNum;$j++)
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+2), $expTableData[$i][$expCellName[$j][0]]);

        $objSheet = $objPHPExcel->getActiveSheet();//获取当前活动sheet
        $objSheet->setTitle('sheet1');//给当前的活动sheet起个名称
        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        header('Cache-Control: max-age=0');
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        $filePath = '.'.self::$filePath.$fileName.'.xlsx';

        $objWriter->save($filePath);

        return webDomain(true).self::$filePath.$fileName.'.xlsx';
    }

}