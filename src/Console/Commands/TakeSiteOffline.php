<?php
/**
 * Created by PhpStorm.
 * User: kernbrand
 * Date: 05.12.17
 * Time: 15:36
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

class TakeSiteOffline extends Command {

	/**
	 * @var string
	 */
	protected $code_format = '<?php $GLOBALS[\'upgrading\']=%d;';

	/**
	 * @var string
	 */
	protected $file = '.maintenance';

	/**
	 * @var string
	 */
	protected $dir = 'wp';

	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('down')
			->setDescription('Takes the site down for 10 Minutes')
			->setHelp('Takes the site down for 10 Minutes: creates a .maintenance file!');
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

		$code = sprintf($this->code_format,time());

		$dir = Path::webroot() .'/'.$this->dir;

		if( ! file_exists($dir) || ! is_dir($dir) ){
			throw new \Exception('WP dir not found');
		}

		file_put_contents($dir.'/'.$this->file,$code);
		$output->writeln('<info>Wordpress site down for 10 min!</info>');

	}


}