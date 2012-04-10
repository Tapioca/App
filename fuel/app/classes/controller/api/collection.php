<?php

class Controller_Api_Collection extends Controller_Api
{
	private $appid;
	private $namespace;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		$this->appid = '4f7977b4c68deebf01000000';
		$this->namespace = 'blog';
	}

	public function get_all()
	{
		if(self::$granted)
		{
			try
			{
				$collection = Tapioca::collection($this->appid); //
				$all = $collection->all();

				self::$data = $all;
				self::$status = 200;
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
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
				$collection = Tapioca::collection($this->appid, $this->namespace); //
				$summary = $collection->summary();
				$data = $collection->data();

				self::$data = array_merge($summary, $data);
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
				$user = Auth::user();

				$user = array(
					'id' => $user->get('_id'),
					'name' => $user->get('name'),
					'email' => $user->get('email'),
				);

				$collection = Tapioca::collection($this->appid, $this->namespace); //
				$data = $collection->update_data($value, $user);

				self::$data = array('status' => $data);
				self::$status = 200;

			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage());
			}
		} // if granted
	}

	/* Summary
	----------------------------------------- */

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
				// fixtures
				$value = array(
					'namespace' => 'articles',
					'name' => 'Articles',
					'desc' => 'une courte déscription de la collection avec un peu de détails mise à jour en REST elle même mise à jour',
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
	public function post_summary()
	{
		if(self::$granted)
		{
			try
			{
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
}