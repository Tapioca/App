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

namespace Tapioca;

use FuelException;
use Config;
use Lang;
use Session;
use Cookie;

class TapiocaException extends FuelException {}
class AuthException extends FuelException {}

class Tapioca 
{
    /**
     * @var  Tapioca version
     */
    protected static $version = '0.8.0';

    /**
     * All the Mongo_Db instances
     *
     * @var  array
     */
    protected static $dbInstances = array();

    /**
     * @var  bool  Whether suspension feature should be used or not
     */
    protected static $suspend = null;

    /**
     * @var  Auth_Attempts  Holds the Auth_Attempts object
     */
    protected static $attempts = null;

    /**
     * @var  array  Caches all users accessed
     */
    protected static $user_cache = array();

    /**
     * @var  object  Caches the current logged in user object
     */
    protected static $current_user = null;

    /**
     * @var  array   List document validation error
     */
    protected static $rulesErrors = array();

    /**
     * @var  string   Background worker type
     */
    private static $worker = null;


    /**
     * Prevent instantiation
     */
    final private function __construct() {}

    /**
     * Run when class is loaded
     *
     * @return  void
     */
    public static function _init()
    {
        // load config
        Config::load('tapioca', true);
        Lang::load('tapioca', 'tapioca');

        // set static vars for later use
        static::$suspend = trim( Config::get('tapioca.limit.enabled') );
        static::$worker  = Config::get('tapioca.worker');
    }

    /**
     * Called to init Lang in UI
     *
     * @return  void
     */
    public static function base()
    {
    }

    /**
     * Return version id
     *
     * @return  string
     */
    public static function getVersion()
    {
        return static::$version;
    }

    /**
     * Return if we decide to skip update check
     *
     * @return  bool
     */
    public static function skipUpdateCheck()
    {
        return Config::get('tapioca.skip_update');
    }

    /**
     * Manage global MongoDb connection.
     * Acts as a Multiton.  Will return the requested instance, or will create
     * a new one if it does not exist.
     *
     * @param   string    $name  The instance name
     * @return  Mongo_Db
     */
    public static function db( $name = 'default' )
    {
        if (\array_key_exists($name, static::$dbInstances))
        {
            return static::$dbInstances[ $name ];
        }

        static::$dbInstances[ $name ] = \Mongo_Db::instance();

        return static::$dbInstances[ $name ];
    }


    /**
     * @param   string app slug
     * @param   string Collection namespace.
     * @throws  TapiocaException
     * @return  Collection object
     */
    public static function collection( $appslug, $namespace = null )
    {
        try
        {
            return new \Collection($appslug, $namespace);
        }
        catch ( CollectionException $e )
        {
            throw new \TapiocaException($e->getMessage());
        }
    }

    /**
     * @param   object App instance
     * @param   string Collection namespace.
     * @param   string Document reference.
     * @param   string Document locale.
     * @throws  TapiocaException
     * @return  Document object
     */
    public static function document( App $app, $namespace, $ref = null, $locale = null)
    {
        try
        {
            return new \Document($app, $namespace, $ref, $locale);
        }
        catch ( DocumentException $e )
        {
            throw new \TapiocaException( $e->getMessage() );
        }

        //\Debug::dump('Tapioca collection call');
        //
    }

    /**
     * @param   string app slug
     * @param   string Filename
     * @throws  TapiocaException
     * @return  File object
     */
    public static function library($appslug, $filename = null)
    {
        try
        {
            return new \Library($appslug, $filename);
        }
        catch ( LibraryException $e )
        {
            throw new \TapiocaException( $e->getMessage() );
        }
    }

    // public static function set_status($status = array())
    // {
    //  $defaults = Config::get('tapioca.status');

    //  if(count($status) > 1)
    //  {
    //      return array_merge($defaults, $status);
    //  }

    //  return $defaults;
    // }

    /**
     * @return  Bool
     */
    public static function check_install()
    {
        return static::user()->admin_set();
    }

    /**
     * Get's either the currently logged in user's app object or the
     * specified app by slug.
     *
     * @param   string  App name url friendly
     * @throws  AuthException
     * @return  App object
     */
    public static function app( $id = null )
    {
        try
        {
            if ($id)
            {
                return new App( $id );
            }

            return new App();
        }
        catch (AppNotFoundException $e)
        {
            throw new \AuthException( $e->getMessage() );
        }
    }


