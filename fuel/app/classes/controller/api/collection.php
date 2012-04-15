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
				$values     = $this->dispatch($model);

				try
				{
					// fixtures
					$value = array(
						'structure' => array(
							array(
								"id" => "title",
								"label" => "Titre",
								"type" => "text",
								"rules" => array(
									"required",
									"min_length[5]"
								)
							),
							array(
								"id" => "desc",
								"label" => "Description",
								"type" => "textarea"
							)
						),
						'summary' => array(
							'title' => 'Titre'
						)
					);

					$app_name = 'dior-backstage';
					$user     = Auth::user();

					$user = array(
						'id'    => $user->get('_id'),
						'name'  => $user->get('name'),
						'email' => $user->get('email'),
					);


					$data       = $collection->update_data($value, $user);

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

	private function dispatch($values)
	{
		$ret     = array('summary' => array(), 'data' => array());
		$summary = Config::get('tapioca.collection.dispatch.summary');
		$data    = Config::get('tapioca.collection.dispatch.data');

		foreach($values as $key => $value)
		{
			if(in_array($key, $summary))
			{
				$ret['summary'][$key] = $value;
			}

			if(in_array($key, $data))
			{
				$ret['data'][$key] = $value;
			}
		}
		Debug::show($ret);
		exit;
	}

	/* Summary
	----------------------------------------- 

	public function get_summary()
	{
		if(self::$granted)
		{
			try
			{
				$collection = Tapioca::collection($this->appid, $this->namespace); //
				$summary = $collection->summary();

				self::$data = $summary;
				self::$status = 200;
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
		}
	}

	//update collection summary.
	public function put_summary()
	{
		if(self::$granted)
		{
			try
			{
				\Debug::show(Input::post('model'));
				exit;
				// fixtures
				$value = array(
					'namespace' => 'blog',
					'name' => 'Blog',
					'desc' => 'une courte déscription',
					'status' => 1
				);

				$collection = Tapioca::collection($this->appid, $this->namespace); //
				$summary = $collection->update_summary($value);

				self::$data = array('status' => $summary);
				self::$status = 200;

			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
		} // if granted
	}

	//Create a new entry in the collection. 
	public function post_summary($values)
	{
		if(self::$granted)
		{
			try
			{
				\Debug::show($values);
				exit;
				// fixtures
				$value = array(
					'namespace' => 'blog',
					'name' => 'Blog',
					'desc' => 'déscription de la collection blog',
					'status' => 1
				);

				$collection = Tapioca::collection($this->appid); //
				$summary = $collection->create_summary($value);

				self::$data = array('status' => 'ok', 'id' => (string) $summary);
				self::$status = 200;

			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
		} // if granted
	}
	*/
}