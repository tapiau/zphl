<?php


namespace ZPHL;

class DTO
{
    public function __toString()
    {
        return json_encode($this);
    }
    public function toJson()
    {
        return json_encode($this);
    }
    public function __wakeup()
    {
        return json_encode($this);
    }
    public function __construct()
    {
        $args = func_get_args();
        $className = get_called_class();

        foreach(array_keys(get_class_vars($className)) as $key=>$value)
        {
            if(array_key_exists($key,$args))
            {
                $this->{$value} = $args[$key];
            }
        }
    }
    public static function create(ArrayObject $source)
    {
        $className = get_called_class();
        $target = new $className($source);

        foreach(get_class_vars($className) as $key=>$value)
        {
            $target->{$key} = $source->{$key};
        }

        return $target;
    }
    public static function fromArray(array $array)
    {
        $className = get_called_class();

        $args = [];
        foreach(get_class_vars($className) as $key=>$value)
        {
            $args[] =  $array[$key];
        }

        $target = new $className(...$args);

        return $target;
    }
    public static function fromJson(string $json)
    {
        $json = json_decode($json);

        $className = get_called_class();

        $args = [];
        foreach(get_class_vars($className) as $key=>$value)
        {
            $args[] =  $json->{$key};
        }

        $target = new $className(...$args);

        return $target;
    }
}