<?php

namespace ZPHL;

class BaseObject
{
	public function __construct($array=array())
	{
		foreach($array as $key=>$value)
		{
			if(is_array($value))
			{
				$value = new self($value);
			}
			$this->$key = $value;
		}
	}
	public function __get($name)
	{
		return isset($this->{$name})?$this->{$name}:null;
	}
}
