<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 20:20
 */

namespace le0daniel\System;


use le0daniel\System\Console\Commands\CreateTheme;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

class ComposerScripts {

	/**
	 * @var bool|string
	 */
	public static $mock_dir = false;

	/**
	 * Handels creating the Theme!
	 */
	public static function afterRootInstall(){

		/* Find Root Dir */
		$dir = __dir__;
		if(self::$mock_dir){
			$dir = self::$mock_dir;
		}

		for ($i = 0;$i < 5;$i++){

			if( file_exists($dir.'/.env.example') ){
				$root_dir = $dir;
				break;
			}

			$dir = realpath($dir.'/..');
		}

		if( ! isset($root_dir) ){
			throw new \Exception('Could not find root dir in 5 tries!');
		}

		/* Boot App */
		\le0daniel\System\App::init($root_dir);

		/** @var Application $console */
		$console = app()->getContainer()->make('console',[]);
		$console->add(resolve(CreateTheme::class));
		$console->setAutoExit(false);
		$console->setName('Installer');
		$console->setVersion('1.0.0');

		$console->run(new ArgvInput([
			'script.php',
			'new:theme'
		]));

		//$command = $console->find('new:theme');
		//$command->run()

		//echo $command->run();
	}

}