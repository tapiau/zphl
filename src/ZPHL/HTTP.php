<?php

namespace ZPHL;

use ZPHL\Exception\HTTP\WrongRequestType;

class HTTP
{
	private $url = '';
	private $referer = '';
	private $type = 'GET';
	private $paramList = [];
	private $headers = [];
	private static $cache = null;

	public static function setCache($cache)
	{
		self::$cache = $cache;
	}

    public function setUrl($url)
    {
        $this->url = $url;

		return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

	public function setReferer($url)
	{
		$this->referer = $url;

		return $this;
	}

	public function getReferer()
	{
		return $this->referer;
	}

    public function setHeader($name, $value)
    {
        $this->headers[$name] = "{$name}: {$value}";

        return $this;
    }

    public function setParam($name, $value)
    {
        $this->paramList[$name] = $value;

		return $this;
    }

    public function setParams($body)
    {
        $this->paramList = $body;

        return $this;
    }

    public function getParam($name)
    {
        return $this->paramList[$name];
    }

    public function setType($type)
    {
		$type = strtoupper($type);

		if(array_search($type,['GET','POST'])===false)
		{
			throw new WrongRequestType();
		}

		$this->type = $type;

		return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function request()
    {
		$hash = md5(json_encode([
			'url'=>$this->getUrl(),
			'params'=>$this->paramList,
			'referer'=>$this->referer
		]));

		if(!is_null(self::$cache))
		{
			$page = self::$cache->{$hash};

			if(!is_null($page))
			{
//				printrlog("HTTP request from cache");

				return $page;
			}
		}

		$ch = curl_init(); // create cURL handle (ch)
		if (!$ch)
		{
			die("Couldn't initialize a cURL handle");
		}

		// set some cURL options
		curl_setopt($ch, CURLOPT_URL,            $this->getUrl());
		curl_setopt($ch, CURLOPT_HEADER,         false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT,        15);

		if($this->type=='POST')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->paramList);
		}

		if(!is_null($this->referer))
		{
			curl_setopt($ch, CURLOPT_REFERER, $this->referer);
		}
        if(is_array($this->headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($this->headers));
        }

		$ret = curl_exec($ch);
		$c = curl_getinfo($ch);

		$page = new Webpage();
		$page->setUrl($this->getUrl());
		$page->setBody($ret);
		$page->setMime($c['content_type']);
		$page->setInfo($c);

		if(!is_null(self::$cache))
		{
			self::$cache->{$hash} = $page;
		}

//		printrlog("HTTP request from web");

		return $page;
    }
}
