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
    private $ArgumentField = null;
    private $customerError = null;
    /**
    * 是否必填
    */
    private $isMust = false;
    public function setIsMust(bool $must)
    {
        $this->isMust = $must;
    }
    /**
    * 是否set
    */
    private $isSet = false;
    public function setIsSet(bool $isset)
    {
        $this->isSet = $isset;
    }
    public function getIsSet()
    {
        return $this->isSet;
    }
    
    /**
    * MethodArgument\Argument::class 钩子
    */
    protected $Resource = null;
    public function getResource()
    {
        return $this->Resource;
    }
    
    /**
    * @param mix $argumentValue     原始传处参数值
    * @param &\MethodArgument\Argument $argumentResource     原始传处参数值
    */
    public function __construct($argumentField, $argumentValue, &$argumentResource)
    {
        $this->ArgumentField = $argumentField;
        $this->ArgumentValue = $argumentValue;
        $this->Resource = $argumentResource;
    }
    /**
    *设置验证规则
    * 
    */
    public function setValidation( $verifyFields, $customerError = null )
    {
        $rules = $this->Resource->parseVerifyRule($verifyFields);
        $this->mergeValidation($rules);
        if($customerError)
        {
            $this->customerError = $customerError;
        }
    }
    /**
    * 合并规则
    */
    private function mergeValidation($verifyFields)
    {
        if($this->VerifyFields == null){
            $this->VerifyFields = [];
        }
        if( empty($verifyFields) || !is_array($verifyFields) )
        {
            return ;
        }
        foreach($verifyFields as $rule)
        {
            $has = false;
            foreach($this->VerifyFields as $_rule)
            {
                if($_rule['handle'] == $rule['handle'])
                {
                    $has = true;
                    break;
                }
            }
            //如果不存在则添加
            if(!$has){
                $this->VerifyFields[] = $rule;
            }
        }
    }
    /**
    * 设置默认值
    */
    public function setDefaultValue( $defaultValue )
    {
        $this->DefaultValue = $defaultValue;
    }
    
    /**
    * 校验
    *
    * @param null | array $customerMessage 自定义错误信息
    * @return boolean | \MethodArgument\Library\Error 
    */
    public function verify($customerMessage = null)
    {
        $error = new Error;
        $error->setCustomerMessage( "{$this->ArgumentField} {rule} {error}" );
        if($this->customerError)
        {
            $error->setCustomerMessage( $this->customerError );
        }
        if( $customerMessage !== null )
        {
            $error->setCustomerMessage( $customerMessage );
        }
        //如果是必填项，则自动生成required规则
        if($this->isMust)
        {
            $this->setValidation('required');
        }
        if( !empty($this->VerifyFields) )
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
    public function type()
    {
        return new ParamterType($this->getValue());
    }
    #region 判断
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
    
    #endregion


    #region 结果输出
    
    public function Get()
    {
        return $this->getValue();
    }
    /**
    * 返回值
    */
    public function getValue()
    {
        $value = $this->ArgumentValue === null ? $this->DefaultValue : $this->ArgumentValue ;
        //检查是否有可访问的查询修改器
        $getValueRefactor = 'get'. $this->getRefactorName() .'Attribue';        
        if( method_exists($this->Resource, $getValueRefactor) )
        {
            $args = [];
            array_unshift($args, $value);
            //
            $value = call_user_func_array([
                $this->Resource, $getValueRefactor
            ], $args);
        }
        return $value;
    }
    /**
     * 输出一个int类型组成的数组
     * @param string $sep 字符串分隔符
     * 
     * @return null|array
     */
    public function GetIntList($sep = ',') : ?array
    {
        if( $this->isEmpty() ){
            return null;
        }
        $value = $this->Get();
        if( $this->type()->isString() ){
            $value = explode($sep, $this->Get());
        }elseif($this->type()->isArray() == false){
            return null;
        }
        foreach( $value as $val ){
            if( !is_int($val) ){
                return false;
            }
        }
        return array_values($value);
    }
    
    public function __toString()
    {
        if($this->type()->isClass())
        { 
            return 'object';
        }
        if($this->type()->isFunction())
        {
            return 'function';
        }
        if($this->type()->isArray())
        {
            return json_encode($this->getValue());
        }
        return (string)$this->getValue();
    }
    /**
    * 只有匿名函数支持的方法，快速调用
    */
    public function closure()
    {
        $args = func_get_args();
        if( $this->isEmpty() == false && $this->type()->isFunction() ){
            return call_user_func_array($this->getValue(), $args);
        }
    }
    /**
    * 通过魔术方法 call get 实现快速调用class实例属性和方法
    */
    public function __get($key)
    {
        if( $this->isEmpty() == false && $this->type()->isClass() ){
            if( property_exists($this->getValue(), $key) )
            {
                return new ParamterValue($key, $this->getValue()->$key, $this->Resource) ;
            }
        }
    }
    public function __call($method, $args)
    {
        if( $this->isEmpty() == false && $this->type()->isClass() ){
            if( property_exists($this->getValue(), $method) )
            {
                return call_user_func_array(
                    [$this->getValue(), $method],
                    $args
                );
            }
            throw new \Exception("Call to undefined method {$this->ArgumentField}::{$method}() ");
        }
        throw new \Exception("Call to a member function {$method}() on null or not Object");
    }
    #endregion
    #region 辅助函数
    
    /**
    * 格式化参数，应对修改器
    */
    private function getRefactorName()
    {
        $str = str_replace('_', ' ', $this->ArgumentField);
        $str = ucwords($str);
        return str_replace(' ', '', $str);
    }
    #endregion
}
