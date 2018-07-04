<?php

namespace ZPHL;

class Webpage
{
	private $url = '';
	private $body = '';
	private $mime = '';
	private $infoList = [];

    public function setUrl($url)
    {
        $this->url = $url;

		return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

	public function setBody($body)
	{
		$this->body = $body;

		return $this;
	}

	public function getBody()
	{
		return $this->body;
	}

	public function setMime($mime)
	{
		$this->mime = $mime;

		return $this;
	}

	public function getMime()
	{
		return $this->mime;
	}

    public function setInfo($name,$value=null)
    {
		if(is_array($name)&&is_null($value))
		{
			$this->infoList = $name;
		}
		else
		{
			$this->infoList[$name] = $value;
		}

		return $this;
    }

    public function getInfo($name=null)
    {
		return is_null($name)
			?
			$this->infoList
			:
			$this->infoList[$name]
		;
    }
}
