<?php

namespace MethodArgument\Library;

use MethodArgument\Library\Validate\Matches;

/**
* 校验规则处理类
*/
Class Validate
{
    private $value = null;
    private $rule  = null;
    
    public function __construct($value, $rule)
    {
        $this->value = $value;
        $this->rule = $rule;        
    }
    /**
    * 校验方法分发处理
    *
    * @param \MethodArgument\Argument $resource
    */
    public function run($resource)
    {
        $handle = $this->rule['handle'] ?? null ;
        $args   = $this->rule['argument'] ?? [] ;
        if(empty($handle)){
            return true;
        }
        //快速验证函数
        if( method_exists($this, strtolower($handle)) ){
            return call_user_func_array([$this, strtolower($handle)], $args);
        }
        //内置验证方法
        if( class_exists( ucfirst($handle) ) )
        {
            $verObject  = new $handle($value, $args);
            return $verObject->verify();
        }
        //执行自定义函数验证规则
        if( method_exists( $resource, ucfirst($handle) ) )
        {
            return call_user_func_array([$resource, ucfirst($handle)], $args);
        }
        
        return true;
    }
    
}