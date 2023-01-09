<?php

namespace MethodArgument\Library;


/**
* 参数值实例对象
*/
Class ParamterValue
{
    private $DefaultValue = null;
    private $VerifyFields = null;
    private $ArgumentValue = null;
    
    private $Resource = null;
    
    /**
    * @param mix $argumentValue     原始传处参数值
    * @param &\MethodArgument\Argument $argumentResource     原始传处参数值
    */
    public function __construct($argumentValue, &$argumentResource)
    {
        $this->ArgumentValue = $argumentValue;
        $this->Resource = $argumentResource;
    }
    //设置验证规则
    public function setValidation( $verifyFields )
    {
        $this->VerifyFields = $verifyFields;
    }
    //设置默认值
    public function setDefaultValue( $defaultValue )
    {
        $this->DefaultValue = $defaultValue;
    }
    
    /**
    * 校验
    *
    * @return boolean | \MethodArgument\Library\Error 
    */
    public function verify()
    {
        $error = new Error;
        if( empty($this->rule) )
        {
            //'handle' 
            //'customer' 
            //'argument' 
            foreach($this->VerifyFields as $rule)
            {
                $validate = new Validate($this, $rule);
                $verify = $validate->run();
                if( $verify !== true)
                {
                    $error->addError($verify);
                }
            }
        }
        if( $error->Count() > 0 ){
            return $error;
        }
        return true;
    }
    /**
    * 类型判断（检测一个给定的对象是否属于（继承于）某个类（class）、某个类的子类、某个接口（interface））
    *
    * @param class $instance
    */
    public function Equals($instance)
    {
        if( is_object($this->getValue()) )
        {
            return $this->getValue() instanceof $instance;
        }
        return false;
    }
    /**
    * 获得异常信息
    */
    public function getError()
    {
        return null;
    }
    /**
    * 获得类型
    */
    public function getType()
    {
        return new ParamterType($this->getValue());
    }
    /**
    * 判断是否为null
    */
    public function isNull()
    {
        return $this->getValue() === null;
    }
    /**
    * 判断是否为空
    */
    public function isEmpty()
    {
        return empty($this->getValue());
    }
    /**
    * 返回值
    */
    public function getValue()
    {
        return $this->ArgumentValue === null ? $this->defaultValue : $this->ArgumentValue ;
    }
}