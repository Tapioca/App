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

        // status translation, to move somewhere else
        $statusArray  = Config::get('tapioca.status');
        $statusTech   = array();
        $statusPublic = array();
        
        foreach ($statusArray as $row)
        {
            $statusTech[$row[0]] = array(
                'label' => __('tapioca.doc_status.'.$row[1]),
                'class' => $row[2]
            );

            if($row[0] >= 0)
            {
                $statusPublic[] = array(
                    'value' => $row[0],
                    'label' => $row[1],
                    'class' => $row[2]
                );
            }
        }

        $settings = array(
            'host'      => $domain,
            'rootUrl'   => Uri::base(),
            'bbRootUrl' => str_replace($domain, '', Uri::base()),
            'apiUrl'    => Uri::create('api/'),
            'appUrl'    => Uri::create('app'),
            'roles'     => Config::get('tapioca.roles'),
            'status'    => array(
                'public' => $statusPublic,
                'tech'   => $statusTech
            )
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
