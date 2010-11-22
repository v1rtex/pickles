<?php

/**
 * String Utility Collection
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
 * String Class
 *
 * Just a simple collection of static functions to accomplish some of the more
 * redundant string related manipulation.
 */
class String
{
	// {{{ Format Phone Number

	/**
	 * Format Phone Number
	 *
	 * Formats a 10 digit phone number with dashes as ###-###-####.
	 *
	 * @static
	 * @param  integer $number number to format
	 * @return string formatted phone number
	 * @todo   Allow a format to be passed in, perhaps make it configurable in config.php
	 */
	static function formatPhoneNumber($number)
	{
		$number = str_replace(array('(', ')', ' ', '-', '.', '_'), '', $number);
		return preg_replace('/^(\d{3})(\d{3})(.+)$/', '$1-$2-$3', $number);
	}

	// }}}

	// {{{ Random

	/**
	 * Random
	 *
	 * Generates a pseudo-random string based on the passed parameters.
	 *
	 * Note: Similar characters = 0, O, 1, I (and may be expanded)
	 *
	 * @static
	 * @param  integer $length optional length of the generated string
	 * @param  boolean $alpha optional include alpha characters
	 * @param  boolean $numeric optional include numeric characters
	 * @param  boolean $similar optional include similar characters
	 * @return string generated string
	 */
	public static function random($length = 8, $alpha = true, $numeric = true, $similar = true)
	{
		$characters = array();
		$string     = '';

		// Adds alpha characters to the list
		if ($alpha == true)
		{
			if ($similar == true)
			{
				$characters = array_merge($characters, range('A', 'Z'));
			}
			else
			{
				$characters = array_merge($characters, range('A', 'H'), range('J', 'N'), range('P', 'Z'));
			}
		}

		// Adds numeric characters to the list
		if ($numeric == true)
		{
			if ($similar == true)
			{
				$characters = array_merge($characters, range('0', '9'));
			}
			else
			{
				$characters = array_merge($characters, range('2', '9'));
			}
		}

		if (count($characters) > 0)
		{
			shuffle($characters);

			for ($i = 0; $i < $length; $i++)
			{
				$string .= $characters[$i];
			}
		}

		return $string;
	}

	// }}}

	// {{{ Truncate

	/**
	 * Truncate
	 *
	 * Truncates a string to a specified length and (optionally) adds a span to 
	 * provide a rollover to see the expanded text.
	 *
	 * @static
	 * @param  string $string string to truncate
	 * @param  integer $length length to truncate to
	 * @param  boolean $hover (optional) whether or not to add the rollover
	 * @return string truncate string
	 */
	public static function truncate($string, $length, $hover = true)
	{
		if (strlen($string) > $length)
		{
			if ($hover == true)
			{
				$string = '<span title="' . $string . '" style="cursor:help">' . substr($string, 0, $length) . '...</span>';
			}
			else
			{
				$string = substr($string, 0, $length) . '...';
			}
		}

		return $string;
	}

	// }}}

	// {{{ Upper Words

	/**
	 * Upper Words
	 *
	 * Applies strtolower() and ucwords() to the passed string. The exception
	 * being email addresses which are not formatted at all.
	 *
	 * @static
	 * @param  string $string string to format
	 * @return string formatted string
	 */
	public static function upperWords($string)
	{
		// Only formats non-email addresses
		if (filter_var($string, FILTER_VALIDATE_EMAIL) == false)
		{
			$string = ucwords(strtolower($string));
		}

		return $string;
	}

	// }}}
}

?>