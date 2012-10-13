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

		$validation->add('email', 'E-mail')
					->add_rule('required')
					->add_rule('valid_email');

		$validation->add('name', 'Name')
					->add_rule('required');

		$validation->add('password', 'Password')
					->add_rule('required')
					->add_rule('min_length', 3);

		$validation->add('appname', 'Application Name')
					->add_rule('required');

		// run validation on just post
		if ($validation->run())
		{
			try
			{
				$master = array(
					'email'    => Input::post('email', null),
					'password' => Input::post('password', null),
					'name'     => Input::post('name', 0)
				);

				$firstGroup = array(
					'name'     => Input::post('appname', null),
				);

				$slug = Input::post('appslug', false);

				if($slug)
				{
					$firstGroup['slug'] = $slug;
				}

				$ret = Install::start($master, $firstGroup);

				if($ret)
				{
					Response::redirect('install/end');
				}
			}
			catch(InstallException $e)
			{
				$view_data['form_error'] = $e->getMessage();
			}
		}
		else
		{
			$errorsKeys = array();
			$errors     = $validation->error();
			
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
			Install::start($master, $firstGroup);
		}
		catch(InstallException $e)
		{
			Debug::show($e->getMessage());
		}

		return $this->response;
	}

	public function after( $response )
	{
		return View::forge('templates/install', self::$data)->auto_filter(false);
	}

}