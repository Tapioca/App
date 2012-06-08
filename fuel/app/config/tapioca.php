<?php

return array(

	/*
	 * Table Names
	 */
	'tables' => array(
		'collections'     => 'collections',
		'documents'       => 'documents',
		'files'           => 'files',
	),

	/*
	 * Default documents/collections status
	 */
	'status' => array(
		array(
			-1,
			'out_of_date'
		),
		array(
			1,
			'draft'
		),
		array(
			100,
			'published'
		)
	),

	/*
	 * required fileds
	 */
	'validation' => array(
		'collection' => array(
			'summary' => array(
				'namespace',
				'name',
				'status', 
				'summary'
			),
			'data' => array(
				'structure', 
				'summary'
			)
		)
	),

	'collection' => array(
		'dispatch' => array(
			'summary' => array(
				'namespace',
				'name',
				'desc',
				'status',
				'preview', 
				'summary'
			),
			'data' => array(
				'structure', 
				'summary',
				'dependencies',
				'indexes',
				'callbacks',
				'templates'
			)
		)
	),

	/*
	 * Upload
	 */

	'upload' => array(
		'path'                => APPPATH.'tmp',
		'storage'             => DOCROOT.'files'.DIRECTORY_SEPARATOR,
		'public'              => Config::get('base_url').'files'.DIRECTORY_SEPARATOR,
		'field'               => 'tappfile',
		'randomize'           => true,
		'fileinfo_magic_path' => '',
		'ext_whitelist'	      => array('jpg', 'jpeg', 'gif', 'png', 'flv', 'mp4', 'ogv', 'doc') //, 'pdf', 'zip')
	),

	/*
	 * File by minetype
	 */

	'file_types' => array(
		'image' => array(
			'image/bmp', 
			'image/x-windows-bmp',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/png',
			'image/x-png',
			'image/tiff',	
		),
		'video' => array(
			'video/mpeg',
			'video/mp4',
			'application/ogg',
			'video/x-flv',
			'video/quicktime',
			'video/x-msvideo',
			'video/x-sgi-movie',
			'video/vnd.rn-realvideo' // LOL
		),
		'document' => array(
			'application/pdf',
			'application/msword',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'application/msword',
			'application/excel',
			'application/vnd.ms-excel',
			'application/msexcel',
			'application/powerpoint',
			'application/vnd.ms-powerpoint',
			'text/richtext',
			'text/rtf'
		)

	)

);
