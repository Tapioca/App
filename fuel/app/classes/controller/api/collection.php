<?php

class Controller_Api_Collection extends Controller_Api
{
	private static $namespace;
	private static $is_allowed;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		static::$namespace = $this->param('namespace', false);

		static::$is_allowed = self::$group->is_admin(self::$user->get('id'));
	}

	// Only admins are allowed to edit Collections
	private static function is_granted()
	{
		if(!static::$is_allowed)
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
				self::$status = 200;

				if(static::$namespace)
				{
					$revision = Input::get('revision', null);

					$collection = Tapioca::collection(static::$group, static::$namespace);

					self::$data = $collection->get($revision, self::$user);
				}
				else
				{
					$status     = (static::$is_allowed) ? 0 : 100;

					$collection = Tapioca::collection(static::$group);
					$all        = $collection->all($status, static::$user);

					if(count($all) == 0)
					{
						self::$status = 204;
					}

					self::$data = $all;
				}
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

			try
			{
				// init tapioca first to get config & translation
				$collection = Tapioca::collection(static::$group); 
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
			
			if(!$model)
			{
				self::$data   = array('error' => __('tapioca.missing_required_params'));
				self::$status = 500;
			}
			else
			{
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

					self::$data   = $collection->get(null, self::$user);
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

			try
			{
				// init tapioca first to get config & translation
				$collection = Tapioca::collection(static::$group, static::$namespace); 
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
				
			if(!$model)
			{
				self::$data   = array('error' => __('tapioca.missing_required_params'));
				self::$status = 500;
			}
			else
			{
				$summary    = array();
				$data       = array();
				
				$this->dispatch($summary, $data, $model);

				// format previous revision as new to compare
				// goals is to know if we have a new revision or just the same data
				// QUESTION: this migth be in the Collection Class ?
				$foo      = array();
				$previous = array();
				$this->dispatch($foo, $previous, $collection->data());

				try
				{
					$summary = $collection->update_summary($summary);

					// TODO: find a better way to make a diff
					if(json_encode($previous) != json_encode($data))
					{
						$data = $collection->update_data($data, self::$user);
					}

					self::$data   = $collection->get(null, self::$user);
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