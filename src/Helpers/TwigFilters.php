<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 08.11.17
 * Time: 17:05
 */

namespace le0daniel\System\Helpers;

/**
 * Includes Specific Helpers
 *
 * Class TwigFilters
 * @package System\Helpers
 */
class TwigFilters {

	/**
	 * Twig filter for Theme Path
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function themePath(string $path):string{
		return Path::combine(
			get_template_directory_uri(),
			$path
		);
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function staticPath(string $path):string{
		return Path::combine(
			get_template_directory_uri(),
			'static',
			$path
		);
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	public static function shortcode(string $content):string{
		return do_shortcode($content,false);
	}

	/**
	 * Translates a String
	 *
	 * @param string $key
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function translate(string $key):string{

		return Language::translate($key);
	}

}