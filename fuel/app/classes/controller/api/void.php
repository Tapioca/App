<?php

// http://pullrequest.org/2011/10/19/subtilites-du-login.html

class Controller_Api_Void extends Controller_Rest
{
	public function before()
	{
		parent::before();
	}

	public function get_index()
	{
		return;
	}

	public function post_index()
	{
		return;
	}

	public function after($response)
	{
		$this->format = 'json';
		$this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
		$this->response(array(''), 204);

		return $this->response;
	}
}