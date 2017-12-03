<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 20:21
 */

namespace le0daniel\System\Helpers;


use Carbon\Carbon;

class File {

	/**
	 * @param $lines
	 *
	 * @return array
	 */
	public static function generateCommentBlockArray($lines):array{
		/* Cast as array */
		if(is_string($lines)){
			$lines = [$lines];
		}

		return array_merge(['/**'],self::prefixLines(' * ',$lines),[' */']);
	}

	/**
	 * @param string $prefix
	 * @param array $lines
	 *
	 * @return array
	 */
	public static function prefixLines(string $prefix,array $lines):array{

		return array_map(function($item)use($prefix){
			return $prefix.$item;
		},$lines);

	}

	/**
	 * @param string $namespace
	 * @param array $uses
	 * @param string $creator
	 *
	 * @return array
	 */
	public static function generatePhpFileHeader(string $namespace='',array $uses = [],string $creator = 'FileGenerator'):array{

		$now = Carbon::now();

		$header = [];

		if( ! empty($namespace) ){
			$header[] = sprintf('namespace %s;',$namespace).PHP_EOL;
		}

		if( ! empty($uses) ){
			foreach($uses as $use){
				$header[] = sprintf('use %s;',$use);
			}
		}

		$doc_block = self::generateCommentBlockArray([
			'Created by '.$creator,
			sprintf('PHP Version (%s)',phpversion()),
			'Date: '.$now->toDateString(),
			'Time: '.$now->toTimeString(),
		]);

		/* Add new Line at the end */
		$header[] = PHP_EOL;

		/* Add Begin of file! */
		$fullheader = array_merge($doc_block,$header);
		array_unshift($fullheader,'<?php');

		return $fullheader;
	}

	/**
	 * @param array $lines
	 */
	public static function generatePhpFileFromArrayOfLines(array $lines){

		/* Delete if php is set */
		if( substr($lines[0],0,5) === '<?php' ){
			unset($lines[0]);
		}

		/**/
	}

	/**
	 * @param string $name
	 * @param array $content
	 * @param string $extends
	 * @param array $interfaces
	 * @param array $traits
	 *
	 * @return array
	 */
	public static function generatePhpClass(string $name,array $content=[],string $extends='',$interfaces=[],$traits = []):array{

		$class_comment_block = self::generateCommentBlockArray([
			sprintf('Class %s',$name)
		]);

		$class_line = 'class '.$name;

		if( ! empty($extends) ){
			$class_line .= sprintf(' extends %s',$extends);
		}

		if( ! empty($interfaces) ){
			$class_line .= sprintf(' implements %s',self::getInterfacesAsString($interfaces));
		}


		$class = $class_comment_block;
		$class[] = $class_line;
		$class[] = '{';

		if ( ! empty($traits) ){
			array_unshift($content,sprintf('use %s;',implode(', ',$traits)));
		}

		if( ! empty($content) ){
			$class[] = $content;
		}

		$class[] = '}';

		return $class;
	}

	/**
	 * @param string $name
	 * @param string $visibility
	 * @param array $arguments
	 * @param bool $static
	 * @param array $content
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function generatePhpMethod(string $name, string $visibility = 'public',array $arguments,bool $static = false,array $content=[]){

		$visibilities = [
			'public',
			'protected',
			'private'
		];

		if( ! in_array($visibility,$visibilities)){
			throw new \Exception('Invalid Mehtod visibility provided!');
		}

		$method = array_filter([
			$visibility,
			($static)?'static':false,
			'function',
			$name,
			'(',
			implode(', ',$arguments),
			')'
		]);

		return array_filter([
			implode(' ',$method),
			'{',
			$content,
			'}'
		]);

	}

	/**
	 * @param $stringOrArray
	 *
	 * @return string
	 */
	public static function getInterfacesAsString($stringOrArray):string {
		if(is_string($stringOrArray)){
			return $stringOrArray;
		}
		return implode(', ',$stringOrArray);
	}

	/**
	 * @param array $lines
	 * @param int $count
	 *
	 * @return string
	 */
	public static function generateFromArray(array $lines,int $count = 0):string{

		$add = 4;
		$return = [];

		foreach($lines as $line){
			if (is_array($line)){
				$return[] = self::generateFromArray($line, ($count + $add) );
			}
			else{
				$return[] = str_repeat(' ',$count).$line;
			}
		}

		return implode(PHP_EOL,$return);
	}

}