<?php
/**
 * Tapioca: Schema Driven Data Engine 
 * Flexible CMS build on top of MongoDB, FuelPHP and Backbone.js
 *
 * @package   Tapioca
 * @version   v0.8
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/App
 */

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