<?php

/**
 * 
 * @package  app
 * @extends  Controller
 */
class Controller_Welcome extends Controller
{

    /**
     * App index
     * 
     * @access  public
     * @return  Response
     */
    public function action_index()
    {
        // load Tapioca config
        Tapioca::base();

        $timezone = Config::get('tapioca.date.timezone');

        date_default_timezone_set( $timezone );
        // OR
        // Date::display_timezone( $timezone );

        $host   = parse_url( Uri::base() );
        $domain = $host['scheme'].'://'.$host['host'];

        if(!empty($host['port']))
        {
            $domain .= ':'.$host['port'];
        }
        
        $domain .= '/';

        $settings = array(
            'host'      => $domain,
            'rootUrl'   => Uri::base(),
            'bbRootUrl' => str_replace($domain, '', Uri::base()),
            'apiUrl'    => Uri::create('api/'),
            'appUrl'    => Uri::create('app'),
            'roles'     => Config::get('tapioca.roles')
        );


        return View::forge('templates/front', array('settings' => $settings ) );
    }
    
    /**
     * The 404 action for the application.
     * 
     * @access  public
     * @return  Response
     */
    public function action_404()
    {
        return Response::forge(ViewModel::forge('templates/404'), 404);
    }
}
