<?php

class Controller_Api_App_User extends Controller_Api
{
    protected static $appslug;
    protected static $userId;
    protected static $member;  // working user

    public function before()
    {
        parent::before();

        static::$appslug = $this->param('appslug', false);
        static::$userId  = $this->param('userid', false);

        if( !static::$appslug || !static::$userId )
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
            static::$member = Tapioca::user( static::$userId );
        }
        catch (AuthException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        try
        {
            Permissions::set( static::$user, static::$app );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
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
            static::error( $e->getMessage() , 500 );
            return;
        }

        $role  = Input::json('role', null);

        try
        {
            static::$app->add_to_app( static::$userId, $role );
        }
        catch (AppException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        try
        {
            static::$member->add_to_app( static::$app->get('id') );
        }
        catch (UserException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        static::$data   = static::$app->get();
        static::$status = 200;
    }

    public function put_index()
    {
        try
        {
            Permissions::isGranted( 'app_promote_users' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        $role  = Input::json('role', null);

        try
        {
            // user Id provided by JSON
            static::$app->user_role( static::$userId, $role );

            static::$data   = static::$app->get();
            static::$status = 200;
        }
        catch (AppException $e)
        {
            static::error( $e->getMessage() );
        }
    }

    public function delete_index()
    {
        try
        {
            Permissions::isGranted( 'app_remove_users' );
        }
        catch( PermissionsException $e)
        {
            static::error( $e->getMessage() , 500 );
            return;
        }

        if( ! static::deleteToken( 'app_user', static::$userId ))
        {
            return;
        }

        try
        {
            static::$app->remove_from_app( static::$userId );
        }
        catch (AppException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        try
        {
            static::$member->remove_from_app( static::$app->get('slug'), static::$app->get('name') );
        }
        catch (UserException $e)
        {
            static::error( $e->getMessage() );
            return;
        }

        static::$data   = static::$app->get();
        static::$status = 200;
    }
}