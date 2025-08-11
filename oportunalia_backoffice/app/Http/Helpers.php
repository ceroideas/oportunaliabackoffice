<?php

/**
 * Composes a filename appending an extension and date to it.
 *
 * @param  array(string)  $filename
 * @param  string  $extension
 * @return string
 */
if (!function_exists('composeFilename')) {

	function composeFilename($filename, $extension)
	{
		$filename[] = '-'.date('Ymd-Hi');

		return strtolower( str_replace(' ', '-', implode('-', $filename) ) . '.' . $extension );
	}
}

/**
 * Makes a curl call to check if a URL returns 404 or not.
 *
 * @param  string  $url
 * @return boolean
 */
if (!function_exists('checkUrl')) {

	function checkUrl($url)
	{
		if ($url == 'http://www.oportunalia.com') { return false; }
		$handle = curl_init($url);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($handle);
		$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		curl_close($handle);
		return $httpCode != 404 && $httpCode != 503;
	}
}

/**
 * Download from a URL and save in 'wp-documents', only used for data migration.
 *
 * @param  string  $url
 * @return string | null
 */
if (!function_exists('migrateDocuments')) {

	function migrateDocuments($url)
	{
		if (checkUrl($url))
		{
			$fullpath_parts = explode('/', $url);
			$filename = array_pop($fullpath_parts);
			$filename_parts = explode('.', $filename);
			$ext = array_pop($filename_parts);
			$name = implode($filename_parts);

			Storage::disk('wp-documents')->put($name . '.' . $ext, fopen($url, 'r'));

			return Storage::disk('wp-documents')->url($name . '.' . $ext);

		} else { return null; }
	}
}

/**
 * Converts a string with a price (format used: "1.234,56 €") into a float.
 *
 * @param  string  $string
 * @return float
 */
if (!function_exists('priceval')) {

	function priceval($string)
	{
		$string = str_replace('m²', '', $string);
		$string = str_replace('€', '', $string);
		$string = str_replace('.', '', $string);
		$string = str_replace(',', '.', $string);
		return floatval($string);
	}
}
