<?php

namespace MethodArgument\Library\Contact;

/**
* 异常对象
*/
interface ValidateInterface
{
    public function __construct($value, $arguments);
    /**
    * 执行验证
    */
    public function verify();
}