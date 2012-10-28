<?php

class Controller_Api_Log extends Controller_Rest
{
	protected static $data    = array('message' => 'Method Not Implemented');
	protected static $status  = 501;

	public function before()
	{
		parent::before();
		
		// set default format
		$this->format = 'json';
	}

	public function get_out()
	{
		// log the user out
		Tapioca::logout();
		
		static::$data   = array('message' => 'Bye');
		static::$status = 200;
	}

	public function post_index()
	{
		$validation = Validation::forge('login_validation');

		$validation->add('email', 'E-mail')
					->add_rule('required')
					->add_rule('valid_email');
					
		$validation->add('password', 'Password')
					->add_rule('required')
					->add_rule('min_length', 3);

		// run validation on just post
		if ($validation->run())
		{
			// try to log a user in
			try
			{
				$email    = Input::post('email', null);
				$password = Input::post('password', null);
				$remember = Input::post('remember', 0);

				// log the user in
				$valid_login = Tapioca::login($email, $password, $remember);

				if ($valid_login)
				{
					// get the current logged in user
					static::$data   = Tapioca::user()->get();
					static::$status = 200;
				}
				else
				{
					static::restricted();
				}
			}
			catch (AuthException $e)
			{
				// issue logging in via Tapioca - lets catch the sentry error thrown
				// store/set and display caught exceptions such as a suspended user with limit attempts feature.
				static::$data   = array('message' => $e->getMessage() );
				static::$status = 401;
			}
		}
		else
		{
			$errors = $validation->error();
			
			$msg = array();
			
			foreach($errors as $key => $error)
			{
				$msg[$key] = $error->get_message();
			}

			static::$data   = array('message' => 'Access not allowed', 'errors' => $msg);
			static::$status = 401;
		}
	}

	public function after( $response )
	{
		$this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
		$this->response(static::$data, static::$status);

		return $this->response;
	}
}