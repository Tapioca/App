<?php

class GridFsException extends FuelException {}

class GridFs extends  Fuel\Core\Mongo_Db
{
	public static function getFs(Mongo_Db $instance)
	{
		return $instance->db->getGridFS();
	}

	/**
	*	--------------------------------------------------------------------------------
	*	GridFSRemove
	*	--------------------------------------------------------------------------------
	*
	*	Removes files from Mongo and captures error if failed
	*
	*/

	public function GridFSRemove($id)
	{
		if(! ($id instanceof MongoId))
		{
			$id = new MongoId($id);
		}

		//Get the GridFS Object and Remove file
		return self::GridFS()->delete($id);
	}

}