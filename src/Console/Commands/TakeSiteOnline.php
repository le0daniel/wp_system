<?php
/**
 * Created by PhpStorm.
 * User: kernbrand
 * Date: 05.12.17
 * Time: 16:07
 */

namespace le0daniel\System\Console\Commands;

use Carbon\Carbon;
use le0daniel\System\Helpers\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class TakeSiteOnline extends Command{

	protected $file = 'web/wp/.maintenance';

	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('up')
			->setDescription('Takes the site up')
			->setHelp('Takes the site up: deletes the .maintenance file!');
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

		$file = Path::$root_dir .'/'.$this->file;

		if( ! file_exists($file) ){
			$output->writeln('<info>Site is already up</info>');
			return;
		}

		unlink($file);

		$output->writeln('<info>Wordpress site is up!</info>');
	}

}