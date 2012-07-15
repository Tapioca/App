<?php

class Controller_Log extends Controller
{
	public function before()
	{
		Tapioca::base();

		if(!Tapioca::check_install())
		{
			Response::redirect('install/start');
		}
	}

	public function action_index()
	{
		$view_data = array(
				'email'      => '',
				'remember'   => 0,
				'auth_error' => null
			);

		$validation = Validation::forge('login_validation');

		$validation->add('email', 'Your e-mail')->add_rule('required')
		           ->add_rule('valid_email');
		$validation->add('password', 'Your password')->add_rule('required')
		           ->add_rule('min_length', 3);

		// run validation on just post
		if ($validation->run())
		{
			try
			{
				$email    = Input::post('email', null);
				$password = Input::post('password', null);
				$remember = Input::post('remember', 0);

				// log the user in
				$valid_login = Auth::login($email, $password, $remember);
		
				if ($valid_login)
				{
					Response::redirect('app');
				}
			}
			catch (AuthException $e)
			{
				$view_data['auth_error'] = $e->getMessage();
			}
		}

		// Repopulate form
		if($_POST)
		{
			$input     = $validation->input();
			$view_data = array_merge($view_data, $input);
		}

		$view_data['validation'] = $validation;

		return View::forge('welcome/login', $view_data)->auto_filter(false);
	}

	public function action_out()
	{
		// log the user out
		Auth::logout();	
		Response::redirect('log');
	}
}