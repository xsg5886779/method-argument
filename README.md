# method-argument

# 介绍 #
Argument的基类可以直接使用。<br />
所有的自定义参数类都需要继承一个Argument的基类进行处理，支持标准判断和操作。<br />

## 标准操作参考 ##
    ○ 参数实现属性读取的方式处理。
    ○ 支持必填项判断。
    ○ 支持类型判断。
    ○ 支持自定义字段名设置以及判断规则。
    ○ 支持一次性必填项错误提示。

## 扩展自定义参数类 ##
### 为什么要扩展自定义参数类？###
扩展自定义的参数类目的是为了对基础操作进行二次封装，进行更精细的处理。例如：对默认值的处理、自定义判断规则、对ArgumentBase预设配置默认处理等。
### 扩展能做哪些二次封装？###
    ○ 配置protected $defaultFields 属性对参数的默认值预设。
    ○ 配置protected (boolean) $fullValidationMode 必填项验证模式（true=一次性验证所有必填项/false只要有一个必填项就停止继续验证）
    ○ 配置protected (array) $verifyFields 设置字段验证规则包括必填项字段列表。
    ○ 通过method get{参数名}Attribue 方法对参数返回值封装。
    ○ 通过method set{规则名}Validation 方法创建自定义验证规则。
