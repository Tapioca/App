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

namespace Tapioca\Jobs\Library;

use Tapioca;
use File;

class Preset
{
    /**
     * apply preset to a file
     *
     * @param   string preset name
     * @return  bool
     */
    public function perform()
    {
        \Cli::write('call perform'."\n", 'green');
        \Cli::write(print_r($this->args, true));

        Tapioca::base();

        if( is_null( $this->filename ) )
        {
            throw new \JobsException(__('tapioca.no_file_selected'));
        }

        if( in_array( $preset_name, $this->file['presets'] ) )
        {
            return true;
        }

        $presets = static::$app->get('library.presets');

        if( !isset( $presets[$preset_name] ) )
        {
            throw new \JobsException(__('tapioca.preset_not_define'));
        }

        $original_file = $this->get_path();
        $path          = $this->get_path(false);
        $new_file_path = $path.$preset_name.'-'.$this->filename;
        $resource      = \Image::load($original_file);
        
        $resource->config('presets', $presets);
        $resource->preset($preset_name)->save($new_file_path);

        if( file_exists( $new_file_path ) )
        {
            $ret = static::$db
                    ->where(array(
                        'filename' => $this->filename
                    ))
                    ->update(static::$dbCollectionName, array(
                        '$addToSet' => array(
                            'presets' => $preset_name
                        )
                    ), array(), true);

            return $ret;
        }
    }
}