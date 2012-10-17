<?php

class Controller_Api_Collection extends Controller_Api
{
	protected static $appslug;
	private static $namespace;

	public function before()
	{
		parent::before();

		static::$appslug   = $this->param('appslug', false);
		static::$namespace = $this->param('namespace', false);

		// check collection's namespace 
		// and app exists
		if( static::$appslug && !static::assignApp())
		{
			return;
		}

		// check if user is allowed
		// for this app
		static::isInApp();
	}

	public function get_index()
	{
		if( static::$granted )
		{
			try
			{
				static::$status = 200;

				if( static::$namespace )
				{
					$revision     = Input::get('revision', null);
					$collection   = Tapioca::collection(static::$app, static::$namespace);

					static::$data = $collection->get( $revision );
				}
				else
				{
					$status       = ( static::isAppAdmin() ) ? 0 : 100;

					static::$data = Collection::getAll( static::$appslug, $status );;
				}
			}
			catch (CollectionException $e)
			{
				static::error($e->getMessage());
			}
		}
	}

	//create collection data.
	public function post_index()
	{
		if( static::$granted && static::isAppAdmin() )
		{
			$model = $this->setModel();
\Debug::dump(file_get_contents('php://input'), $model ); exit;
			try
			{
				// init tapioca first to get config & translation
				$collection = Tapioca::collection( static::$app ); 
			}
			catch (CollectionException $e)
			{
				static::error( $e->getMessage() );
			}
			
			$summary    = array();
			$schema     = array();
			$values     = $this->dispatch( $summary, $schema, $model );

			try
			{
				$summary = $collection->create_summary( $summary );

				if(count($schema) > 0)
				{
					$schema = $collection->update_data( $schema, static::$user );
				}

				static::$data   = $collection->get( null );
				static::$status = 200;

			}
			catch (CollectionException $e)
			{
				static::error($e->getMessage());
			}
		} // if granted
	}

	//update collection data.
	public function put_index()
	{		
		if( static::$granted && static::isAppAdmin() )
		{
			$model = $this->setModel();
// \Debug::dump( file_get_contents('php://input'), $model ); exit;
			try
			{
				// init tapioca first to get config & translation
				$collection = Tapioca::collection(static::$app, static::$namespace); 
			}
			catch (CollectionException $e)
			{
				static::error( $e->getMessage() );
			}

			$summary = array();
			$schema  = array();
			
			$this->dispatch( $summary, $schema, $model );
// \Debug::dump( $summary ); exit;
			// format previous revision as new to compare
			// goals is to know if we have a new revision or just the same data
			// QUESTION: this migth be in the Collection Class ?
			$foo      = array();
			$previous = array();

			$this->dispatch( $foo, $previous, $collection->data() );

			try
			{
				$summary = $collection->update_summary($summary);

				// TODO: find a better way to make a diff
				if(json_encode($previous) != json_encode($schema))
				{
					$schema = $collection->update_data($schema, static::$user);
				}

				static::$data   = $collection->get( null );
				static::$status = 200;

			}
			catch (CollectionException $e)
			{
				static::error( $e->getMessage() );
			}
		} // if granted
	}

	public function delete_index()
	{
		if( static::$granted && static::isAppAdmin() )
		{
			$data = Tapioca::collection(static::$app, static::$namespace)->delete(); 

			static::$data   = array('status' => $data);
			static::$status = 200;
		}
	}

	public function delete_drop()
	{
		if( static::$granted && static::isAppAdmin() )
		{
			$documents = Tapioca::document(static::$app, static::$namespace);
			$delete    = $documents->drop();
			static::$data   = array('status' => $delete);
			
			static::$status = 200;
		}
	}

	private function setModel()
	{
		return array(
				'namespace'    => Input::json('namespace', false),
				'name'         => Input::json('name', false),
				'desc'         => Input::json('desc', false),
				'status'       => Input::json('status', false),
				'preview'      => Input::json('preview', false), 
				'schema'       => Input::json('schema', false), 
				'summary'      => Input::json('summary', false), 
				'summaryEdit'  => Input::json('summaryEdit', false),
				'dependencies' => Input::json('dependencies', false),
				'indexes'      => Input::json('indexes', false),
				'callback'     => Input::json('callback', false),
				'templates'    => Input::json('templates', false)
			);
	}

	private function dispatch(&$summary, &$schema, $values)
	{
		$arrSummary = Config::get('tapioca.collection.dispatch.summary');
		$arrData    = Config::get('tapioca.collection.dispatch.data');

		foreach($values as $key => $value)
		{
			if(in_array($key, $arrSummary))
			{
				$summary[$key] = $value;
			}

			if(in_array($key, $arrData))
			{
				$schema[$key] = $value;
			}
		}

		return;
	}
}