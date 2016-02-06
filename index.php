<?php
//turn all reporting on
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';


\RedBeanPHP\R::setup('mysql:host=localhost;dbname=db_smi','root','root');

$logWriter = new \Slim\LogWriter(fopen(__DIR__ . '/logs/log-'.date('Y-m-d', time()), 'a'));

$customConfig = array();

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array('log.writer' => $logWriter, 'custom' => $customConfig ));

/*
$app->add(new \Slim\Middleware\HttpBasicAuthentication([
    "realm" => "Protected",
    "relaxed" => array("localhost"),
    "users" => [
        "root" => "r0Ot_C0n643"
    ]
]));
*/



//Including all resources
foreach (glob("resources/*.php") as $filename)
{
    require_once $filename;
}



$app->run();