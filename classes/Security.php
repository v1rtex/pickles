<?php

/**
 * Security System for PICKLES 
 *
 * PHP version 5
 *
 * Licensed under The MIT License 
 * Redistribution of these files must retain the above copyright notice.
 *
 * @author    Josh Sherman <josh@gravityblvd.com>
 * @copyright Copyright 2007-2010, Gravity Boulevard, LLC
 * @license   http://www.opensource.org/licenses/mit-license.html
 * @package   PICKLES
 * @link      http://p.ickl.es
 */

/**
 * Security Class
 *
 * Collection of static methods for handling security within a website running
 * on PICKLES. Requires sessions to be enabled.
 *
 * @usage <code>Security::setLevel('level');</code>
 * @usage <code>Security::isLevel('level');</code>
 */
class Security
{
	/**
	 * Check Session
	 *
	 * Checks if sessions are enabled.
	 *
	 * @return boolean whether or not sessions are enabled
	 */
	private static function checkSession()
	{
		if (session_id() == '')
		{
			throw new Exception('Sessions must be enabled to use the Security class');
		}
		else
		{
			return true;
		}
	}

	/**
	 * Check Level
	 *
	 * Checks if a passed level is an integer and/or properly defined in the
	 * site's configuration file.
	 *
	 * @param  mixed access level to validate
	 * @return whether ot not the access level is valid
	 */
	private static function checkLevel(&$access_level)
	{
		if (is_int($access_level))
		{
			return true;
		}
		else
		{
			$config = Config::getInstance();

			// Attempts to validate the string passed
			if (isset($config->security[$access_level]))
			{
				if (is_numeric($config->security[$access_level]))
				{
					$access_level = (int)$config->security[$access_level];
					return true;
				}
				else
				{
					throw new Exception('Level "' . $access_level . '" is not numeric in config.ini');
				}
			}
			else
			{
				throw new Exception('Level "' . $access_level . '" is not defined in config.ini');
			}
		}
	}

	/**
	 * Set Level
	 *
	 * Sets the security level.
	 *
	 * @param  mixed $access_level access level to set this session to
	 * @return boolean true on success, thrown exception on error
	 */
	public static function setLevel($access_level)
	{
		if (self::checkSession() && self::checkLevel($access_level))
		{
			$_SESSION['__pickles']['security']['level'] = $access_level;
		}

		return true;
	}

	/**
	 * Clear Level
	 *
	 * Clears out the security level.
	 *
	 * @return boolean true
	 */
	public static function clearLevel()
	{
		if (isset($_SESSION['__pickles']['security']['level']))
		{
			$_SESSION['__pickles']['security']['level'] = null;
		}

		return true;
	}

	/**
	 * Is Level
	 *
	 * Checks the user's access level is exactly the passed level
	 *
	 * @param  integer $access_level access level to be checked against
	 * @return boolean whether or not the user is that level
	 */
	public static function isLevel()
	{
		$is_level = false;

		if (self::checkSession())
		{
			if (isset($_SESSION['__pickles']['security']['level']))
			{
				foreach (func_get_args() as $access_level)
				{
					if (self::checkLevel($access_level))
					{
						if ($_SESSION['__pickles']['security']['level'] == $access_level)
						{
							$is_level = true;
							break;
						}
					}
				}
			}
			else
			{
				throw new Exception('Security level has not been set');
			}
		}

		return $is_level;
	}

	/**
	 * Has Level
	 *
	 * Checks the user's access level against the passed level.
	 *
	 * @param  integer $access_level access level to be checked against
	 * @return boolean whether or not the user has access
	 */
	public static function hasLevel()
	{
		$has_level = false;

		if (self::checkSession())
		{
			if (isset($_SESSION['__pickles']['security']['level']))
			{
				foreach (func_get_args() as $access_level)
				{
					if (self::checkLevel($access_level))
					{
						if ($_SESSION['__pickles']['security']['level'] >= $access_level)
						{
							$has_level = true;
							break;
						}
					}
				}
			}
			else
			{
				throw new Exception('Security level has not been set');
			}
		}

		return $has_level;
	}

	/**
	 * Between Level
	 *
	 * Checks the user's access level against the passed range.
	 *
	 * @param  integer $low access level to be checked against
	 * @param  integer $high access level to be checked against
	 * @return boolean whether or not the user has access
	 */
	public static function betweenLevel($low, $high)
	{
		$between_level = false;

		if (self::checkSession())
		{
			if (isset($_SESSION['__pickles']['security']['level']))
			{
				if (self::checkLevel($low) && self::checkLevel($high))
				{
					if ($_SESSION['__pickles']['security']['level'] >= $low && $_SESSION['__pickles']['security']['level'] <= $high)
					{
						$between_level = true;
						break;
					}
				}
			}
			else
			{
				throw new Exception('Security level between not been set');
			}
		}

		return $between_level;
	}
}

?>