    /**
     * Get's either the currently logged in user or the specified user by id or Login
     * Column value.
     *
     * @param   int|string  User id or Login Column value to find.
     * @throws  UserNotFoundException
     * @return  User
     */
    public static function user($id = null, $recache = false)
    {
        if ($id === null and $recache === false and static::$current_user !== null)
        {
            return static::$current_user;
        }
        elseif ($id !== null and $recache === false and isset(static::$user_cache[$id]))
        {
            return static::$user_cache[$id];
        }

        try
        {
            if ($id)
            {
                static::$user_cache[$id] = new User($id);
                return static::$user_cache[$id];
            }
            // if session exists - default to user session
            else if(static::check())
            {
                $user_id = Session::get(Config::get('tapioca.session.user'));
                static::$current_user = new User($user_id);
                return static::$current_user;
            }
        }
        catch (UserNotFoundException $e)
        {
            throw new \AuthException($e->getMessage());
        }

        // else return empty user
        return new User();
    }


    /**
     * Gets the Attempts object
     *
     * @return  Attempts
     */
     public static function attempts($login_id = null, $ip_address = null)
     {
        return new Attempts($login_id, $ip_address);
     }

    /**
     * Attempt to log a user in.
     *
     * @param   string  Login column value
     * @param   string  Password entered
     * @param   bool    Whether to remember the user or not
     * @return  bool
     * @throws  AuthException
     */
    public static function login($login_column_value, $password, $remember = false)
    {
        // log the user out if they hit the login page
        static::logout();

        // get login attempts
        if (static::$suspend)
        {
            $attempts = static::attempts($login_column_value, \Input::real_ip());

            // if attempts > limit - suspend the login/ip combo
            if ($attempts->get() >= $attempts->get_limit())
            {
                try
                {
                    $attempts->suspend();
                }
                catch(UserSuspendedException $e)
                {
                    throw new \AuthException($e->getMessage());
                }
            }
        }

        // make sure vars have values
        if (empty($login_column_value) or empty($password))
        {
            return false;
        }

        // if user is validated
        if ($user = static::validate_user($login_column_value, $password, 'password'))
        {
            if (static::$suspend)
            {
                // clear attempts for login since they got in
                $attempts->clear();
            }

            // set update array
            $update = array();

            // if they wish to be remembers, set the cookie and get the hash
            if ($remember)
            {
                $update['remember_me'] = static::remember($login_column_value);
            }

            // if there is a password reset hash and user logs in - remove the password reset
            if ($user->get('password_reset_hash'))
            {
                $update['password_reset_hash'] = '';
                $update['temp_password'] = '';
            }

            $update['last_login'] = new \MongoDate();
            $update['ip_address'] = \Input::real_ip();

            // update user
            if (count($update))
            {
                $user->update($update, false);
            }

            // set session vars
            Session::set(Config::get('tapioca.session.user'), $user->get('id'));
            Session::set(Config::get('tapioca.session.provider'), 'Tapioca');

            return true;
        }

        return false;
    }

    /**
     * Checks if the current user is logged in.
     *
     * @return  bool
     */
    public static function check()
    {
        // get session
        $user_id = Session::get(Config::get('tapioca.session.user'));
        
        // invalid session values - kill the user session
        if ($user_id === null)
        {
            // if they are not logged in - check for cookie and log them in
            if (static::is_remembered())
            {
                return true;
            }
            //else log out
            static::logout();

            return false;
        }

        return true;
    }

    /**
     * Logs the current user out.  Also invalidates the Remember Me setting.
     *
     * @return  void
     */
    public static function logout()
    {
        Cookie::delete( Config::get('tapioca.remember_me.cookie_name') );

        Session::delete( Config::get('tapioca.session.user') );
        Session::delete( Config::get('tapioca.session.provider') );
    }

    /**
     * Remember User Login
     *
     * @param int
     */
    protected static function remember($login_column)
    {
        // generate random string for cookie password
        $cookie_pass = \Str::random('alnum', 24);

        // create and encode string
        $cookie_string = base64_encode($login_column.':'.$cookie_pass);

        // set cookie
        Cookie::set(
            Config::get('tapioca.remember_me.cookie_name'),
            $cookie_string,
            Config::get('tapioca.remember_me.expire')
        );

        return $cookie_pass;
    }

