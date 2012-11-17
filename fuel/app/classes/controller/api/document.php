<?php

class Controller_Api_Document extends Controller_Api
{
	protected static $appslug;
	private static $collection;
	private static $document;
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

		// Document instance
		static::$document = Tapioca::document(static::$app, static::$collection, static::$ref, static::$locale );

	}

	/* Data
	----------------------------------------- */

	public function get_index()
	{
		if( static::$granted )
		{
			try
			{
				if( static::$query )
				{
					static::$document->set( static::$query );
				}

				if( static::$ref )
				{
					static::$data = static::$document->get( static::$revision );
				}
				else
				{
					static::$data = static::$document->getAll();
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

			if( $model )
			{
				try
				{
					static::$data   = static::$document->save( $model, static::$user );
					static::$status = 200;

				} catch (DocumentException $e)
				{
					static::error( $e->getMessage() );
				}
			} // if model
		} // if granted
	}

	public function put_index()
	{
		if( static::$granted && static::$ref)
		{
			$model = $this->clean();

			if( $model )
			{
				static::$data   = static::$document->save( $model, static::$user );
				static::$status = 200;
			}
		}
	}

	public function delete_index()
	{
		if( static::$granted )
		{
			if( ! static::deleteToken( 'document', static::$ref ))
			{
				return;
			}
			
			static::$data   = array('status' => static::$document->delete());
			static::$status = 200;
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
		
		static::$data   = array('error' => __('tapioca.missing_required_params'));
		static::$status = 500;

		return false;
	}
}