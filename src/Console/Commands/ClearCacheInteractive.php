<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 08.12.17
 * Time: 22:41
 */

namespace le0daniel\System\Console\Commands;

use Carbon\Carbon;
use le0daniel\System\Helpers\File;
use le0daniel\System\Helpers\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ClearCacheInteractive extends Command{


	/**
	 * Configure
	 */
	protected function configure()
	{
		$this
			->setName('clear:cache')
			->setDescription('Clear cache')
			->setHelp('Interactively clear cache')
			->addOption('all','a',InputOption::VALUE_NONE,'Force all');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output){

		$dirs = glob(Path::cachePath('*'),GLOB_ONLYDIR);
		$dirs = array_map(function($value){return basename($value);},$dirs);
		array_unshift($dirs,'*');

		/* Set all if flag is set */
		if( $input->getOption('all') === true ){
			$dir = '*';
		}
		else{
			$helper = $this->getHelper('question');
			$question = new ChoiceQuestion(
				'Please select your favorite color [default <info>*</info>]:',
				$dirs,
				0
			);
			$question->setErrorMessage('Cache %s is invalid.');
			$dir = $helper->ask($input, $output, $question);
		}

		/* Clear input */
		unset($dirs[0]);

		if( $dir === '*' ){
			foreach($dirs as $dir){
				$this->clearDir(Path::cachePath($dir),$output);
			}
		}
		else{
			$this->clearDir(Path::cachePath($dir),$output);
		}
	}

	/**
	 * Recursive List of Files and Dirs
	 *
	 * @param $dir
	 *
	 * @return array
	 */
	protected function getAllFiles($dir){

		$raw_files = glob($dir.'/*');

		$files = [];
		$dirs = [];

		foreach ($raw_files as $file){
			if( is_dir($file) ){
				list($_files,$_dirs) = $this->getAllFiles($file);

				$files = array_merge($files,$_files);

				$dirs[] = $file;
				$dirs = array_merge($dirs,$_dirs);
			}
			else{
				$files[] = $file;
			}
		}

		return [$files,$dirs];
	}

	/**
	 * @param $pattern
	 * @param OutputInterface $output
	 */
	protected function clearDir($pattern,OutputInterface $output){

		$start = microtime(true);
		list($files,$dirs)= $this->getAllFiles($pattern);

		//print_r($files);
		//print_r($dirs);

		$output->writeln('Deleting (files):');

		foreach ($files as $file){
			$output->writeln('   > '.$file);
			if( ! unlink($file) ){
				$output->writeln('>  <error>'.$file.'</error>');
			}
		}

		$output->writeln('Deleting (directories):');

		foreach (array_reverse($dirs) as $dir){
			$output->writeln('   > '.$dir);
			if( ! rmdir($dir) ){
				$output->writeln('>  <error>'.$dir.'</error>');
			}
		}

		$delta = microtime(true) - $start;
		$output->writeln(
			sprintf('Deleted <info>%d</info> cached files in <info>%f</info>s', (count($files) + count($dirs) ),$delta)
		);

	}
}