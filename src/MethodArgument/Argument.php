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

    protected $systemVerifyHandle = ["required", "matches", "email", "number", "range", "max","maxlen","minlen","min"];

    private $verifyError = [];
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
                    $this->setMust($_key);
                }
            }
        }
        elseif( is_string($key) ){
            $this->mustFields[] = $key;
            //更新已生成字段
            if( isset($this->OriginalFields[$key]) )
            {
                $this->OriginalFields[$key]->setMust(true);
            }
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
        //更新已生成字段
        if( isset($this->OriginalFields[$field]) )
        {
            $this->OriginalFields[$field]->setValidation($rule);
        }
    }
    /**
    * 自定义字段验证失败提示
    *
    * 规则 [paramter => [rule => message] ]
    * @example  ['user_id' => ['required' => 'user_id必填'] ]
    */
    protected $verifyErrorMessage = [];
    /**
    * 添加一个自定义错误提示
    *
    * @param string $ParamterAndRule  规则路径 'user_id.required' = user_id字段下的字段必填验证
    * @param string $message
    */
    public function addVerifyError($ParamterAndRule, $message)
    {
        if( $ParamterAndRule && strpos($ParamterAndRule, '.') !== false && !empty($message))
        {
            list($paramterKey, $validateHandle) = explode('.', $ParamterAndRule);
            if(!isset($this->verifyErrorMessage[$paramterKey]))
            {
                $this->verifyErrorMessage[$paramterKey] = [];
            }
            $this->verifyErrorMessage[$paramterKey][lcfirst($validateHandle)] = $message;
        }
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
    *
    * @param array | null $customerErrorMessage 自定义错误信息 。 格式[ paramterKey.rule => message ]
    * @return boolean false时，可以通过Argument::getError()获得错误信息
    */
    public function verify($customerErrorMessage = null)
    {
        if( !empty($customerErrorMessage) && is_array($customerErrorMessage) )
        {
            foreach($customerErrorMessage as $paramter => $message)
            {
                $this->addVerifyError($paramter, $message);
            }
        }
        $paramters = array_keys($this->fields);
        $paramters = array_unique( array_merge($paramters, $this->mustFields) );
        foreach($paramters as $key)
        {
            $verify = $this->verifyParamter( $key );

            //如果是非一次验证，则发现错误就退出
            if(!$this->fullValidationMode && $verify !== true)
            {
                break;
            }
        }
        return count($this->verifyError) == 0;
    }
    /**
    * 按参数名进行验证
    */
    public function verifyParamter($paramter)
    {
        //获得自定义错误信息
        $verifyMessage = $this->verifyError[$paramter] ?? null ;
        //获得参数ParamterValue
        $Paramter = $this->getArgument($paramter);
        if(($error = $Paramter->verify($verifyMessage)) !== true)
        {
            $this->verifyError[$paramter] = $error;
        }
        return $error;
    }
    /**
    * 获得验证错误信息
    */
    public function getError()
    {
        $collection = [];
        foreach($this->verifyError as $key => $error)
        {
            $collection[] = $error->getMessage();
        }
        return $collection;
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
            $ParamterValue = new ParamterValue($key, $argumentValue, $this);
            $ParamterValue->setIsSet( isset($this->fields[$key]) );

            //必填项自动生成验证规则
            if(is_array($this->mustFields)){
                $ParamterValue->setIsMust( in_array($key, $this->mustFields) );
            }
            //设置自定义错误信息
            $customerVerifyError = null;
            if(isset($this->verifyErrorMessage[$key]))
            {
                $customerVerifyError = $this->verifyErrorMessage[$key];
            }
            //设置验证规则
            if( isset($this->verifyFields[$key]) ){
                $ParamterValue->setValidation( $this->verifyFields[$key], $customerVerifyError );
            }
            //设置默认值
            $ParamterValue->setDefaultValue( $this->getDefaultValue($key) );
            //存储实例化
            $this->OriginalFields[$key] = $ParamterValue;
        }


        return $this->OriginalFields[$key];
    }
    
}
