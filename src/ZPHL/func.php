<?php

// OBJECT

function object($array=array(),$recursive=true)
{
//	$obj = ((object) NULL);
	$obj = new BaseObject();
	foreach($array as $key=>$value)
	{
		if(is_integer($recursive))
		{
			$recursive--;

			if($recursive===0)
			{
				$recursive = false;
			}
		}
		if(is_array($value) && $recursive)
		{
			$value = object($value,$recursive);
		}
		$obj->$key = $value;
	}
	return $obj;
}

// UTIL

function now()
{
	return date('Y-m-d H:i:s');
}

// STRING

function str_endswith($haystack,$needle)
{
	return substr($haystack,-strlen($needle))==$needle;
}
function str_beginswith($haystack,$needle)
{
	return substr($haystack,0,strlen($needle))==$needle;
}
function str_contains($haystack,$needle)
{
	return strpos($haystack,$needle)!==false;
}
function str_between($str,$start,$stop)
{
    $start = strpos($str,$start)+strlen($start);
    $stop = strpos($str,$stop);
    return substr($str,$start,$stop-$start);
}

// ARRAY

function array_merge_recursive_overwrite(/*$array1,$array2...*/)
{
	$arrayList = func_get_args();

	$arr1 = array_shift($arrayList);
	$arr2 = array_shift($arrayList);

	foreach($arr2 as $key=>$value)
	{
		if(array_key_exists($key, $arr1) && is_array($value))
		{
			$arr1[$key] = array_merge_recursive_overwrite($arr1[$key], $arr2[$key]);
		}
		else
		{
			if(!empty($value))
			{
				$arr1[$key] = $value;
			}
		}
	}

	if(!empty($arrayList))
	{
		array_unshift($arrayList,$arr1);

		$arr1 = call_user_func_array('array_merge_recursive_overwrite',$arrayList);
	}

	return $arr1;
}

function array_qsort(&$array, $column=0, $order=SORT_ASC)
{
	$dst = array();
	$sort = array();

	foreach($array as $key => $value)
	{
		if(is_array($value))
		{
			$sort[$key] = $value[$column];
		}
		else
		{
			$sort[$key] = $value->$column;
		}
	}
	if($order == SORT_ASC)
	{
		asort($sort);
	}
	else
	{
		arsort($sort);
	}

	foreach($sort as $key=>$value)
	{
		$dst[(string)$key] = $array[$key];
	}
	$array = $dst;
}

// DEBUG

function backtrace()
{
	return array_map(
		function($row){unset($row['object']); return $row;},
		debug_backtrace()
	);
}

function printr($tab)
{
	$dbg = debug_backtrace();

	$file = "{$dbg[0]['file']}:{$dbg[0]['line']}";
//	$file = str_replace(__ROOT,'',$file);

	$id = uniqid();

	if(isCli())
	{
		echo "================================================================================\n";
		echo "    {$dbg[0]['file']}:{$dbg[0]['line']}\n";
		echo "--------------------------------------------------------------------------------\n";
		print_r($tab);
		echo "\n";
		echo "================================================================================\n";
	}
	else
	{
		$out = "<pre style='color: #000000; background-color: #efefef; border: 1px solid #aaaaaa; text-align: left;'><iframe name='{$id}' style='display: none;'></iframe>";
		$out .= "<div style='font-weight: bold; background-color: #FFF15F; border-bottom: 1px solid #aaaaaa; color: 000000;'>\n<a href='http://localhost:8091/?message={$file}' target='{$id}'>{$dbg[0]['file']}:{$dbg[0]['line']}</a>\n</div>\n";
		$out .= print_r($tab,true);
		$out .= "\n</pre>\n";
		$out .= "\n";

		echo $out;

		ob_flush();
		flush();
	}
}

function paranoid($error = null)
{
	set_error_handler(
		function ($errno, $errstr, $errfile, $errline)
		{
			$exception = new Exception($errstr . '; File: '.$errfile.':'.$errline, $errno);
			throw $exception;
		}
	);

	register_shutdown_function('paranoidError');
}

function paranoidError()
{
	if(@is_array($error = @error_get_last()))
	{
		echo "<pre>";
		print_r($error);
	}
}

// TESTS

if(!function_exists('is_iterable'))
{
    function is_iterable($obj,$interface=false)
    {
        return
            is_object($obj) ?
                $interface ?
                    array_search('Iterator',class_implements($obj))!==false
                    :
                    true
                :
                is_array($obj)
            ;
    }
}

function isCli() {

    if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR'])) {
        return true;
    } else {
        return false;
    }
}

function isWindows()
{
	$windows = false;

	if(array_key_exists('OS',$_SERVER))
	{
		if($_SERVER['OS']=='Windows_NT')
		{
			$windows = true;
		}
	}
	if(array_key_exists('DOCUMENT_ROOT',$_SERVER))
	{
		if(str_contains($_SERVER['DOCUMENT_ROOT'],':/'))
		{
			$windows = true;
		}
	}

	return $windows;
}

function not_empty($val)
{
    $val = trim($val);
    return !empty($val);
}
