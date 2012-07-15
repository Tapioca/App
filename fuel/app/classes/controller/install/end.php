<?php

class Controller_Install_End extends Controller
{
	public function action_index()
	{
		$view = View::forge('install/resume')->auto_filter(false);

		$data_view = array(
			'breadcrumb' => array('admin', 'resume'),
			'view'       => $view
		);

		return View::forge('templates/install', $data_view)->auto_filter(false);
	}
}