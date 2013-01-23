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

namespace Tapioca\Jobs;

// use Tapioca;

class Test
{
    public function setUp()
    {
        \Cli::write('****************'."\n", 'green');
        \Cli::write('call setUp'."\n", 'green');
    }

    public function perform()
    {

        \Cli::write('call perform'."\n", 'green');
        \Cli::write(print_r($this->args, true));

        if( isset( $this->args['failed'] ) )
            throw new \JobsException("Error Processing Request");
        
        \Cli::write('****************'."\n", 'green');
        return true;
    }

    public function tearDown()
    {
        \Cli::write('call tearDown'."\n", 'green');
        \Cli::write('****************'."\n", 'green');
    }
}