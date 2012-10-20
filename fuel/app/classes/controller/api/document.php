<?php

class Controller_Api_Document extends Controller_Api
{
	protected static $appslug;
	private static $collection;
	private static $ref;
	private static $revision;
	private static $locale;
	private static $query;

	public function before()
	{
		parent::before();

		static::$appslug    = $this->param('appslug', false);
		static::$ref        = $this->param('ref', null);

		$namespace  = $this->param('namespace', false);

		// check collection's namespace 
		// and app exists
		if( static::$appslug && !static::assignApp() )
		{
			return;
		}

		// if no collection define
		if( !$namespace )
		{
			static::restricted();
			return;
		}

		try
		{
			static::$collection = Tapioca::collection( static::$app, $namespace );
		}
		catch( TapiocaException $e )
		{
			static::error($e->getMessage());
			return;
		}

		static::$locale     = Input::get('l', null);
		static::$revision   = Input::get('r', null);
		static::$query      = Input::get('q', null);

		// cast revision ID as integer
		if( !is_null( static::$revision ) )
		{
			static::$revision = (int) static::$revision;
		}

		// decode query
		if( !is_null( static::$query ) )
		{
			static::$query = json_decode(static::$query, true);
		}

	}

	/* Data
	----------------------------------------- */

	public function get_index()
	{
		if( static::$granted )
		{

			try
			{
				$document = Tapioca::document( static::$app, static::$collection, static::$ref, static::$locale );

				if( static::$query )
				{
					$document->set( static::$query );
				}

				// Set status restriction
				// if( !is_null(static::$doc_status) )
				// {
				// 	$document->set(array('where' => array('_tapioca.status' => (int) static::$doc_status)));
				// }

				if( static::$ref )
				{
					static::$data   = $document->get( );
				}
				else
				{
					static::$data   = $document->getAll();
				}

				static::$status = 200;
			}
			catch ( TapiocaException $e )
			{
				static::error( $e->getMessage() );
			}
		} // if granted
	}

	//create collection data.
	public function post_index()
	{
		if( static::$granted )
		{
			$model = $this->clean();

			if( !$model )
			{
				static::$data   = array('error' => __('tapioca.missing_required_params'));
				static::$status = 500;
			}
			else
			{
				
				$document = Tapioca::document(static::$app, static::$collection, null, static::$locale);

				try
				{
					static::$data   = $document->save($model, static::$user);
					static::$status = 200;

				} catch (DocumentException $e)
				{
					static::error( $e->getMessage() );
				}
			}
		} // if granted
	}

	//update collection data.
	public function put_index()
	{
		if( static::$granted && static::$ref)
		{
			$model = $this->clean();

			if( !$model )
			{
				static::$data   = array('error' => __('tapioca.missing_required_params'));
				static::$status = 500;
			}
			else
			{

				$document = Tapioca::document(static::$app, static::$collection, static::$ref, static::$locale);
	
				static::$data   = $document->save($model, static::$user);
				static::$status = 200;
			}
		} // if granted
	}

	public function delete_index()
	{
		if( static::$granted )
		{
				$document     = Tapioca::document(static::$app, static::$namespace, static::$ref);
				
				static::$data   = array('status' => $document->delete());
				static::$status = 200;
		} // if granted
	}

	public function get_status()
	{
		if( static::$granted )
		{
			$document     = Tapioca::document(static::$app, static::$namespace, static::$ref, static::$locale);

			if(is_null(static::$doc_status))
			{
				static::$data   = array('error' => __('tapioca.missing_required_params'));
				static::$status = 500;
			}
			else
			{
				static::$data   = array('revisions' => $document->update_status(static::$doc_status, static::$revision));
				static::$status = 200;
			}
		}
	}

	private function clean()
	{
		$model = Input::json(null, false);

		if( $model )
		{
			if(isset($model['_ref']))
			{
				unset($model['_ref']);
			}

			if(isset($model['_tapioca']))
			{
				unset($model['_tapioca']);
			}

			return $model;
		}

		return false;
	}
}