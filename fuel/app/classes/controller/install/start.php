<?php

class Controller_Install_Start extends Controller
{
	public static $data = array();

	public function before()
	{
		Tapioca::base();

		if(Tapioca::check_install())
		{
			Response::redirect('log');
		}
	}

	public function action_index()
	{
		$view_data = array(
			'email' => '',
			'name'  => '',
		);

		$validation = Validation::forge('admin_validation');

		$validation->add('email', 'Your e-mail')
					->add_rule('required')
					->add_rule('valid_email');

		$validation->add('name', 'Your name')
					->add_rule('required');

		$validation->add('password', 'Your password')
					->add_rule('required')
					->add_rule('min_length', 3);

		$validation->add('appname', 'Application name')
					->add_rule('required');

		// run validation on just post
		if ($validation->run())
		{
			try
			{
				$master = array(
					'email'    => Input::post('email', null),
					'password' => Input::post('password', null),
					'name'     => Input::post('password', 0)
				);

				$firstGroup = array(
					'name'     => Input::post('appname', null),
				);

				$slug = Input::post('appslug', false);

				if($slug)
				{
					$firstGroup['slug'] = $slug;
				}

				$ret = Tapioca\Install::start($master, $firstGroup);

				if($ret)
				{
					Response::redirect('install/end');
				}
			}
			catch(TapiocaInstallException $e)
			{
				$view_data['form_error'] = $e->getMessage();
			}
		}
		else
		{
			$errorsKey = array();
			$errors    = $validation->error();
			
			foreach($errors as $key => $error)
			{
				$errorsKeys[] = $key;
			}

			$view_data['displayError'] = (bool) count($errors);
			$view_data['errorsKeys']   = $errorsKeys;
		}

		// Repopulate form
		if($_POST)
		{
			$input     = $validation->input();
			$view_data = array_merge($view_data, $input);
		}


		$view_data['validation'] = $validation;

		$view = View::forge('install/admin', $view_data)->auto_filter(false);

		self::$data = array(
			'breadcrumb' => array('admin'),
			'view'       => $view
		);
	}

	public function action_config()
	{

	}

	public function action_admin()
	{

		// First, create master admin

		$master = array(
			'email'    => 'michael@tapiocapp.com',
			'password' => 'michael',
			'name'     => 'Michael'
		);

		$firstGroup = array(
			'name'  => 'Bouffes du Nord',
			'slug' => 'bdn'
		);

		try
		{
			Tapioca\Install::start($master, $firstGroup);
		}
		catch(TapiocaInstallException $e)
		{
			Debug::show($e->getMessage());
		}

		return $this->response;
	}

	public function after()
	{
		return View::forge('templates/install', self::$data)->auto_filter(false);
	}

}