<?php
/**
 * Created by PhpStorm.
 * User: kernbrand
 * Date: 19.12.17
 * Time: 16:33
 */

namespace le0daniel\System\Console\Commands;

use Carbon\Carbon;
use Dotenv\Dotenv;
use le0daniel\System\App;
use le0daniel\System\Helpers\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class TestDatabase extends command{
	protected $file = 'web/wp/.maintenance';

	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('test:db')
			->setDescription('Tests the Database')
			->setHelp('');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output){

		if ( ! file_exists(Path::$root_dir . '/.env')) {
			return;
		}

		/* Load Dot Env */
		//App::loadEnv(Path::$root_dir);

		$output->writeln(sprintf('<info>Test database: %s</info>',  env('DB_NAME','NOT SET!')));
		$output->writeln(sprintf('<info>> User   : %s</info>',      env('DB_USER','NOT SET!')));
		$output->writeln(sprintf('<info>> Passwd : %s</info>',      (env('DB_PASSWORD',false)?'**********':'No PASSWD')));
		$output->writeln(sprintf('<info>> Host   : %s</info>',      env('DB_HOST','NOT Specified --> localhost')));

		try{
			$conn = new \mysqli(env('DB_HOST','localhost'), env('DB_USER',''), env('DB_PASSWORD',''),env('DB_NAME',''));

			if($conn->connect_error){
				throw new \Exception('Connection failed!');
			}

			$output->writeln('<info>> Successfully connected! </info>');
		}
		catch (\Exception $e){
			$output->writeln('<error>> Connection failed with error: '.$e->getMessage().' </error>');
		}



	}
}