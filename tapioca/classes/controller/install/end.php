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