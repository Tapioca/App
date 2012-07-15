<?php


/**
 * Casset: Convenient asset library for FuelPHP.
 *
 * @package    Casset
 * @version    v1.11
 * @author     Antony Male
 * @license    MIT License
 * @copyright  2011 Antony Male
 * @link       http://github.com/canton7/fuelphp-casset
 */

return array(
	'groups' => array(
		'css' => array(
			'app' => array(
				'files' => array(
					'class.css',
					'layout.css',
					'font-awesome.css',
					'bootstrap.css',
					'bootstrap-overload.css',
					'colorpicker.css',
					'datepicker.css',
					'bootstrap-wysihtml5.css',

					'wtwui/Crit.css',
					'wtwui/Dialog.css',
					'wtwui/Overlay.css',

					'jquery.fileupload-ui.css',
				),
				'combine' => true,
				'enabled' => true,
				'inline' => false
			),
			'install' => array(
				'files' => array(
					'bootstrap.css',
					'class.css',
					'install.css'
				),
				'combine' => true,
				'enabled' => true,
				'inline' => false
			),
		)
	)
);