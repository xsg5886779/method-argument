<?php

namespace MethodArgument\Library\Contact;

/**
* 异常对象
*/
interface ValidateInterface
{
    public function __construct($value, $arguments);
    /**
    * 获得错误信息
    */
    public function getError();
    /**
    * 执行验证
    */
    public function verify();
}