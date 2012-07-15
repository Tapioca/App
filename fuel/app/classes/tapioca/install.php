<?php

namespace Tapioca;

use FuelException;
use Set;
use Auth;

class TapiocaInstallException extends FuelException {}

class Install
{
	public static function start(array $master, array $firstGroup)
	{
		try
		{
			// create regular account
			$masterId = Auth::user()->create($master);
		}
		catch (UserException $e)
		{
			throw new \TapiocaInstallException($e->getMessage());
		}

		$masterObject = Auth::user($master['email']);
		
		// then grant him admin
		$masterObject->granted_admin(100);


		// create first group
		try
		{
			$groupId = Auth::group()->create($firstGroup);
		}
		catch (GroupException $e)
		{
			throw new \TapiocaInstallException($e->getMessage());
		}

		// add group to user profile
		try
		{
			$masterObject->add_to_group($groupId, array('is_admin' => 1, 'level' => 100));
		}
		catch (UserException $e)
		{
			throw new \TapiocaInstallException($e->getMessage());
		}

		// add master to group granted witch admin permission
		try
		{
			$groupObject = Auth::group($groupId);

			$groupObject->add_to_group($master['email']); //, array('is_admin' => 1, 'level' => 100));

			$groupObject->add_admin($master['email']);
		}
		catch (AuthException $e)
		{
			throw new \TapiocaInstallException($e->getMessage());
		}

		return true;
	}
}