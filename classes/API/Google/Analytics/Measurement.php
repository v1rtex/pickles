<?php

class API_Google_Analytics_Measurement
{
	public static function hit($tracking_id, $client_id, $type, $parameters)
	{
		$endpoint = 'https://ssl.google-analytics.com/collect';

		$payload = array(
			'v'   => 1,
			'tid' => $tracking_id,
			'cid' => $client_id,
			't'   => $type,
		);

		if ($parameters != array())
		{
			$payload = array_merge($payload, $parameters);
		}

		foreach ($payload as $variable => $value)
		{
			$payload[$variable] = urlencode($value);
		}

		$options = array(
			CURLOPT_HEADER         => false,
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => $payload,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_URL            => $endpoint,
			CURLOPT_USERAGENT      => 'PICKLES',
			CURLOPT_VERBOSE        => true,
		);

		$curl = curl_init();
		curl_setopt_array($curl, $options);
		curl_exec($curl);
		curl_close($curl);

		// API only returns a 1x1 pixel image, just going to push through boolean true
		return true;
	}

	public static function pageview($tracking_id, $client_id, $hostname = false, $page = false, $title = false)
	{

	}

	public static function event($tracking_id, $client_id, $category, $action, $label = false, $value = false)
	{
		$parameters = array(
			'ec' => $category,
			'ea' => $action,
		);

		foreach (array('el' => $label, 'ev' => $value) as $parameter => $value)
		{
			if ($value)
			{
				$parameters[$parameter] = $value;
			}
		}

		return self::hit($tracking_id, $client_id, 'event', $parameters);
	}

	public static function transaction($tracking_id, $client_id, $transaction_id, $affiliation = false, $revenue = false, $shipping = false, $tax = false, $currency = false)
	{

	}

	public static function item($tracking_id, $client_id, $transaction_id, $name, $price = false, $quantity = false, $sku = false, $category = false, $currency = false)
	{

	}

	public static function social($tracking_id, $client_id, $action, $network, $target)
	{

	}

	public static function exception($tracking_id, $client_id, $description = false, $fatal = false)
	{

	}

	public static function timing($tracking_id, $client_id, $category = false, $variable = false, $time = false, $label = false, $dns = false, $download = false, $redirect = false, $tcp = false, $server = false)
	{

	}

	public static function appview($tracking_id, $client_id, $name = false, $version = false, $description = false)
	{

	}

	public static function appevent($tracking_id, $client_id, $name, $category, $action)
	{

	}
}

?>
