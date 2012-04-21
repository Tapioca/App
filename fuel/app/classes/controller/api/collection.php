<?php

class Controller_Api_Collection extends Controller_Api
{
	private $appid;
	private $namespace;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		$this->appid = Input::get('appid', '4f7977b4c68deebf01000000');
		$this->namespace = $this->param('namespace', false);
		$this->app_slug   = $this->param('app_slug', false);
	}

	/* Data
	----------------------------------------- */

	public function get_index()
	{
		if(self::$granted)
		{
			try
			{
				if($this->namespace)
				{
					$revision = Input::get('revision', null);

					$collection = Tapioca::collection($this->appid, $this->namespace);
					self::$data = $collection->get($revision);
				}
				else
				{
					$collection = Tapioca::collection($this->appid);
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
				$collection = Tapioca::collection($this->appid); 
				$summary    = array();
				$data       = array();
				$values     = $this->dispatch($summary, $data, $model);

				try
				{

					$app_name = 'dior-backstage';
					$user     = Auth::user();

					$user = array(
						'id'    => $user->get('_id'),
						'name'  => $user->get('name'),
						'email' => $user->get('email'),
					);

					$summary = $collection->create_summary($summary);

					if(count($data) > 0)
					{
						$data = $collection->update_data($data, $user);
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
				$collection = Tapioca::collection($this->appid, $this->namespace); 
				$summary    = array();
				$data       = array();
				$values     = $this->dispatch($summary, $data, $model);

				try
				{

					$app_name = 'dior-backstage';
					$user     = Auth::user();

					$user = array(
						'id'    => $user->get('_id'),
						'name'  => $user->get('name'),
						'email' => $user->get('email'),
					);

					if(count($summary) > 0)
					{
						$summary = $collection->update_summary($summary);
					}

					if(count($data) > 0)
					{
						$data = $collection->update_data($data, $user);
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
		if(self::$granted)
		{
			$data = Tapioca::collection($this->appid, $this->namespace)->delete(); 

			self::$data   = array('status' => $data);
			self::$status = 200;
		}
	}

	public function get_documents()
	{
		if(self::$granted)
		{
			$document = Tapioca::document($this->app_slug, $this->namespace);
			self::$data = $document->all();
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