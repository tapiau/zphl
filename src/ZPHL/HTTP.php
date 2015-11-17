<?php

/**
 * User: Zbigniew 'zibi' Jarosik <zibi@nora.pl>
 * Date: 23.06.14 14:29
 */

class HTTP
{
	private $referer = null;
	private $params = array();
	private $url = null;
	private $body = null;
	private $mime = null;
	private $info = array();
	private $post = array();

	/**
	 * @param null $url string
	 */
	public function __construct($url = null)
    {
        $this->url = $url;
    }

	/**
	 * @return null|string
	 */
	public function getUrl()
    {
		$url = $this->url;
//		$url = str_replace(' ','%20',$url);

		if(count($this->params))
		{
			$url .= '?'.http_build_query($this->params);
		}

		return $url;
	}

	/**
	 * @return $this
	 */
	public function request()
	{
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

		if(!empty($this->post))
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
		}

		if(!is_null($this->referer))
		{
			curl_setopt($ch, CURLOPT_REFERER, $this->referer);
		}

		$ret = curl_exec($ch);
		$c = curl_getinfo($ch);

		$this->body = $ret;
		$this->mime = $c['content_type'];
		$this->info = $c;

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function setPost($post)
	{
		$this->post = $post;

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getBody()
	{
		return $this->body;
	}
	/**
	 * @return null|string
	 */
	public function getMime()
	{
		return $this->mime;
	}
	/**
	 * @return null|array
	 */
	public function getInfo()
	{
		return $this->info;
	}

	/**
	 * @param null $referer
	 * @return $this
	 */
	public function setReferer($referer = null)
	{
		$this->referer = $referer;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getReferer()
	{
		return $this->referer;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function setParam($key,$value)
	{
		$this->params[$key] = $value;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}


}
