<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 12:55
 */

namespace le0daniel\System\Console\Commands;

use Carbon\Carbon;
use le0daniel\System\Helpers\File;
use le0daniel\System\Helpers\Path;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ClearCacheVC extends Command {

	/**
	 * Configure
	 */
	protected function configure()
	{
		$this
			->setName('clear:cache:vc')
			->setDescription('Clears the VC Cache')
			->setHelp('Will delete all VC Cache files');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output){

		$files = glob(Path::cachePath('vc/*.serialized.vc'));
		$start = microtime(true);

		$output->writeln('Deleting: ');
		foreach ($files as $file){
			$output->writeln('   > '.$file);
			unlink($file);
		}

		$delta = microtime(true) - $start;
		$output->writeln(
			sprintf('Deleted <info>%d</info> cached files in <info>%f</info>s',count($files),$delta)
		);

	}

}