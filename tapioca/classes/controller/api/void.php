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