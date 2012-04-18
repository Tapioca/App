<?php

namespace Tapioca\Jobs;

use Tapioca;

class PHP_Job
{
	private $appid = '4f7977b4c68deebf01000000';
	private $namespace = 'articles';

	public function perform()
	{
		//sleep(20);
		fwrite(STDOUT, 'Hello '.$this->args['name'].'!');
		
		//$collection = Tapioca::collection($this->appid, $this->namespace);
		
		//\Cli::write(print_r($collection, true));
		//sleep(30);
	}
}
