<?php

namespace Tapioca\Jobs;

use Tapioca;

class Dependency
{
	public function perform()
	{
		// \Cli::write(print_r($this->args, true));

		Tapioca::base();

		$db = \Mongo_Db::instance();

		$dbCollectionName = $this->args['appslug'].'-'.$this->args['collection'];
		$where = array(
						'_ref'              => $this->args['ref'],
						'_tapioca.revision' => $this->args['revision'],
					);

		// source document
		$originDoc   = $db
						->where( $where )
						->get( $dbCollectionName );

		// \Cli::write($dbCollectionName);
		// \Cli::write(print_r($where, true));		
		// \Cli::write(print_r($originDoc, true));


		if( count( $originDoc ) != 1 )
		{
			return;
		}

		$originDoc = $originDoc[0];

		// \Cli::write( print_r($originDoc, true) );

		// list all collections with dependencies
		$collections = $db
						->select( array('namespace', 'dependencies') )
						->where(array(
							'app_id'                  => $this->args['appslug'],
							'active'                  => true,
							'dependencies.collection' => $this->args['collection']
						))
						->get( \Config::get('tapioca.collections.collections') );

		// \Cli::write(print_r($collections, true));

		if( count( $collections) == 0)
		{
			return;
		}

		foreach( $collections as $collection)
		{
			foreach( $collection['dependencies'] as $dependency )
			{
				if( $dependency['collection'] == $this->args['collection'] )
				{
					$path             = $dependency['path'].'.ref';
					$dbCollectionName = $this->args['appslug'].'-'.$collection['namespace'];

					$set = array();

					foreach( $dependency['fields'] as $field )
					{
						$set[ $field ] = \Arr::get( $originDoc, $field, null);
					}


					$update = array('$set' => array($dependency['path'].'.embedded' => $set) );

					$where = array( $path             => $this->args['ref'],
									'_tapioca.locale' => $this->args['locale'],
									'_tapioca.status' => 100
								);

					// \Cli::write( $dbCollectionName );
					// \Cli::write(print_r($where, true));
					// \Cli::write(print_r($update, true));

					$documents = $db
									->where( $where )
									->update_all( $dbCollectionName, $update, true );

				}
			}

		}

	}
}