<?php

class Controller_Api_Document extends Controller_Api
{
	private $appid;
	private $app_slug;
	private $collection;
	private $ref;
	private $revision;
	private $doc_status;
	private $query;
	private $debug;

	public function before()
	{
		parent::before();

		// to define with api key and query string
		$this->appid      = Input::get('appid', '4f7977b4c68deebf01000000');
		$this->app_slug   = $this->param('app_slug', false);
		$this->collection = $this->param('collection', false);
		$this->ref        = $this->param('ref', null);
		$this->revision   = Input::get('revision', null);
		$this->doc_status = Input::get('status', null);
		$this->query      = Input::get('q', null);
		$this->debug      = Input::get('debug', false);

		// cast revision ID as integer
		if(!is_null($this->revision))
		{
			$this->revision = (int) $this->revision;
		}

		// decode query
		if(!is_null($this->query))
		{
			$this->query = json_decode($this->query, true);
		}

	}

	/* Data
	----------------------------------------- */

	public function get_index()
	{
		if(self::$granted)
		{
			/* DEBUG
			self::$data   = array(
								'app_slug' => $this->app_slug,
								'collection' => $this->collection,
								'ref' => $this->ref,
								'revision' => $this->revision,
								'status' => $this->doc_status,
								'query' => $this->query
							);
			self::$status = 200;
			*/

			try
			{
				$document = Tapioca::document($this->app_slug, $this->collection, $this->ref);

				if($this->query)
				{
					$document->set($this->query);
				}

				// Set status restriction
				if(!is_null($this->doc_status))
				{
					$document->set(array('where' => array('_about.status' => (int) $this->doc_status)));
				}

				self::$data   = $document->get($this->revision);
				self::$status = 200;

				if($this->debug)
				{
					self::$data['debug'] = $document->last_query();
				}
			}
			catch (TapiocaException $e)
			{
				self::error($e->getMessage(), array(
								'app_slug' => $this->app_slug,
								'collection' => $this->collection,
								'ref' => $this->ref,
								'revision' => $this->revision,
								'status' => $this->doc_status,
								'query' => $this->query
							));
			}
		} // if granted
	}

	//create collection data.
	public function post_index()
	{
		if(self::$granted)
		{

		} // if granted
	}

	//update collection data.
	public function put_index()
	{
		if(self::$granted)
		{

		} // if granted
	}

	public function delete_index()
	{
		if(self::$granted)
		{

		} // if granted
	}
}