    /**
     * Check if remember me is set and valid
     */
    protected static function is_remembered()
    {
        $encoded_val = Cookie::get(Config::get('tapioca.remember_me.cookie_name'));

        if ($encoded_val)
        {
            $val = base64_decode($encoded_val);

            list($login_column, $hash) = explode(':', $val);

            // if user is validated
            if ($user = static::validate_user($login_column, $hash, 'remember_me'))
            {
                // update last login
                $user->update(array(
                    'last_login' => new \MongoDate()
                ));

                // set session vars
                Session::set(Config::get('tapioca.session.user'), $user->get('id'));
                Session::set(Config::get('tapioca.session.provider'), 'Tapioca');

                return true;
            }
            else
            {
                static::logout();

                return false;
            }
        }

        return false;
    }


    /**
     * Validates a Login and Password.  This takes a password type so it can be
     * used to validate password reset hashes as well.
     *
     * @param   string  Login column value
     * @param   string  Password to validate with
     * @param   string  Field name (password type)
     * @return  bool|User
     */
    protected static function validate_user($login_column_value, $password, $field)
    {
        // get user
        $user = static::user($login_column_value);

        // check activation status
        if ($user->activated != 1 and $field != 'activation_hash')
        {
            throw new \AuthException('account_not_activated');
        }

        // check user status
        if ($user->status != 1)
        {
            throw new \AuthException('account_is_disabled');
        }

        // check password
        if ( ! $user->check_password($password, $field))
        {
            if (static::$suspend and ($field == 'password' or $field == 'password_reset_hash'))
            {
                static::attempts($login_column_value, \Input::real_ip())->add();
            }
            return false;
        }

        return $user;
    }


    /**
     * Provide a token, required for delete action
     *
     * @param   string  object type to delete
     * @param   string  object ID
     * @throws  TapiocaException
     * @return  object
     */
    public static function getDeleteToken( $object, $id )
    {
        // if( is_null( static::$db ) )
        // {
        //     static::$db = \Mongo_Db::instance();
        // }

        $collection = Config::get('tapioca.collections.deletes');

        $token = \Str::random('alnum', 16);

        $array = array(
                        'token'  => $token,
                        'object' => $object,
                        'id'     => $id,
                        'date'   => new \MongoDate()
                    );

        $action = static::db()->insert( $collection, $array);

        if( !$action )
        {
            throw new \TapiocaException( __('tapioca.internal_server_error') );
        }

        unset( $array['date'] );
        unset( $array['_id'] );

        return $array;
    }

    /**
     * Check if given token is valid for delete action
     *
     * @param   string  token
     * @throws  TapiocaException
     * @return  object
     */
    public static function checkDeleteToken( $token, $object, $id )
    {
        // if( is_null( static::$db ) )
        // {
        //     static::$db = \Mongo_Db::instance();            
        // }

        $collection = Config::get('tapioca.collections.deletes');
        $limitDate  = ( time() - Config::get('tapioca.deleteToken') );

        $object = static::db()->get_where( $collection, array(
                'token'  => $token,
                'object' => $object,
                'id'     => $id,
            ));

        if( count( $object ) != 1 )
        {
            throw new \TapiocaException( __('tapioca.no_valid_token') );
        }

        if( $object[0]['date']->sec <= $limitDate )
        {
            throw new \TapiocaException( __('tapioca.token_expire') );
        }


        static::db()->where( array('token' => $token) )->delete( $collection );

        return true;
    }

