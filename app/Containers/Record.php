<?php
namespace App\Containers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Record
{
    private $str;

    public function __construct($str){
        $this->str = $str;
    }

    public function execute(){
        $log = new Logger('suite');
        $log->pushHandler(new StreamHandler(storage_path().'/logs/suite.log', Logger::WARNING));
        $log->warning($this->str);
    }

}