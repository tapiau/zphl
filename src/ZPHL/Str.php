<?php

/**
 * User: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 * Date: 28.08.17 16:56
 *
 * http://domexception.blogspot.com/2013/08/php-magic-methods-and-arrayobject.html
 *
 */

namespace ZPHL;

class Str
{
    private $string = '';

    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function __toString()
    {
        return $this->string;
    }

    /**
     * @param $name
     * @param $args
     * @return Arr|mixed
     */

    public function __methodExists($name)
    {
        $methodList = get_class_methods(self::class);

        if(str_beginswith($name,'str_'))
        {
            $name = substr($name,4);
        }
        if(str_beginswith($name,'str'))
        {
            $name = substr($name,3);
        }

        return array_search($name,$methodList)!==false;
    }

    public function __call($name, $args)
    {
        $name = from_camel_case($name);

        if($this->__methodExists($name))
        {
            $name = substr($name,4);
            return call_user_func_array([$this,$name],$args);
        }

        if (!function_exists($name))
        {
            if(function_exists("str_{$name}"))
            {
                $name = "str_{$name}";
            }
            if(function_exists("str{$name}"))
            {
                $name = "str{$name}";
            }
        }

        if (function_exists($name))
        {
            $newargs=array($this);
            foreach($args as $arg)
            {
                $newargs[]=$arg;
            }
            $ret = call_user_func_array($name,$newargs);

            if(is_string($ret))
            {
                $ret = new self($ret);
            }
            return $ret;
        }
    }
    public function explode($delimiter)
    {
        return explode($delimiter,$this);
    }
    public function replace($search,$replace)
    {
        return new self(str_replace($search,$replace,$this));
    }
    public function ireplace($search,$replace)
    {
        return new self(str_ireplace($search,$replace,$this));
    }
    public function icmp($str)
    {
        return $this->strcasecmp($this,$str);
    }
    public function substr_compare(string $str , int $offset , int $length = NULL , bool $case_insensitivity = FALSE)
    {
        return substr_compare($this,$str,$offset,$length,$case_insensitivity);
    }
}
