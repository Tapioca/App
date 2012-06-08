<?php

class Controller_App_File extends Controller_App
{
	protected static $group;
	protected static $file;
	protected static $filename;

	public function before()
	{
		parent::before();

		$app_slug         = $this->param('app_slug', false);
		static::$filename = $this->param('ref', false);
		static::$group    = Auth::group(array('slug' => $app_slug));
		static::$file     = Tapioca::file(static::$group, static::$filename);
	}

	public function action_index()
	{
		$bytes = static::$file->getBytes(); 

		if(count($bytes) == 1)
		{
			$this->gateway($bytes[0]);
		}

		return $this->response;
	}

	public function action_preview()
	{
		$bytes = static::$file->getBytes(true); 

		if(count($bytes) == 1)
		{
			$this->gateway($bytes[0]);
		}
	}

	public function action_download()
	{
		$bytes = static::$file->getBytes(); 

		if(count($bytes) == 1)
		{
			$this->gateway($bytes[0], true);
		}
	}

 	/* ------------------------------------------------
	 *	GATEWAY
	 * --------------------------------------------- */
	 
	private function gateway($bytes, $force_download = false)
	{
		$file = static::$file->file;

		header('Content-type: '.$file['mimetype']);
		header("Content-Length:".$file['length']); // Provide stream size
		header("Accept-Ranges: bytes"); // Ok, seems to work on films.
		header("Content-Transfer-Encoding: binary"); // all files are binay in any case

		$ttl = (3600*24*7);
		header("Expires: " . date('D, d M Y H:i:s',time() + $ttl)); // expires in a week ..
		header('Cache-Control: max-age='.$ttl); // cache for a week ..
		//header('Content-Disposition: inline; filename="' . basename($url) . '"');
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
		header('Server: ');
		header('X-Powered-By: ');

		header("Pragma: public"); // required

		if($force_download)
		{
			header('Content-Disposition: attachment; filename="'.$bytes->getFilename().'"');
		}

		echo $bytes->getBytes();			
	}
}