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
