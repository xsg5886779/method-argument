<?php

namespace MethodArgument\Library;

/**
* 异常对象
*/
class Error
{
    /**
    * 判断规则
    */
    private $Handle = null;
    /**
    * 系统预调的handle汉化
    */
    private $HandleName = [
        'required'  => '必填项',
        'matches'   => '格式验证',
        'email'     => '邮箱验证',
        'number'    => '数字',
        'range'     => '数字',
        'max'       => '最大值',
        'min'       => '最小值',
        'maxlen'    => '字符长度',
        'minlen'    => '字符长度',
    ];
    /**
    * 自定义输出错误信息
    */
    private $customerMessage = null;
    public function setCustomerMessage( $message )
    {
        $this->customerMessage = $message;
    }
    /**
    * 消息集合
    */
    protected $errorHandles = [];
    
    public function __construct($ValidateHandle = null)
    {
        if($ValidateHandle){
            $this->Handle = lcfirst($ValidateHandle);
        }
    }
    public function getHandle()
    {
        return $this->Handle;
    }
    /**
    * 添加异常
    */
    public function addError($error)
    {
        if($error == null) return ;
        $this->errorHandles[] = $error;
    }
    /**
    * 获得错误条数，可以通过$error->Count()>0来判断是否有错误
    */
    public function Count()
    {
        return count($this->errorHandles);
    }
    
    /**
    * 获得文字错误信息
    *
    * @param array $customerMessage 自定义错误信息
    */
    public function getMessage($customerMessage = null)
    {
        if( $this->count() == 0 )
        {
            return null;
        }
        if($customerMessage)
        {
            $this->customerMessage = $customerMessage;
        }
        /**
        * 只有最小单位才会有handle
        */
        if( $this->Handle != null )
        {
            $message = array_shift($this->errorHandles);
            return $this->formatMessage($message);
        }
        else{
            $message = [];
            foreach( $this->errorHandles as $error )
            {
                $hand = $error->getHandle();
                if( $hand && 
                    !empty($this->customerMessage) && is_array($this->customerMessage) && 
                    isset($this->customerMessage[$hand]) 
                ){
                    $error->setCustomerMessage($this->customerMessage[$hand]);
                }
                elseif( !empty($this->customerMessage) && is_string($this->customerMessage) ){
                    $error->setCustomerMessage($this->customerMessage);
                }
                $message[] = $error->getMessage();
            }
            return implode(',', $message);
        }
        
    }
    private function formatMessage($message)
    {
        $return = $this->customerMessage;
        if( empty($message) ) return '';
        if( empty($return) ) return $message;
        $rule = $this->HandleName[ $this->Handle ]?? $this->Handle ;
        $return = str_replace('{rule}', $rule, $return);
        $return = str_replace('{error}', $message, $return);
        
        return $return;
    }
}