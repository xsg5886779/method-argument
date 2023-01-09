<?php

namespace MethodArgument\Library\Validate;

use MethodArgument\Library\Contact\ValidateInterface;
/**
* 校验规则处理类
*/
Class Matches implements ValidateInterface
{
    private $value = null;
    private $arguments = null;
    
    private $error = null;
    
    public function __construct($value, $arguments)
    {
        $this->value = $value;
        $this->arguments = $arguments;
        
        $this->error = new Error;
    }
    /**
    * 执行验证
    */
    public function verify();
    {
        
        return true;
    }
    
}