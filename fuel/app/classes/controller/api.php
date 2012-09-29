<?php

class Controller_Api extends Controller_Rest
{
	protected $rest_format    = 'json'; // default format

	protected static $granted = true;
	protected static $data    = array('message' => 'Method Not Implemented');
	protected static $status  = 501;
	protected static $user    = null;
	protected static $group   = null;
	protected static $debug   = null;
	protected static $apiKey  = false;

	public function before()
	{
		parent::before();

		static::$apiKey = Input::get('apikey', false);

		if (!Auth::check() && !static::$apiKey)
		{
			self::restricted();
		}
		else
		{
			static::$debug = Input::get('debug', false);
			$valid = true;
			
			try
			{
				self::$user = Auth::user();
			}
			catch (UserException $e)
			{
				$valid = false;
				static::error($e->getMessage());
			}

			try
			{
				$app_slug = $this->param('app_slug', false);
				self::$group = Auth::group(array('slug' => $app_slug));
			}
			catch (AuthException $e)
			{
				$valid = false;
				static::error($e->getMessage());
			}

			if($valid && !static::$apiKey)
			{
				// Check if user is a member of the group
				$user_email = self::$user->get('email');
				$in_group   = self::$group->in_group($user_email);

				if(!$in_group)
				{
					self::restricted();
				}
			}

		}// if Auth

		// if no url define format
		// set default format
		if(is_null($this->format))
		{
			$this->format = $this->rest_format;
		}

	}

	protected static function restricted()
	{
		self::$granted = false;
		self::$status  = 401;
		self::$data    = array(
			'message' => 'Access not allowed'
		);
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
		$this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
		$this->response(self::$data, self::$status);

		return $this->response;
	}
}