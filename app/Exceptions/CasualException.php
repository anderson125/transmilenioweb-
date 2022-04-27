<?php

namespace App\Exceptions;

use Exception;

class CasualException extends Exception
{
    
    protected $message;
    private $arr = ['errors'=>[]];

    public function __construct($message){
        $this->message = $message;
    }

    public function __set($property,$value){
        $this->arr[$property] = $value;
    }

    public function __get($property){
        return $this->arr[$property];
    }
}
