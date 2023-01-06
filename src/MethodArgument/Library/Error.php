<?php

namespace MethodArgument\Library;

/**
* 异常对象
*/
class Error
{
    protected $errorHandles = [];
    
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
}