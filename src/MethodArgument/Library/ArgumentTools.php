<?php

namespace MethodArgument\Library;

use MethodArgument\Library\ParamterValue;

Trait ArgumentTools
{
    protected $systemVerifyHandle = ["required", "matches", "email", "number", "range", "max","maxlen","minlen","min"];
    
    /**
    * 获取一个参数的默认值
    */
    public function getDefaultValue($key)
    {
        return $this->defaultFields[$key] ?? null ;
    }
    /**
    * 解析一个参数的结构
    *
    * @example 
    */
    public function parseVerifyFields($key)
    {
        $validation = $this->verifyFields[$key] ?? null;
        //如果是null则不存在
        if( $validation == null ){
            return null;
        }
        /**
        * 如果是字符串格式，支持:
        * 
        * required 必填 "" || null 均不通过
        * email 邮箱
        * int|max:value、min:value、range:max:min
        * string|maxlen:value、minlen:value
        * string|matches:regex
        * 
        */
        if(gettype($validation) == "string")
        {
            return $this->parseStringVerify( $validation );
        }
        /**
        * 如果是数组格式的，则支持：
        *
        * @example A:
        *   ["required","max:10"]
        * @example B:
        *   [
        *       "required",
        *       "max" => 12 //max只支持一个参数
        *       "number" => [10,12]  //number验证规则支持两个参数
        *       "customer" => […… args]  //自定义验证规则的参数不因定
        *   ]
        */
        if(gettype($validation) == 'array')
        {
            return $this->parseArrayVerify( $validation );
        }
        return null;
    }
    /**
    * 解析一个字符串的验证格式
    */
    private function parseStringVerify($string)
    {
        $get_data = explode('|', $string);
        
        $validation = [];
        foreach($get_data as $_validat)
        {
            $validation[] = $this->getVerifyStructureFromString($_validat);
        }
        return $validation;
    }
    /**
    * 解析一个数组结构的验证格式
    */
    private function parseArrayVerify($validation)
    {
        $validation = [];
        foreach($validation as $formula => $args)
        {
            //["required","max:10"]
            if( is_int($formula) && is_string($args) )
            {
                $validation[] = $this->getVerifyStructureFromString($args);
                continue;
            }
            elseif( is_int($formula) ){
                continue;
            }
            $customer   = false;
            if( !in_array($formula, $this->systemVerifyHandle) )
            {
                if( $this->hasCustomerVerify($formula) == false ){
                    continue;
                }
                $customer = true;
            }
            $validation[] = [
                'handle'   => $formula,
                'customer' => $customer,
                'argument' => (array)$args
            ];
        }
        return $validation;
    }
    private function getVerifyStructureFromString($validation)
    {
        $args       = explode(":", $validation);
        $formula    = array_shift($args);
        $customer   = false;
        if( !in_array($formula, $this->systemVerifyHandle) )
        {
            if( $this->hasCustomerVerify($formula) == false ){
                return null;
            }
            $customer = true;
        }
        return [
            'handle'   => $formula,
            'customer' => $customer,
            'argument' => $args
        ];
    }
    /**
    * 判断是否存在自定义验证规则
    */
    private function hasCustomerVerify($formula)
    {
        $method = "set" . ucfirst($formula) . "Validation";
        return method_exists($this, $method) == true;
    }
    /**
    * 判断是否存在一个属性获取器
    */
    public function hasArgumentFromMethod( $key )
    {
        $method = $this->getArgumentMethodCallName($key);
        
        return method_exists($this, $method) == true;
    }
    /**
    * 通过一个属性获取器来处理参数值
    */
    public function getArgumentFromMethod($key, $argumentValue)
    {
        $method = $this->getArgumentMethodCallName($key);
        //
        
        if(method_exists($this, $method))
        {
            $argumentValue = call_user_func_array([$this, $method], [$argumentValue]);
        }
        
        return $argumentValue;
    }
    /**
    * 属性获取器名称生成
    */
    public function getArgumentMethodCallName($key)
    {
        return "get" . ucfirst($key) . "Attribue";
    }
}