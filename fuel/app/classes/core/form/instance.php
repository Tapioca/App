<?php

class Form_Instance extends Fuel\Core\Form_Instance
{
	/**
	 * Create a form open tag
	 *
	 * @param   string|array  id string or array with more tag attribute settings
	 * @return  string
	 */
	public function open($attributes = array(), array $hidden = array())
	{
		$attributes = ! is_array($attributes) ? array('id' => $attributes) : $attributes;

		if( ! array_key_exists('class', $attributes) or $attributes['class'] === null)
		{
			$attributes['class']  = 'form-horizontal';
		};

		// If there is still no action set, Form-post
		// if( ! array_key_exists('action', $attributes) or $attributes['action'] === null)
		// {
		// 	$attributes['action'] = \Uri::main();
		// }
		// // If not a full URL, create one
		// elseif ( ! strpos($attributes['action'], '://'))
		// {
		// 	$attributes['action'] = \Uri::create($attributes['action']);
		// }
		
		// for void url
		$attributes['action'] = \Uri::create('api/void');
		$attributes['target'] = 'postFrame';

		if (empty($attributes['accept-charset']))
		{
			$attributes['accept-charset'] = strtolower(\Fuel::$encoding);
		}

		// If method is empty, use POST
		! empty($attributes['method']) || $attributes['method'] = $this->get_config('form_method', 'post');

		$form = '<form';
		foreach ($attributes as $prop => $value)
		{
			$form .= ' '.$prop.'="'.$value.'"';
		}
		$form .= '>';

		// Add hidden fields when given
		foreach ($hidden as $field => $value)
		{
			$form .= PHP_EOL.$this->hidden($field, $value);
		}

		return $form;
	}

	/**
	 * Create a form close tag
	 *
	 * @return  string
	 */
	public function close()
	{
		return '</form><iframe name="postFrame" class="hide"></iframe>';
	}
}