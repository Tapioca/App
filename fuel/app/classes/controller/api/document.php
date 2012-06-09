<?php

class Controller_Api_Document extends Controller_Api
{
	private static $collection;
	private static $ref;
	private static $revision;
	private static $locale;
	private static $doc_status;
	private static $query;
	private static $mode;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		static::$collection = $this->param('collection', false);
		static::$ref        = $this->param('ref', null);
		static::$locale     = Input::get('locale', null);;
		static::$revision   = Input::get('revision', null);
		static::$doc_status = Input::get('status', null);
		static::$query      = Input::get('q', null);
		static::$mode       = Input::get('mode', null);

		// cast revision ID as integer
		if(!is_null(static::$revision))
		{
			static::$revision = (int) static::$revision;
		}

		// decode query
		if(!is_null(static::$query))
		{
			static::$query = json_decode(static::$query, true);
		}

	}

	/* Data
	----------------------------------------- */

	public function get_index()
	{
		if(self::$granted)
		{
			try
			{
				$document = Tapioca::document(self::$group, static::$collection, static::$ref, static::$locale);

				if(static::$query)
				{
					$document->set(static::$query);
				}

				// Set status restriction
				if(!is_null(static::$doc_status))
				{
					$document->set(array('where' => array('_about.status' => (int) static::$doc_status)));
				}

				// list for back-office
				if(static::$mode == 'list')
				{
					self::$data = $document->all();
				}
				else // standard API call
				{
					self::$data   = $document->get(static::$revision, static::$mode);
				}

				self::$status = 200;

				if(static::$debug)
				{
					self::$data['debug'] = $document->last_query();
				}
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
		} // if granted
	}

	//create collection data.
	public function post_index()
	{
		if(self::$granted)
		{
			$model    = json_decode(Input::post('model', false), true);
			$document = Tapioca::document(self::$group, static::$collection, null, static::$locale);

			if(!$model)
			{
				self::$data   = array('error' => __('tapioca.missing_required_params'));
				self::$status = 500;
			}
			else
			{
				$this->clean($model);

				self::$data   = $document->save($model, self::$user);
				self::$status = 200;
			}
		} // if granted
	}

	//update collection data.
	public function put_index()
	{
		if(self::$granted)
		{
			$model    = json_decode(Input::put('model', false), true);
			$document = Tapioca::document(self::$group, static::$collection, static::$ref, static::$locale);

			if(!$model)
			{
				self::$data   = array('error' => __('tapioca.missing_required_params'));
				self::$status = 500;
			}
			else
			{
				$this->clean($model);

				self::$data   = $document->save($model, self::$user);
				self::$status = 200;
			}
		} // if granted
	}

	public function delete_index()
	{
		if(self::$granted)
		{
				$document     = Tapioca::document(self::$group, static::$collection, static::$ref);
				
				self::$data   = array('status' => $document->delete());
				self::$status = 200;
		} // if granted
	}

	public function get_status()
	{
		if(self::$granted)
		{
			$document     = Tapioca::document(self::$group, static::$collection, static::$ref);

			if(is_null(static::$doc_status))
			{
				self::$data   = array('error' => __('tapioca.missing_required_params'));
				self::$status = 500;
			}
			else
			{
				self::$data   = array('status' => $document->update_status(static::$doc_status, static::$revision));
				self::$status = 200;
			}
		}
	}

	private function clean(&$model)
	{
		if(isset($model['_ref']))
		{
			unset($model['_ref']);
		}

		if(isset($model['_about']))
		{
			unset($model['_about']);
		}
	}
}