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

class Controller_Api_Version extends Controller_Rest
{
    public function before()
    {
        parent::before();
        
        // set default format
        $this->format = 'json';
    }

    public function get_index()
    {
        Tapioca::base();
        
        // check for updates
        if( !Tapioca::skipUpdateCheck() )
        {
            $currentVersion = Tapioca::getVersion();

            try 
            {
                $latest = @file_get_contents( Config::get('tapioca.version_url') );

                if( $latest && version_compare($latest, $currentVersion, '>') ) 
                {
                    $alerts[] = array(
                        'level' => 'warning',
                        'msg' =>  __('tapioca.new_version_available', array('installed' => $currentVersion, 'lastest' => $lastest))
                    );
                }
            }
            catch (Exception $e)
            {
                // do nothing
            }
        }

        $this->response->set_header('Content-Type', 'application/json; charset=UTF-8');
        $this->response($alerts, 200);

        return $this->response;
    }
}