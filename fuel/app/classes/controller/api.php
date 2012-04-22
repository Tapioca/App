<?php

class Controller_Api extends Controller_Rest
{
	protected static $granted = true;
	protected static $data    = array('message' => 'Method Not Implemented');
	protected static $status  = 501;
	protected static $user    = null;
	protected static $group   = null;
	protected static $debug   = null;

	public function before()
	{
		parent::before();

		if (!Auth::check())
		{
			self::$granted = false;
			self::$status  = 401;
			self::$data    = array(
				'message' => 'Access not allowed'
			);
		}
		else
		{
			static::$debug = Input::get('debug', false);
			
			try
			{
				self::$user = Auth::user();
			}
			catch (UserException $e)
			{
				$errors = $e->getMessage();
				Debug::dump($errors);
			}

			try
			{
				$app_slug = $this->param('app_slug', false);
				self::$group = Auth::group(array('slug' => $app_slug));
			}
			catch (UserException $e)
			{
				$errors = $e->getMessage();
				Debug::dump($errors);
			}

		}// if Auth
	}

	protected static function error($message, $debug = null)
	{
		self::$data = array('error' => $message);

		if(!is_null($debug))
		{
			self::$data['debug'] = $debug;
		}
		self::$status = 501;
	}

	public function after($response)
	{
		$this->response(self::$data, self::$status);
	}
}