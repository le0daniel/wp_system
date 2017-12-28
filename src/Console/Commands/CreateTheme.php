<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 20:17
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

class CreateTheme extends Command{

	protected $name;
	protected $namespace;
	protected $slug;
	protected $root_dir;
	protected $public_folder_name = 'public_html';

	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('new:theme')
			->setDescription('This command should only be runned once')
			->setHelp('This command allows you to generate all files needed when creating a new Theme');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output){

		/* Set Root dir */
		$this->root_dir = Path::$root_dir;

		/* Helper */
		$helper = $this->getHelper('question');

		/* Create Name Question */
		$question = new Question('Please choose a name of your Theme (Human Readable) [<info>My Skeletton</info>]: ', null);
		$question->setNormalizer([$this,'normalizeName']);
		$question->setValidator([$this,'validateName']);
		$question->setMaxAttempts(10);

		/* Create the Theme Slug */
		$this->name = $helper->ask($input, $output, $question);
		$this->slug = snake_case($this->name);
		$this->namespace = $this->getNamespace($this->name);

		/* Show the name to the user */
		$output->writeln('Your theme is called: '.$this->name.' [<info>'.$this->slug.']</info>');
		$output->writeln('Namespace > <info> '.$this->namespace.' </info>');

		/* Update composer Json to support PSR-4 for the Theme */
		$this->updateComposerJson();

		/* Modify Extender File */
		$this->modifyConfigFile();

		/* Update Webpack Mix for theme */
		$this->updateWebpackMix();

		/* Edit Functions file */
		$this->modifyFunctionsFile();

		/* Create style.css file */
		$this->createStyleFile();

		/* Modify Extender File */
		$this->modifyExtenderFile();

		/* Rename the theme */
		rename(Path::themesPath('skeletton'),Path::themesPath($this->slug));

		/*  */
		$output->writeln('Theme: <info>'.$this->name.'</info> successfully installed');

		/* Check .env file */
		if( ! file_exists($this->root_dir.'/.env')){
			return;
		}

		$dotenv = file_get_contents($this->root_dir.'/.env');

		/* Configure .env */
		$question = new Question('Please enter the Database Username [<info>leodanielDev</info>]: ', 'leodanielDev');
		$question->setNormalizer([$this,'wrapInQuotes']);
		$db['database_user']= $helper->ask($input, $output, $question);

		$question = new Question('Please enter the Password for User '.$db['database_user'].' [<info>hidden</info>]: ', '');
		$question->setNormalizer([$this,'wrapInQuotes']);
		$question->setHidden(true);
		$db['database_password']= $helper->ask($input, $output, $question);

		$question = new Question('Please enter the Database Name [<info>WordpressDB</info>]: ', 'WordpressDB');
		$question->setNormalizer([$this,'wrapInQuotes']);
		$db['database_name']= $helper->ask($input, $output, $question);

		$question = new Question('Please enter the host of the DB [<info>127.0.0.1</info>]: ', '127.0.0.1');
		$question->setNormalizer([$this,'wrapInQuotes']);
		$db['database_host']= $helper->ask($input, $output, $question);

		/* Replace Values and write config file */
		$dotenv = str_replace(array_keys($db),array_values($db),$dotenv);
		file_put_contents($this->root_dir.'/.env',$dotenv);

		/* Error Handling */
		mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);

		/* Check Database */
		try{
            $connection = mysqli_connect($db['database_host'],$db['database_user'],$db['database_password'],$db['database_name']);
            if(!$connection){
                throw new \Exception('Ups, no connection');
            }
        }
        catch (\Exception $e){
            $output->writeln('<error>Unable to connect to database ('.$db['database_name'].')!</error>');
            $output->writeln('<error>Please check your .env file</error>');
        }
        $output->writeln('<info> > mysql: connected successfully!</info>');


		/* Done */
		$output->writeln('Setup done!');
		$output->writeln('<info>Don\'t forget to dump composer autoloader</info>');
		$output->writeln('Use WP cli to complete installation!');
	}

	/**
	 * @param $name
	 *
	 * @return string
	 */
	protected function getNamespace($name){
		return 'Themes\\'.ucfirst( camel_case( $name ) );
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function normalizeName($value){
		return rtrim(trim($value));
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public function wrapInQuotes($value){
		if(empty($value) || ! is_string($value) ){
			return $value;
		}

		return "'". addslashes($value) ."'";
	}

	/**
	 * @param string $answer
	 *
	 * @return string
	 */
	public function validateName($answer){
		if (!is_string($answer)) {
			throw new \RuntimeException('You must enter a string');
		}

		if(empty($answer)){
			throw new \RuntimeException('Name can not be empty');
		}

		if( ! preg_match('/^[a-zA-Z][a-z0-9A-Z\s]{3,30}$/',$answer)){
			throw new \RuntimeException('The name must only contain [a-z A-Z 0-9 whitespaces] and must be between 4 and 30 chars');
		}

		return $answer;
	}

	/**
	 * Updates the composer.json file for the namespace
	 */
	protected function updateComposerJson(){

		$file_path = $this->root_dir.'/composer.json';

		/* Decode the json file */
		$composer_json = json_decode( file_get_contents($file_path),true);

		/* update composer file */
		$array = [
			'autoload'=>[
				'psr-4' => [
					$this->namespace.'\\' => $this->public_folder_name.'/app/themes/'.$this->slug.'/',
				]
			]
		];

		/* Merge */
		$composer_json = array_merge_recursive($array,$composer_json);

		/* Save */
		$compiled = json_encode($composer_json,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
		file_put_contents($file_path,$compiled);
	}

	/**
	 * Update Path to Match Theme
	 */
	protected function modifyConfigFile(){

		$find = '/* %ThemeName% */';
		$replace = sprintf('\le0daniel\System\Helpers\Path::$theme_dirname =  \'%s\';',$this->slug);
		$file_path = $this->root_dir.'/config/application.php';

		$content = file_get_contents($file_path);

		$compiled = str_replace($find,$replace,$content);
		file_put_contents($file_path,$compiled);

	}

	/**
	 * Update Path to Match Theme
	 */
	protected function updateWebpackMix(){

		$find = '/* %ThemeName% */';
		$replace = sprintf("const theme_name = '%s'",$this->slug);
		$file_path = $this->root_dir.'/webpack.mix.js';

		$content = file_get_contents($file_path);

		$compiled = str_replace($find,$replace,$content);
		file_put_contents($file_path,$compiled);

	}

	/**
	 * Modify the functions file!
	 */
	protected function modifyFunctionsFile(){

		$file_path = Path::themesPath('skeletton/functions.php');
		$search_replace = [
			'/* %ThemeName% */'     => sprintf('$theme_name = \'%s\';',$this->slug),
			'/* %NameSpace% */'     => sprintf('namespace %s;',$this->namespace),
			'/* %UseWPExtender% */' => sprintf('use %s\\App\\WordPressExtender;',$this->namespace),
		];

		$content = file_get_contents($file_path);
		$compiled = str_replace(array_keys($search_replace),array_values($search_replace),$content);
		file_put_contents($file_path,$compiled);
	}

	/**
	 * Modify the functions file!
	 */
	protected function modifyExtenderFile(){

		$file_path = Path::themesPath('skeletton/App/WordPressExtender.php');
		$search_replace = [
			'/* %NameSpace% */' => sprintf('namespace %s\\App;',$this->namespace),
		];

		$content = file_get_contents($file_path);
		$compiled = str_replace(array_keys($search_replace),array_values($search_replace),$content);
		file_put_contents($file_path,$compiled);
	}

	/**
	 * Create WordPress style.css file
	 */
	protected function createStyleFile(){

		$content = [
			'/* ',
			' * The only purpose of this file is laying here because Wordpress requires it in every theme!',
			' * ',
			' * Theme Name: '.$this->name,
			' * Description: Describe your shiny new theme!',
			' * Author: ThemeGenerator v1.0 by le0daniel',
			'*/'
		];

		file_put_contents(
			Path::themesPath('skeletton/style.css'),
			implode(PHP_EOL,$content)
		);

	}

}