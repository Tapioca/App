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

class Controller_Api_App_Invite extends Controller_Api
{
    protected static $appslug;

    public function before()
    {
        parent::before();

        static::$appslug = $this->param('appslug', false);

        if( !static::$appslug )
        {
            static::error( __('tapioca.missing_required_params') );
            return;
        }

        // app instance
        if( !static::assignApp() )
        {
            return;
        }

        try
        {
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 403 );
            return;
        }
    }

    public function post_index()
    {
        try
        {
            Permissions::isGranted( 'app_invite_users' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 403 );
            return;
        }

        $email    = Input::json('email', false);
        $role     = Input::json('role', false);
        $hash     = null;

        if( !$email || !$role )
        {
            static::error( __('tapioca.missing_required_params') , 400 );
            return;
        }

        if( !filter_var($email, FILTER_VALIDATE_EMAIL) )
        {
            static::error( __('tapioca.not_valid_email') , 400 );
            return;
        }

        try
        {
            $guest  = Tapioca::user( $email );
        }
        catch( AuthException $e)
        {
            $account = Tapioca::user()->create( array(
                    'email'    => $email,
                    'name'     => $email,
                    'password' => Config::get('tapioca.default_password')
                ), true);
            
            $hash  = $account['hash'];
            $guest = Tapioca::user( $email );
        }

        $guestId = $guest->get('id');

        if( static::$app->in_app( $guestId ) )
        {
            static::error( __( 'tapioca.user_already_in_app', array( 'app' => static::$app->get('name') ) ) );
            return;
        }

        try
        {
            static::$app->add_to_app( $guestId, $role );
        }
        catch (AppException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        try
        {
            $guest->add_to_app( static::$app->get('id') );
        }
        catch (UserException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        try
        {
            $invite = Tapioca::sendInvite( $email, static::$user, static::$app, $hash );
        }
        catch ( TapiocaException $e )
        {
            static::error( $e->getMessage() );
            return;
        }

        static::$data   = static::$app->get('team');
        static::$status = 200;
    }
}