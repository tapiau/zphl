<?php

define('__ROOT',realpath(dirname(__FILE__).'/..'));
define('__LIB',realpath(dirname(__FILE__)));
//define('__TMP','/tmp/');

//ini_set('include_path',dirname(__FILE__).PATH_SEPARATOR.ini_get('include_path'));
//autoload();

//set_error_handler(
//    function ($errno, $errstr, $errfile, $errline)
//    {
//        $exception = new Exception($errstr . '; File: '.$errfile.':'.$errline, $errno);
//        throw $exception;
//    }
//);

function array_grep($tab,$word)
{
	return array_filter(
		$tab,
		function ($row) use ($word)
		{
			return strpos($row,$word)!==false;
		}
	);
}

function isCli()
{
	return (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
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

class Object
{
	public function __construct($array=array())
	{
		foreach($array as $key=>$value)
		{
			if(is_array($value))
			{
				$value = new Object($value);
			}
			$this->$key = $value;
		}
	}
	public function __get($name)
	{
		return isset($this->{$name})?$this->{$name}:null;
	}
}

function object($array=array())
{
//	$obj = ((object) NULL);
	$obj = new Object();
	foreach($array as $key=>$value)
	{
		if(is_array($value))
		{
			$value = object($value);
		}
		$obj->$key = $value;
	}
	return $obj;
}

function printr($tab)
{
	$dbg = debug_backtrace();

	$file = "{$dbg[0]['file']}:{$dbg[0]['line']}";
	$file = str_replace(__ROOT,'',$file);

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

function requestFilter($tab)
{
	$out = array();

	foreach($tab as $key=>$value)
	{
		if(is_iterable($value))
		{
			$out[$key] = requestFilter($value);
			continue;
		}

		if(!str_contains($key,'.'))
		{
			$out[$key] = $value;
		}
		else
		{
			$prefixList = explode('.',$key);

			$arr = &$out;
			foreach($prefixList as $prefixStr)
			{
				if(!array_key_exists($prefixStr,$arr))
				{
					$arr[$prefixStr] = array();
				}
				$arr = &$arr[$prefixStr];
			}

			$arr = $value;
		}
	}

	return $out;
}

function request($paramName=false,$default=false)
{
	$params = requestGet();

	foreach($_POST as $key=>$value)
	{
		$params[$key]=$value;
	}
	foreach($_GET as $key=>$value)
	{
		$params[$key]=$value;
	}
	foreach($params as $key=>$value)
	{
		if(str_contains($key,'['))
		{
			unset($params[$key]);
		}
	}

	$params = requestFilter($params);

	return $paramName?(isset($params[$paramName])?$params[$paramName]:$default):object($params);
}

function requestGet($paramName=false,$default=false)
{
	global $config;
	//        $request=$_SERVER["REQUEST_URI"];

	$request = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:(isset($_SERVER['REDIRECT_URL'])?$_SERVER['REDIRECT_URL']:$_SERVER["REQUEST_URI"]);
	$request .= isset($_SERVER['QUERY_STRING'])?'?'.$_SERVER['QUERY_STRING']:'';

	if(isset($config->nginx))
	{
		if($config->nginx == 1)
		{
			$request = urldecode($request);
		}
	}

	if(isset($config->request))
	{
		$names = (array)$config->request;
	}
	else
	{
		$names = array();
	}

	$params=array();

	if(strpos($request,'?',0)!==false)
	{
		list($path,$paramsStr)=explode('?',$request);
	}
	else
	{
		$path = $request;
		$paramsStr='';
	}
	$path = explode('/',trim($path,'/'));

	while($node = array_shift($path))
	{
		if($node!='')
		{
			if($name = array_shift($names))
			{
				$params[$name]=$node;
			}
			else
			{
				$value = array_shift($path);
				$params[$node] = $value;
			}
		}
	}

	if($paramsStr!='')
	{
		$paramsArray=explode('&',$paramsStr);

		foreach($paramsArray as $param)
		{
			if(strpos($request,'=',0)!==false)
			{
				list($key,$value)=explode('=',$param);
			}
			else
			{
				$key = $param;
				$value = true;
			}

			$params[$key]=$value;
		}
	}

	return $paramName?(isset($params[$paramName])?$params[$paramName]:$default):$params;
}

function url($params=array(),$mod=array())
{
	$names = array();
	$chunks = array();

	foreach($mod as $key=>$value)
	{
		$params[$key]=$value;
	}

	foreach($params as $paramName=>$paramValue)
	{
		if($paramValue!== null)
		{
			if(array_search($paramName,$names,true)===false)
			{
				$chunks[] = $paramName;
			}
			$chunks[] = $paramValue;
		}
	}

	return '/'.join('/',$chunks).'/';
}

function autoload()
{
	//    ini_set('unserialize_callback_func','spl_autoload_call');
	//    spl_autoload_register("_autoload");

	function __autoload($class_name)
	{
		$filename = str_replace('_','/',$class_name) . '.php';

		$found = false;
		foreach(explode(PATH_SEPARATOR,ini_get('include_path')) as $path)
		{
			if(file_exists($path.'/'.$filename))
			{
				$found = $path.'/'.$filename;
			}
		}

		if(!$found)
		{
			//			printr($filename);
			//			$dbg = debug_backtrace();
			//			printr($dbg);

			throw new Exception('Class not found: '.$class_name);
		}

		require_once $found;
	}
}

function _autoload($class_name)
{
	$filename = str_replace('_','/',$class_name) . '.php';

	$found = false;
	foreach(explode(PATH_SEPARATOR,ini_get('include_path')) as $path)
	{
		if(file_exists($path.'/'.$filename))
		{
			$found = $path.'/'.$filename;
		}
	}

	if(!$found)
	{
		//			printr($filename);
		//			$dbg = debug_backtrace();
		//			printr($dbg);

		throw new Exception('Class '.$class_name.' not found');
	}

	require_once $found;
}

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

function array_csort(&$array, $column=0, $order=SORT_ASC)
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

function now()
{
	return date('Y-m-d H:i:s');
}
function today()
{
	return date('Y-m-d');
}

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

function backtrace()
{
	return array_map(
		function($row){unset($row['object']); return $row;},
		debug_backtrace()
	);
}

function array_merge_recursive_overwrite($arr1, $arr2)
{
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

	return $arr1;
}

function safePath($path)
{
	$path = preg_replace('/\/+/','/',$path);
	$path = preg_replace('/\.+/','.',$path);
	$path = str_replace('/./','/',$path);

	if(str_beginswith($path,'http:/'))
	{
		$path = 'http://'.substr($path,6);
	}

	return $path;
}

function safePathRelative($path)
{
	$path = safePath($path);

	if(str_beginswith($path,'/'))
	{
		$path = substr($path,1);
	}

	return $path;
}

function parseCLI($argv,$inputs=array())
{
        $ret = array('param'=>array(),'flag'=>array(),'input'=>array());
        $n=false;

        foreach($argv as $arg)
        {
                // named param
                if(substr($arg,0,2)==='--')
                {
                        $value = preg_split( '/[= ]/', $arg, 2 );
                        $param = substr( array_shift($value), 2 );
                        $value = join('',$value);

                        $ret['param'][$param] = !empty($value) ? $value : true;
                        continue;
                }
                // flag
                if(substr($arg,0,1)==='-')
                {
                        for($i=1;isset($arg[$i]);$i++)
                        {
                                $flag = substr($arg,$i,1);
                                if($flag!=='-')
                                {
                                        $ret['flag'][$flag]=(substr($arg,$i+1,1)=='-')?false:true;
                                }
                        }
                        continue;
                }
                if(substr($arg,0,1)==='+')
                {
                        $flag = substr($arg,1,1);
                        $ret['flag'][$flag]=true;
                        continue;
                }

                if(count($inputs)&&$n)
                {
                        $ret['input'][array_shift($inputs)]=$arg;
                }
                else
                {
                        $ret['input'][]=$arg;
                }
                $n=true;
        }

        return $ret;
}

