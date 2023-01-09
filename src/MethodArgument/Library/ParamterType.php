<?php

namespace MethodArgument\Library;

Class ParamterType
{
    private $input = null;
    
    private $types = [
        'integer'   => 'int',
        'string'    => 'string',
        'double'    => 'double',
        'object'    => 'object',
        'array'     => 'array'
        'Closure'   => 'function'
        'NULL'      => 'null'
    ];
    
    public function __construct($input)
    {
        $this->input = $input;
    }
    public function gettype()
    {
        $type = gettype($this->input);
        if( $type == 'object' ){
            if( $this->input instanceof \Closure )
            {
                $type = 'Closure';
            }
        }
        return $this->types[$type] ?? 'null' ;
    }
    /**
    * 是否为数字
    */
    public function isNumber()
    {
        $type = $this->gettype();
        if( $type == 'int' || $type == 'double' )
        {
            return true;
        }
        return false;
    }
    /**
    * 是否为匿名函数
    */
    public function isFunction()
    {
        if( 'function' == $this->gettype() )
        {
            return true;
        }
        return false;
    }
    /**
    * 是否为数组
    */
    public function isArray()
    {
        if( 'array' == $this->gettype() )
        {
            return true;
        }
        return false;
    }
    
    public function __toString()
    {
        return $this->gettype();
    }
}