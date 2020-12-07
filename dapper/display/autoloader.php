<?php
declare(strict_types = 1);
spl_autoload_register(function($className) {
    $namespace=str_replace("\\","/",__NAMESPACE__);
    $className=str_replace("\\","/",$className);
    $class=__DIR__."/../../classes/".(empty($namespace)?"":$namespace."/")."{$className}.php";
    include_once($class);
  });
