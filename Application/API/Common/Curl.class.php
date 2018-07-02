<?php
namespace API\Common;
class Curl{
    /**
        @get请求
        @author:姚志伟
        @date:2016-09-23
     **/
    public  static function curl_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl使用自定义header头
        //curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }
	/**
		@post请求
		@author:姚志伟
		@date:2016-10-24
	**/
		
   public static function curl_post($url,$data){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }

    /**
        @POST_请求OA
        @auhtor:yaozhiwei
        @date:2016-12-22
     **/
    public static function curl_header_post($url,$data,$header){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }
    
    /**
        @POST_请求OA
        @auhtor:yaozhiwei
        @date:2016-12-22
     **/
    public static function curl_header_get($url,$header){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl使用自定义header头
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }
}
