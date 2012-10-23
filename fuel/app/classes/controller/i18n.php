<?php

/**
 * Collect locale string into a big hash.
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_I18n extends Controller
{
	public function action_index()
	{
		// init Tapioca config
		Tapioca::base();

		$arr = Lang::get('tapioca.ui');

		$headers = array ('Content-Type' => 'text/javascript');

		$body    = '$.Tapioca.I18n.Str = '.Format::forge( $arr )->to_json();

		return new Response( $body, 200, $headers );
	}

}
