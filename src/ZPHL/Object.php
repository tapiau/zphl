<?php

namespace ZPHL;

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
