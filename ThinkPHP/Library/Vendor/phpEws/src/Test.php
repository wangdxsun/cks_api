<?php

namespace jamesiarmes\PhpEws;
use \jamesiarmes\PhpEws\Client;

class Test
{
    
    public function index(){
        $server = 'mail.phicomm.com';
        $username = 'jianjia.zheng@phicomm.com';
        $password = 'jj321++';

        $ews = new \jamesiarmes\PhpEws\Client($server, $username, $password);

        $res = $ews->getClient();
        var_dump($res);
    }


}

$res = new Test();
$res->index();

