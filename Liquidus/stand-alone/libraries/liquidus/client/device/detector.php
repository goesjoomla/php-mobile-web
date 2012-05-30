<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for detecting client device/browser.
 *
 * @copyright  Copyright (c) 2011 Manh-Cuong Nguyen
 * @author     Manh-Cuong Nguyen [nmc] <cuongnm@f6studio.com>
 * @license    http://f6studio.com/license
 *
 * @package    Liquidus
 * @link       http://f6studio.com/Liquidus
 *
 * @version    1.0.0
 */

class LiquidusClientDeviceDetector {
	/**
	 * Define data for mobile device detection.
	 *
	 * @var array
	 */
	private static $devices = array(
		/**
		 * Signatures for detecting feature phone that usually only has a WAP
		 * browser.
		 */
		'wap' => array(
			// Known User-Agent header.
			'HTTP_USER_AGENT' => array(
				'#(wap)#i',
			),

			// Known Accept header.
			'HTTP_ACCEPT' => array(
				'application/vnd.wap.wmlc',
				'application/vnd.wap.wmlscriptc',
				'image/vnd.wap.wbmp',
				'text/vnd.wap.wml',
				'text/vnd.wap.wmlscript'
			),

			// Known header usually only set by a feature phone's WAP browser.
			'HTTP_X_WAP_PROFILE' => TRUE
		),

		/**
		 * Signatures for detecting tablet PC in which the primary input method
		 * is touching the screen.
		 */
		'tablet' => array(
			// Known User-Agent header.
			'HTTP_USER_AGENT' => array(
				// Platforms.
				'#(android 3\.|tablet pc)#i',

				// Device types.
				'#(ipad|xoom|tablet)#i'
			)
		),

		/**
		 * Signatures for detecting mobile device that has a web browser support
		 * XHTML Mobile Profile.
		 */
		'mobile' => array(
			// Known User-Agent header.
			'HTTP_USER_AGENT' => array(
				// Companies.
				'#(blackberry|cingular|ddipocket|docomo|htc|htc;|htc/|htc-|htc_|lg;|lg/|lg-|lge |lge-|lgku|motorola|mot |mot-|nokia|o2|orange|sagem|samsung|sec-|sch|sgh|sph|sanyo|sharp|sie-|sony cmd|sonyericsson|sprint|t-mobile|verizon|vodafone)#i',

				// Platforms.
				'#(android|armv5|armv6|armv7|brew |maemo|palm|palmos|palm os|series60|series80|symbian|symbianos|symbian os|symbos|webos/|windows ce|window mobile|windows phone)#i',

				// Device types.
				'#(epoc|iphone|ipod|j-phone|kindle|mobile|nintendo wii|nook|pda|pocket|ppc;|playstation|psp|smartphone)#i',

				// Web browsers.
				'#(avantgo|fennec|iemobile|iris|j2me|midp|minimo|mini 9\.5|mobileexplorer|mobile safari|netfront|opera mini|opera mobi|pie|regking|tear|ucweb|up\.b|up\.l|up/|wireless)#i',

				// Palm devices usually have following strings.
				'#(blazer|centro|elaine|eudoraweb|hiptop|plink|plucker|pre/|treo|xiino)#i'
			),

			// Known Accept header.
			'HTTP_ACCEPT' => array(
				'application/vnd.wap.xhtml+xml',
				'application/xhtml+xml'
			),

			// Known header only set by a mobile device's web browser.
			'HTTP_PROFILE' => TRUE
		)
	);

	/**
	 * Public method to detect client device.
	 *
	 * @return  string  Client device type: default, tablet, mobile or phone
	 */
	public static function detect()
	{
		$detected = 'default';

		// Loop thru declared signatures for detecting client device type.
		foreach (self::$devices AS $device => $data)
		{
			foreach ($data AS $header => $signature)
			{
				// Skip checking full-featured web browser.
				if ($header != 'HTTP_ACCEPT' OR strpos($_SERVER[$header], 'text/html') === FALSE)
				{
					if (is_array($signature))
					{
						foreach ($signature AS $pattern)
						{
							if (substr($pattern, 0, 1) == '#' AND preg_match($pattern, $_SERVER[$header], $match))
							{
								$detected = $device;
								break 3;
							}
							elseif (strpos($_SERVER[$header], $pattern) !== FALSE)
							{
								$detected = $device;
								break 3;
							}
						}
					}
					elseif (is_bool($signature) AND $signature AND isset($_SERVER[$header]))
					{
						$detected = $device;
						break 2;
					}
				}
			}
		}

		return $detected;
	}
}
?>