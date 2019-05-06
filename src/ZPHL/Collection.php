<?php

namespace ZPHL;

/**
 * User: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 * Date: 28.08.17 16:56
 *
 * based on:
 * http://domexception.blogspot.com/2013/08/php-magic-methods-and-arrayobject.html
 *
 */


/**
 * show off @method
 *
 * @methos Collection change_key_case(int $case = CASE_LOWER) Changes the case of all keys in an array
 * @method Collection chunk(int $size, bool $preserve_keys = false) Split an array into chunks
 * @method Collection column(mixed $column_key, mixed $index_key = null) Return the values from a single column in the input array
 * @method Collection combine(array $values) Creates an array by using self for keys and another for its values
 * @method Collection count_values() Counts all the values of an array
 * @method Collection diff_assoc(array $array) Computes the difference of self and array with additional index check
 * @method Collection diff_key(array $array) Computes the difference of self and array using keys for comparison
 * @method Collection diff_uassoc(array $array,callable $key_compare_func) Computes the difference of self and array with additional index check which is performed by a user supplied callback function
 * @method Collection diff_ukey(array $array,callable $key_compare_func) Computes the difference of self and array using a callback function on the keys for comparison
 * @method Collection diff(array $array) Computes the difference of self and array
 * @method Collection fill_keys(mixed $value) Fill an array with values, specifying self as keys
 * @method Collection fill(int $start_index , int $num , mixed $value) Fill an array with values
 * @method Collection filter(callable $callback, int $flag = 0) Filters elements of an array using a callback function
 * @method Collection flip() Exchanges all keys with their associated values in an array
 * @method Collection intersect_assoc(array $array) Computes the intersection of self and array with additional index check
 * @method Collection intersect_key(array $array) Computes the intersection of self and array using keys for comparison
 * @method Collection intersect_uassoc(array $array, callable $key_compare_func) Computes the intersection of self and array with additional index check, compares indexes by a callback function
 * @method Collection intersect_ukey(array $array, callable $key_compare_func) Computes the intersection of self and array using a callback function on the keys for comparison
 * @method Collection intersect(array $array) Computes the intersection of self and array
 * @method bool key_exists(mixed $key) Checks if the given key or index exists in the array
 * @method Collection keys(mixed $search_value = null, bool $strict = false) Return all the keys or a subset of the keys of an array
 * @method Collection map(callable $callback) Applies the callback to the elements of the given arrays
 * @method Collection merge_recursive(array $array) Merge array recursively
 * @method Collection merge(array $array) Merge array
 * @method Collection multisort(mixed $array1_sort_order = SORT_ASC, mixed $array1_sort_flags = SORT_REGULAR) Sort multi-dimensional arrays
 *
 * @method Collection walk(callback $func) walks through array
 */
class Collection extends \ArrayObject
{
    /**
     * @param array $arr
     */
    public function __construct($arr = [])
    {
        parent::__construct($arr);
    }

    public static function fromArray(array $array)
    {
        return new Collection($array);
    }

    public function toArray()
    {
        return $this->getArrayCopy();
    }
    public function values()
    {
        return array_values($this->getArrayCopy());
    }
    public function first()
    {
        return reset($this);
    }
    public function last()
    {
        return end($this);
    }
    public function key()
    {
        return key($this);
    }
    public function next()
    {
        return next($this);
    }
    public function offsetSet($offset, $value)
    {
        parent::offsetSet($offset, $value);
    }
    public function offsetUnset($offset)
    {
        parent::offsetUnset($offset);
    }

    public function pluck($key, $asKey = null)
    {
        if (is_null($asKey)) {
            return $this->map(function ($item) use ($key) {
                return ((array)$item)[$key];
            });
        } else {
            return $this->reduce(
                function ($array, $item) use ($key, $asKey) {
                    $array[((array)$item)[$asKey]] = ((array)$item)[$key];
                    return $array;
                }
            );
        }
    }

    /**
     * @param $name
     * @param $args
     * @return Collection|mixed
     */

    public function __call($name, $args)
    {
        if(function_exists('array_'.$name))
        {
            foreach($args as $key=> $ar)
            {
                if(is_object($ar) && get_class($ar)==self::class)
                    $args[$key]=(array)$ar;
            }
            set_error_handler(function($a,$b,$c,$d) {throw new \Exception($b);});
            try
            {
                $ref=new \ReflectionFunction('array_'.$name);

                if(current($ref->getParameters())->isPassedByReference()) //walk
                {
                    $newargs=array(&$this);
                }
                else
                {
                    $newargs=array((array)$this);

                }
                foreach($args as $arg)
                {
                    $newargs[]=$arg;
                }
                $r=call_user_func_array('array_'.$name,$newargs);
            }
            catch (\Exception $e)
            {
                array_splice( $args, 2, 0, array((array)$this));
                $r=call_user_func_array('array_'.$name,$args);
            }
            restore_error_handler();
            if(is_array($r))
            {
                return new self($r);
            }
            return $r;
        }

        if(array_search($name,['sort','rsort'])!==false)
        {
            $array = (array)$this;
            $newargs=array(&$array);;
            foreach($args as $arg)
            {
                $newargs[]=$arg;
            }
            call_user_func_array($name,$newargs);
            return new self($array);
        }
    }
}
