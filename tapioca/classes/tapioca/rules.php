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

class Rules
{
    // --------------------------------------------------------------------

    /**
     * Required
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function required($str)
    {
        if ( ! is_array($str))
        {
            return (trim($str) == '') ? FALSE : TRUE;
        }
        else
        {
            return ( ! empty($str));
        }
    }

    // --------------------------------------------------------------------

    /**
     * Performs a Regular Expression match test.
     *
     * @access  public
     * @param   string
     * @param   regex
     * @return  bool
     */
    static public function regex_match($str, $regex)
    {
        if ( ! preg_match($regex, $str))
        {
            return FALSE;
        }

        return  TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @access  public
     * @param   string
     * @param   field
     * @return  bool
     */
    static public function matches($str, $field)
    {
        if ( ! isset($_POST[$field]))
        {
            return FALSE;
        }

        $field = $_POST[$field];

        return ($str !== $field) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Minimum Length
     *
     * @access  public
     * @param   string
     * @param   value
     * @return  bool
     */
    static public function min_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) < $val) ? FALSE : TRUE;
        }

        return (strlen($str) < $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Max Length
     *
     * @access  public
     * @param   string
     * @param   value
     * @return  bool
     */
    static public function max_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) > $val) ? FALSE : TRUE;
        }

        return (strlen($str) > $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Exact Length
     *
     * @access  public
     * @param   string
     * @param   value
     * @return  bool
     */
    static public function exact_length($str, $val)
    {
        if (preg_match("/[^0-9]/", $val))
        {
            return FALSE;
        }

        if (function_exists('mb_strlen'))
        {
            return (mb_strlen($str) != $val) ? FALSE : TRUE;
        }

        return (strlen($str) != $val) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Valid Email
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function valid_email($str)
    {
        return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------


    /**
     * Alpha
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function alpha($str)
    {
        return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function alpha_numeric($str)
    {
        return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function alpha_dash($str)
    {
        return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Numeric
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function numeric($str)
    {
        return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

    }

    // --------------------------------------------------------------------

    /**
     * Is Numeric
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function is_numeric($str)
    {
        return ( ! is_numeric($str)) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Integer
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function integer($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Decimal number
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function decimal($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Greather than
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function greater_than($str, $min)
    {
        if ( ! is_numeric($str))
        {
            return FALSE;
        }
        return $str > $min;
    }

    // --------------------------------------------------------------------

    /**
     * Less than
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function less_than($str, $max)
    {
        if ( ! is_numeric($str))
        {
            return FALSE;
        }
        return $str < $max;
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function is_natural($str)
    {
        return (bool) preg_match( '/^[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @access  public
     * @param   string
     * @return  bool
     */
    static public function is_natural_no_zero($str)
    {
        if ( ! preg_match( '/^[0-9]+$/', $str))
        {
            return FALSE;
        }

        if ($str == 0)
        {
            return FALSE;
        }

        return TRUE;
    }
}