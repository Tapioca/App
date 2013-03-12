<?php
/**
 * Inspired by FuelPHP LessCSS package implementation.
 *
 * @author     Michael Lefebvre
 * @version    1.0
 * @package    Fuel
 * @subpackage Casset
 */

namespace Casset;

class Casset_Addons_Lessphp
{
	/**
	 * Init the class
	 */
	public static function _init()
	{
		require_once PKGPATH.'casset'.DS.'vendor'.DS.'lessphp'.DS.'lessc.inc.php';
	}

	/**
	 * Compile the Less file in $origin to the CSS $destination file
	 *
	 * @param array $origin Less files
	 */
	public static function compile( $origin )
	{
		$less = new \lessc;
		$less->indentChar = \Config::get('asset.indent_with');

		$raw_css = '';

		if( is_array( $origin ) )
		{
			foreach ($origin as $file)
			{
				$raw_css .= $less->compileFile( DOCROOT.$file['file'] );
			}
		}
		else
		{
			$raw_css = $less->compile( $origin );
		}

		return $raw_css;
	}
}
