<?php

namespace MethodArgument;

use MethodArgument\Library\ParamterValue;
use MethodArgument\Library\ArgumentTools;
use MethodArgument\Library\Error;
/**
*
* 生命周期：Argument::verify() 会将所有己填参数和配置生成ParamterValue实例存储到OriginalFields中
*                              如果没有触发Argument::verify()，则在Argument::__get 时，实例ParamterValue存储到OriginalFields中
*           
*           实例ParamterValue后，Argument::addFieldRule将不生效
*           
*/
class Argument
{
    use ArgumentTools;
    
    /**
    * 默认值设置
    */
    protected $defaultFields = [];
    /**
    * 必填参数设置
    */
    protected $mustFields = [];
    public function setMust($key)
    {
        if( is_array($key) ){
            foreach($key as $_key)
            {
                if( is_string($_key) ){
                    $this->mustFields[] = $_key;
                }
            }
        }
        elseif( is_string($key) ){
            $this->mustFields[] = $key;
        }
    }
    
    /**
    * 字段验证规则
    */
    protected $verifyFields = [];
    /**
    * 按字段设置验证规则（当$field 为数组的时候且$rule为空，则视为批量设置）
    *
    * @des 支持规则：
    *            required：必填（不能为null,不能为空）, 
    *            matches：正则表达式, 
    *            email：邮件, 
    *            number：数字, 
    *            range：区间数字（仅数字类型）, 
    *            max：最大数字（仅数字类型）,
    *            min：最小数字（仅数字类型）,
    *            maxlen：最大字符长度（仅string）,
    *            minlen：最小字符长度（仅string）
    *
    * @规则A
    *   @param string $field 字段名
    *   @param array $rule  ["required","max:10"]
    * @规则B
    *   @param string $field 字段名
    *   @param array $rule  [
    *                           "required",
    *                           "max" => 12 //max只支持一个参数
    *                           "range" => [10,12]  //number验证规则支持两个参数
    *                           "customer" => […… args]  //自定义验证规则的参数不因定
    *                       ]
    * @规则C
    *   @param string $field ["参数A" => "required",  "参数B" =>"max:10"]
    */
    public function setRule( $field, $rule = null )
    {
        if(is_array($field) && $rule == null){
            foreach($field as $f => $r)
            {
                $this->addFieldRule($f, $r);
            }
            return ;
        }
        $this->addFieldRule($field, $rule);
    }
    private function addFieldRule($field, $rule)
    {
        if(empty($rule)){
            return ;
        }
        $this->verifyFields[$field] = $rule;
    }
    
    /**
    * 必填项验证模式（true=一次性验证所有必填项/false只要
    * @type Proterty
    */
    protected $fullValidationMode = false;
    /**
    * 存储创建ParamterValue实例对象
    */
    private $OriginalFields = [];
    
    /**
    * 存储所有参数值
    */
    private $fields = [];
    
    
    public function __get($key)
    {
        return $this->getArgument( $key );
    }
    public function __set($key, $value)
    {
        return $this->addArgument($key, $value);
    }
    /**
    * 进行所有参数的校验
    */
    public function verify()
    {
        $error = [];
        foreach($this->fields as $key => $value)
        {
            $Paramter = $this->getArgument($key);
            if($Paramter->verify() !== true)
            {
                $error[$key] = $Paramter->getError();
                //如果是非一次验证，则发现错误就退出
                if(!$this->fullValidationMode)
                {
                    break;
                }
            }
        }
        return $error;
    }
    /**
    * 添加一个参数
    */
    protected function addArgument($key, $value)
    {
        $this->fields[$key] = $value;
    }
    /**
    * 获得一个参数值
    */
    protected function getArgument($key)
    {
        $argumentValue = $this->fields[$key] ?? null;
        
        if( !isset($this->OriginalFields[$key]) )
        {
            if( $this->hasArgumentFromMethod($key) ){
                $argumentValue = $this->getArgumentFromMethod($key, $argumentValue);
            }
            $ParamterValue = new ParamterValue($argumentValue, $this);
            //设置验证规则
            $ParamterValue->setValidation( $this->parseVerifyFields($key) );
            //设置默认值
            $ParamterValue->setDefaultValue( $this->getDefaultValue($key) );
            //存储实例化
            $this->OriginalFields[$key] = $ParamterValue;
        }
        
        
        return return $this->OriginalFields[$key];
    }
    
}