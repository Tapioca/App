<?php

class Controller_Api_User extends Controller_Api
{
    protected static $userId;
    protected static $isAllowed;

    public function before()
    {
        parent::before();

        static::$userId = $this->param('userid', false);
    }

    public function get_index()
    {
        if( static::$granted )
        {
            if( static::$userId )
            {
                try
                {
                    static::$data   = Tapioca::user( static::$userId )->get();
                    static::$status = 200;
                }
                catch ( AuthException $e )
                {
                    // catch errors such as user doesn't exists
                    static::error( $e->getMessage() );
                }

            }
            else
            {
                if( static::isAdmin() )
                {
                    static::$data   = User::getAll();
                    static::$status = 200;
                }
                else
                {
                    static::restricted();
                }
            }
        }
    }

    public function post_index()
    {
        if( static::$granted && !static::$userId && static::isAdmin() )
        {
            $fields = Input::json(null, false);
            
            try
            {
                $user = Tapioca::user()->create( $fields );

                if( $user )
                {
                    $user = Tapioca::user( $fields['email'] );

                    static::$data   = $user->get();
                    static::$status = 200;
                }
                else
                {
                    static::error( __('tapioca.internal_server_error'), 500 );
                }

            }
            catch ( UserException $e )
            {
                // catch errors such as user exists or bad fields
                static::error( $e->getMessage() );
            }
        }
    }

    public function put_index()
    {
        if( static::$granted && static::isAdmin() && static::$userId )
        {
            try
            {
                $fields = Input::json(null, false);

                $user   = Tapioca::user( static::$userId );
                $action = $user->update( $fields );

                if( $action )
                {
                    static::$data   = $user->get();
                    static::$status = 200;                  
                }
                else
                {
                    static::error( __('tapioca.internal_server_error'), 500 );
                }
            }
            catch (UserException $e)
            {
                // catch errors such as user doesn't exists
                static::error( $e->getMessage() );
            }
        }
    }

    public function delete_index()
    {
        if( static::$granted && static::isAdmin() && static::$userId )
        {
            if( ! static::deleteToken( 'user', static::$userId ))
            {
                return;
            }

            try
            {
                $action = Tapioca::user( static::$userId )->delete();

                if( $action )
                {
                    static::$data   = array('message' => __('tapioca.user_deleted') );
                    static::$status = 200;
                }
                else
                {
                    static::error( __('tapioca.internal_server_error'), 500 );
                }

            }
            catch (UserException $e)
            {
                // catch errors such as user doesn't exists
                static::error( $e->getMessage() );
            }
        }
    }
}