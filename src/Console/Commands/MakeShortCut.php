<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 18:01
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

/**
 * Class MakeShortCut
 * @package le0daniel\System\Console\Commands
 */
class MakeShortCut extends Command
{

	/**
	 * @var String
	 */
	protected $theme_path;

	/**
	 *
	 */
	protected function configure()
	{
		$this
			->setName('make:shortcut')
			->setDescription('Generates a new Wordpress shortcut with all needed files')
			->setHelp('This command allows you to generate all files needed to add a new shortcut!');
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return null
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$helper = $this->getHelper('question');
		$theme = '';

		if(count(Path::getAvailableThemes()) === 1 ){
			$theme = Path::getAvailableThemes()[0];
		}
		else{
			$question = new ChoiceQuestion(
				'For which theme should the Shortcut be generated?',
				Path::getAvailableThemes()
			);
			$question->setErrorMessage('Theme [%s] is invalid.');
			$theme = $helper->ask($input, $output, $question);
		}

		$this->theme_path = Path::themesPath($theme);

		$output->writeln('Creating shortcut for Theme <info>'.ucfirst($theme).'</info>');
		$output->writeln('Path > <info>'.$this->theme_path.'</info>');

		/* Check if app was found */
		if( ! file_exists($this->theme_path .'/App/WordPressExtender.php') ){
			throw new RuntimeException('WordPressExtender.php not found in ~App/'.PHP_EOL.'Are you sure this theme is using le0daniel/wp_system?');
		}

		/* Check and make dir */
		$this->checkAndMakeDir();

		/**
		 * Get the name
		 */
		$question = new Question('Enter the name of your shortcut [<info>Human readable: My Awesome Shortcut</info>]: ', null);
		$question->setValidator(function ($answer) {
			if (! is_string($answer) || ! preg_match('/^[a-zA-Z][a-zA-Z0-9\s]+$/',$answer)) {
				throw new \RuntimeException('The name can only contain alphanumeric Characters and whitespaces!');
			}
			if( strlen($answer) < 5 ){
				throw new \RuntimeException('The name must be longer than 5');
			}

			return $answer;
		});
		$question->setMaxAttempts(4);

		$name = $helper->ask($input, $output, $question);
		$slug = snake_case($name);
		$class= ucfirst(camel_case($name));
		$namespace = 'Themes\\'.ucfirst($theme).'\\App\\ShortCodes';

		/* Ask if Slug is okey */

		$question = new Question('Enter the slug (alphanum, lowercase and `_`) [<info>'.$slug.'</info>]: ', $slug);
		$question->setValidator(function ($answer) {
			if (! is_string($answer) || ! preg_match('/^[a-z][a-z0-9_]+[a-z0-9]$/',$answer)) {
				throw new \RuntimeException('The slug can only contain lowercase letters and numbers and underscores'.PHP_EOL.'Full Regex: ^[a-z][a-z0-9_]+[a-z0-9]$');
			}
			if( strlen($answer) < 5 ){
				throw new \RuntimeException('The name must be longer than 5');
			}

			return $answer;
		});
		$question->setMaxAttempts(4);
		$slug = $helper->ask($input, $output, $question);

		/*  */
		$output->writeln('Shortcurt name:  <info>'.$name.'</info>');
		$output->writeln('Shortcurt slug:  <info>'.$slug.'</info>');
		$output->writeln('Shortcurt class: <info>'.$class.'</info>');

		/* Generate and Save Class */
		$generated_class = $this->generateClass($class,$namespace,$name,$slug);
		file_put_contents($this->theme_path.'/App/ShortCodes/'.$class.'.php',$generated_class);

		/* Create Twig Template */
		$generated_twig_template = $this->generateTwigTemplate($name,$slug,$class);
		file_put_contents($this->theme_path.'/resources/views/shortcodes/'.$slug.'.twig',$generated_twig_template);

		$output->writeln('<info>Successfully generated</info>');
		$output->writeln('<info>Don\'t forget to register the Class in App\\WordPressExtender</info>');

		return;
	}

	/**
	 * Create ShortCodes Dir
	 */
	protected function checkAndMakeDir(){
		if( ! file_exists($this->theme_path.'/App/ShortCodes') ){
			mkdir($this->theme_path.'/App/ShortCodes',0777,true);
		}
		if( ! file_exists($this->theme_path.'/resources/views/shortcodes') ){
			mkdir($this->theme_path.'/resources/views/shortcodes/',0777,true);
		}
	}

	/**
	 * @param string $class_name
	 * @param string $namespace
	 * @param string $name
	 * @param string $slug
	 *
	 * @return array|string
	 */
	protected function generateClass(string $class_name,string $namespace,string $name,string $slug){

		$lines = [

			'<?php',
			'/* Created by le0daniel/wp_system */',
			'',
			sprintf('namespace %s;',$namespace),
			'',
			'use le0daniel\\System\\WordPress\\ShortCode;',
			'',
			'',
			sprintf('class %s extends ShortCode {',$class_name),

			[
				'',
				sprintf('protected $name = \'%s\';',$name),
				sprintf('protected $slug = \'%s\';',$slug),
				''
			],

			'}'

		];

		return $this->generateFromArray($lines);
	}

	/**
	 * @param string $name
	 * @param string $slug
	 * @param string $class_name
	 *
	 * @return string
	 */
	protected function generateTwigTemplate(string $name,string $slug,string $class_name):string {

		$lines = [
			'{# ',
			' # This is the Template file for the Shortcode '.$name,
			' # ',
			' # The identifiere is: '.$slug,
			' # The context is disabled by default, if you want to change that,',
			' # you need to edit it\'s Controller ('.$class_name.')',
			' # which can be found in ~App/ShortCodes',
			' # ',
			' # Generated on '.Carbon::now()->toDateTimeString(),
			' #}',
		];

		return implode(PHP_EOL,$lines);
	}

	/**
	 * @param array $array
	 * @param int $count
	 *
	 * @return string
	 */
	protected function generateFromArray(array $array,int $count = 0):string{
		$add = 4;
		$return = [];

		foreach($array as $line){
			if (is_array($line)){
				$return[] = $this->generateFromArray($line, ($count + $add) );
			}
			else{
				$return[] = str_repeat(' ',$count).$line;
			}
		}

		return implode(PHP_EOL,$return);
	}

}