    /**
     * Test each rules in the current document 
     *
     * @param   array collection rules definition
     * @param   array document data
     * @return  bool
     */
    public static function checkRules($rules_list, $document)
    {
        // reset errors list before
        static::$rulesErrors = array();

        foreach($rules_list as $field)
        {
            $args = \Set::extract($field['path'], $document);

            foreach($field['rules'] as $rule)
            {
                // prevent to run rule on un-required field
                if( empty( $args ) && $rule !== 'required' )
                    continue;

                // Strip the parameter (if exists) from the rule
                // Rules can contain a parameter: max_length[5]
                $param = false;

                if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
                {
                    $rule  = $match[1];
                    $param = explode('|', $match[2]);                   
                    $args  = array_merge($args, $param);
                }

                $valid = call_user_func_array(array(__NAMESPACE__ .'\Rules', $rule), $args);

                if(!$valid)
                {
                    $obj = new \stdClass;
                    $obj->rule = $rule;
                    $obj->path = $field['path'];
                    // $obj->args = array_merge(array($item['id']), (array) $param);
                    
                    static::$rulesErrors[] = $obj;
                    
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Return failed rules array
     *
     * @return  array
     */
    public static function getFailedRules()
    {
        return static::$rulesErrors;
    }

    /**
     * Add a new background job to the queue
     *
     *
     * @param   string       App Slug
     * @param   string       Class to perform
     * @param   array        Method Arguments 
     * @param   int          Job priority (Mongo Only)
     * @return  string       job token
     */
    public static function enqueueJob( $slug, $class, $args, $priority)
    {
        if( static::$worker == 'Mongo' )
        {
            $token = Jobs::push( $slug, $class, $args, $priority);
        }
        else
        {
            $token = \Resque::enqueue( Config::get('resque.queue'), $class, $args, true);
        }

        return $token;
    }

    /**
     * Send invitation mail
     *
     * @param   string       Guest email
     * @param   object       Sender User instance
     * @param   object       App instance
     * @param   bool/string  activation hash if needed
     * @return  bool
     * @throws  TapiocaException
     */
    public static function sendInvite( $guestEmail, User $user, App $app, $hash )
    {
        $emailData = array(
                'hostName' => $user->get('name'),
                'appName'  => $app->get('name'),
                'linkUrl'  => \Uri::base()
            );

        if( $hash )
        {
            $emailData['linkUrl'] = \Uri::create('invite', array(), array( 'hash' => $hash ) );
        }

        $emailBody    = \View::forge( 'emails/invite', $emailData )->auto_filter(false);
        $emailSubject = __('tapioca.invite_new_user', array( 'app' => $app->get('name') ));
        
        \Package::load('email');

        $config = \Config::get('tapioca.mailer');

        $mailer = \Email::forge();

        $mailer->from( $user->get('email'), $user->get('name') );
        $mailer->to( $guestEmail );
        $mailer->subject( $emailSubject );
        $mailer->body( $emailBody );

        try
        {
            return $mailer->send();
        }
        catch(\EmailValidationFailedException $e)
        {
            throw new \TapiocaException( 'email validation failed' );
        }
        catch(\EmailSendingFailedException $e)
        {
            throw new \TapiocaException( 'The driver could not send the email' );
        }
    }


    public static function genApiKey($app_id, $slug)
    {
        $api_conf   = Config::get('tapioca.api');
        $db_prefix  = $api_conf['db_prefix'];
        $salts      = $api_conf['salts'];
        
        $salt       = $salts[ array_rand( $salts ) ];
        $str        = $slug.':'.$salt.':'.$app_id;
        $secret     = hash('sha256', $str); //substr( , 16, 16 );

        $db         = new \stdClass;
        $db->name   = $db_prefix.$slug;
        $db->user   = $slug;
        $db->pass   = \Str::random('alnum', 16);
        $db->host   = 'localhost';
        $db->key    = \Str::random('alnum', 16);
        $db->secret = $secret;

        return $db;
    }


    // TO CLEAN
    // protected static function createDbUser($name, $user, $pass, $readOnly = false)
    // {
    //  $db_collection = Config::get('db.mongo.default');

    //  // TODO: trouver une façon plus élégante de faire ça.
    //  $mongo  = new Mongo('mongodb://'.$db_collection['username'].':'.$db_collection['password'].'@localhost');
    //  $db     = $mongo->selectDB($name);
        
    //  // insert the user - note that the password gets hashed as 'username:mongo:password'
    //  // set readOnly to true if user should not have insert/delete privs
    //  $collection = $db->selectCollection("system.users");

    //  return $collection->insert(array('user' => $user, 'pwd' => self::get_user_pass($user, $pass), 'readOnly' => $readOnly));    
    // }
    
    // protected static function getMongoUserPass($user, $pass)
    // {
    //  $salted = "${user}:mongo:${pass}";

    //  return md5( $salted );
    // }
}