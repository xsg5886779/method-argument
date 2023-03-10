<?php

namespace MethodArgument\Library;

use MethodArgument\Library\Validate\Matches;

/**
* 校验规则处理类
*/
Class Validate
{
    private $Value = null;
    private $rule  = null;
    
    public function __construct($Value, $rule)
    {
        $this->Value = $Value;
        $this->rule = $rule;        
    }
    /**
    * 校验方法分发处理
    *
    * @param \MethodArgument\Library\ParamterValue $Value
    */
    public function run()
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
        if( class_exists( '\\MethodArgument\\Library\\Validate\\'.ucfirst($handle) ) )
        {
            $ValidateClass = '\\MethodArgument\\Library\\Validate\\'.ucfirst($handle);
            $verObject  = new $ValidateClass($this->Value->getValue(), $args);
            return $verObject->verify();
        }
        //执行自定义函数验证规则
        $customHandle = 'set' . ucfirst($handle) . 'Validation';
        if( method_exists( $this->Value->getResource(), $customHandle ) )
        {
            try{
                array_unshift($args, $this->Value->getValue());
                $callRes = call_user_func_array([$this->Value->getResource(), $customHandle], $args);
                if($callRes === true || empty($callRes) )
                {
                    return true;
                }
                throw new \Exception("{$callRes}");
            }catch(\Exception $e)
            {
                $error = new Error($handle);
                $error->addError($e->getMessage());
                return $error;
            }
        }
        
        return true;
    }
    /**
    * 必填
    */
    private function Required()
    {
        if(!$this->Value->getIsSet())
        {
            $error = new Error('Required');
            $error->addError('未填写');
            return $error;
        }
        if(is_null($this->Value->getValue()) && $this->Value->getValue() !== 0 && $this->Value->getValue() !== false)
        {
            $error = new Error('Required');
            $error->addError('未填写');
            return $error;
        }
        return true;
    }
    
    /**
    * 邮件格式
    */
    public function Email()
    {
        if(empty($this->Value->getValue()) || !preg_match('/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i',$this->Value->getValue()))
        {
            $error = new Error('Email');
            $error->addError('邮箱格式错误');
            return $error;
        }
        return true;        
    }
    /**
    * 验证是否为数字
    */
    public function Number()
    {
        //是否为数字格式
        if( $this->Value->type()->isNumber() == false )
        {
            $error = new Error('Number');
            $error->addError('非数字格式');
            return $error;
        }
        return true;
    }
    /**
    * 验证是否为数字
    */
    public function Range()
    {
        //是否为数字格式
        if( $this->Value->type()->isNumber() == false )
        {
            $error = new Error('Range');
            $error->addError('非数字格式');
            return $error;
        }
        $args = func_get_args();
        //没有约束条件
        if( empty($args) ){
            return true;
        }
        $_min = intval( $args[0] ?? 0 );
        //只有一个参数，则第一个参数为min
        if(count($args) == 1){
            $min = $_min;
            if( $this->Value->getValue() < $min )
            {
                $error = new Error('Range');
                $error->addError("小于{$min}");
                return $error;   
            }
        }
        elseif(count($args) > 1){
            $_max = intval( $args[1] ?? 0 );
            $min = min($_min, $_max);
            $max = max($_min, $_max);
            if( $this->Value->getValue() < $min || $this->Value->getValue() > $max )
            {
                $error = new Error('Range');
                $error->addError("不能小于{$min}或大于{$max}");
                return $error;   
            }
        }
        return true;
    }
    /**
    * 判断最大数字
    */
    public function Max()
    {
        //是否为数字格式
        if( $this->Value->type()->isNumber() == false )
        {
            $error = new Error('Max');
            $error->addError('非数字格式');
            return $error;
        }
        $args = func_get_args();
        //没有约束条件
        if( empty($args) ){
            return true;
        }
        $max = intval( $args[0] ?? 0 );
        
        if( $this->Value->getValue() > $max )
        {
            $error = new Error('Max');
            $error->addError("大于{$max}");
            return $error;   
        }
        return true;
    }
    /**
    * 判断最小数字
    */
    public function Min()
    {
        //是否为数字格式
        if( $this->Value->type()->isNumber() == false )
        {
            $error = new Error('Min');
            $error->addError('非数字格式');
            return $error;
        }
        $args = func_get_args();
        //没有约束条件
        if( empty($args) ){
            return true;
        }
        $min = intval( $args[0] ?? 0 );
        
        if( $this->Value->getValue() < $min )
        {
            $error = new Error('Min');
            $error->addError("小于{$min}");
            return $error;   
        }
        return true;
    }
    /**
    * 判断最大长度
    */
    public function Maxlen()
    {
        //是否为数字格式
        if( $this->Value->isEmpty() )
        {
            $error = new Error('Maxlen');
            $error->addError('为空');
            return $error;
        }
        $args = func_get_args();
        //没有约束条件
        if( empty($args) ){
            return true;
        }
        $max = intval( $args[0] ?? 0 );
        
        if( strlen($this->Value->getValue()) > $max )
        {
            $error = new Error('Maxlen');
            $error->addError("超出{$max}长度");
            return $error;   
        }
        return true;
    }
    /**
    * 判断最小数字
    */
    public function Minlen()
    {
        //是否为数字格式
        if( $this->Value->isEmpty() )
        {
            $error = new Error('Minlen');
            $error->addError('为空');
            return $error;
        }
        $args = func_get_args();
        //没有约束条件
        if( empty($args) ){
            return true;
        }
        $min = intval( $args[0] ?? 0 );
        
        if( strlen($this->Value->getValue()) < $min )
        {
            $error = new Error('Minlen');
            $error->addError("小于{$min}长度");
            return $error;   
        }
        return true;
    }
    
}