<?php

namespace Tapioca;

use FuelException;
use Set;

class InstallException extends FuelException {}

class Install
{
	public static function start(array $master, array $firstApp)
	{
		try
		{
			// create regular account
			$masterId = Tapioca::user()->create( $master, false, true );
		}
		catch (UserException $e)
		{
			throw new \InstallException( $e->getMessage() );
		}

		$masterObject = Tapioca::user( $master['email'] );


		// create first app
		try
		{
			$appId = Tapioca::app()->create( $firstApp );
		}
		catch (AppException $e)
		{
			throw new \InstallException( $e->getMessage() );
		}

		// add app to user profile
		try
		{
			$masterObject->add_to_app( $appId );
		}
		catch (UserException $e)
		{
			throw new \InstallException( $e->getMessage() );
		}

		// add master to app granted witch admin permission
		try
		{
			$appObject = Tapioca::app( $appId );

			$appObject->add_to_app( $masterId, 100 );

			$appObject->add_admin( $masterId );
		}
		catch (AuthException $e)
		{
			throw new \InstallException($e->getMessage());
		}

		return true;
	}
}