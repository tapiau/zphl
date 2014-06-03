<?php
/**
 * User: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 * Date: 07.03.14 10:20
 */

class Config
{
	public static $config = null;
	private static $file = null;

	public static function get($path=null)
	{
		$config = self::$config;

		if(!is_null($path))
		{
			$path = trim($path,'/');

			$chunks = explode('/',$path);

			foreach($chunks as $chunk)
			{
				if(isset($config->{$chunk}))
				{
					$config = $config->{$chunk};
				}
				else
				{
					throw new Exception("Config::/{$path} not found");
				}
			}
		}

		return $config;
	}
	public static function load($file = null,$stage = 'production')
	{
		if(is_null($file))
		{
			$file = __ROOT.'/config.ini';
		}

		self::$file = $file;

		$tmp = IniParser::parse(self::$file, true);
		if(@file_exists(self::$file.'.local'))
		{
			$tmp2 = IniParser::parse(self::$file.'.local', true);
			$tmp = IniParser::merge($tmp, $tmp2);
		}
		self::$config = object($tmp[$stage]);

		return self::$config;
	}
	public static set($array)
	{
		self::$config = object($array);

		return self::$config;
	}
}
