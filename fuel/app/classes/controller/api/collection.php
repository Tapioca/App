<?php

class Controller_Api_Collection extends Controller_Api
{
	private static $namespace;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		static::$namespace = $this->param('namespace', false);
	}

	// Only admins are allowed to edit Collections
	private static function is_granted()
	{
		$is_allowed = self::$group->is_admin(self::$user->get('id'));
		
		if(!$is_allowed)
		{
			self::restricted();
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
				if(static::$namespace)
				{
					$revision = Input::get('revision', null);

					$collection = Tapioca::collection(static::$group, static::$namespace);

					self::$data = $collection->get($revision);
				}
				else
				{
					$collection = Tapioca::collection(static::$group);
					self::$data = $collection->all();
				}

				self::$status = 200;
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
		}
	}

	//create collection data.
	public function post_index()
	{
		self::is_granted();

		if(self::$granted)
		{
			$model = json_decode(Input::post('model', false), true);
			
			if(!$model)
			{
				self::$data   = array('error' => 'tapioca.missing_required_params');
				self::$status = 500;
			}
			else
			{
				// init tapioca first to get config
				$collection = Tapioca::collection(static::$group); 
				$summary    = array();
				$data       = array();
				$values     = $this->dispatch($summary, $data, $model);

				try
				{

					$summary = $collection->create_summary($summary);

					if(count($data) > 0)
					{
						$data = $collection->update_data($data, self::$user);
					}

					self::$data   = $collection->get();
					self::$status = 200;

				}
				catch (TapiocaException $e)
				{
					self::error($e->getMessage());
				}
			}// if $model
		} // if granted
	}

	//update collection data.
	public function put_index()
	{
		self::is_granted();
		
		if(self::$granted)
		{
			$model = json_decode(Input::put('model', false), true);
			
			if(!$model)
			{
				self::$data   = array('error' => 'tapioca.missing_required_params');
				self::$status = 500;
			}
			else
			{
				// init tapioca first to get config
				$collection = Tapioca::collection(static::$group, static::$namespace); 
				$summary    = array();
				$data       = array();
				$values     = $this->dispatch($summary, $data, $model);

				try
				{

					$app_name = 'dior-backstage';

					if(count($summary) > 0)
					{
						$summary = $collection->update_summary($summary);
					}

					if(count($data) > 0)
					{
						$data = $collection->update_data($data, self::$user);
					}

					self::$data   = array('status' => $data);
					self::$status = 200;

				}
				catch (TapiocaException $e)
				{
					self::error($e->getMessage());
				}
			}// if $model
		} // if granted
	}

	public function delete_index()
	{
		self::is_granted();

		if(self::$granted)
		{
			$data = Tapioca::collection(static::$group, static::$namespace)->delete(); 

			self::$data   = array('status' => $data);
			self::$status = 200;
		}
	}

	public function delete_drop()
	{
		self::is_granted();
		
		if(self::$granted)
		{
			$documents = Tapioca::document(self::$group, static::$namespace);
			$delete    = $documents->drop();
			self::$data   = array('status' => $delete);
			
			self::$status = 200;
		}
	}

	private function dispatch(&$summary, &$data, $values)
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
				$data[$key] = $value;
			}
		}

		return;
	}
}