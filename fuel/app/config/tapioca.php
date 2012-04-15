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
				'status'
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
				'preview'
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
	)
);
