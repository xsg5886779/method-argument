<?php

namespace MethodArgument\Library\Validate;

use MethodArgument\Library\Contact\ValidateInterface;
use MethodArgument\Library\Error;
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
        
        $this->error = new Error('Matches');
    }
    /**
    * 执行验证
    */
    public function verify()
    {
        if(empty($this->arguments))
        {
            return true;
        }
        try{
            if( preg_match('/' . $this->arguments . '/i', $this->value) )
            {
                return true;
            }
            $this->error->addError('验证失败');
            return $this->error;
        }catch(\Exception $e)
        {
            $this->error->addError('规则错误');
            return $this->error;
        }
        
        return true;
    }
    
    
}