<?php

namespace Resque;

/**
 * Resque statistic management (jobs processed, failed, etc)
 *
 * @package		Resque/Stat
 * @author		Chris Boulton <chris.boulton@interspire.com>
 * @copyright	(c) 2010 Chris Boulton
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class Resque_Stat
{
	/**
	 * Get the value of the supplied statistic counter for the specified statistic.
	 *
	 * @param string $stat The name of the statistic to get the stats for.
	 * @return mixed Value of the statistic, or false is key doesn't exist
	 */
	public static function get($stat)
	{
		return (int)Resque::redis()->get('stat:' . $stat);
	}

	/**
	 * Increment the value of the specified statistic by a certain amount (default is 1)
	 *
	 * @param string $stat The name of the statistic to increment.
	 * @param int $by The amount to increment the statistic by.
	 * @return int The new value
	 */
	public static function incr($stat, $by = 1)
	{
		return (int)Resque::redis()->incrby('stat:' . $stat, $by);
	}

	/**
	 * Decrement the value of the specified statistic by a certain amount (default is 1)
	 *
	 * @param string $stat The name of the statistic to decrement.
	 * @param int $by The amount to decrement the statistic by.
	 * @return int The new value
	 */
	public static function decr($stat, $by = 1)
	{
		return (int)Resque::redis()->decrby('stat:' . $stat, $by);
	}

	/**
	 * Delete a statistic with the given name.
	 *
	 * @param string $stat The name of the statistic to delete.
	 * @return int The number of keys deleted
	 */
	public static function clear($stat)
	{
		return (int)Resque::redis()->delete('stat:' . $stat);
	}
}