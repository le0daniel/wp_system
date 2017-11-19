<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 18:01
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

/**
 * Class MakeShortCut
 * @package le0daniel\System\Console\Commands
 */
class MakeShortCut extends Command
{

	/**
	 * @var array
	 */
	protected $shortcut = [
		'name'=>null,
		'slug'=>null,
		'class'=>null,
		'namespace'=>null,
	];
	/**
	 * @var String
	 */
	protected $theme_path;

	/**
	 * Is it a visual composer component
	 *
	 * @var bool
	 */
	protected $is_vc_component = false;

	/**
	 * List of variables to be set by the controller
	 * for a VC component
	 *
	 * @var array
	 */
	protected $vc_required_variables = [
		'category'      =>'%String%',
		'description'   =>'%String%',
		'group'         =>'%String%',
		'icon'          =>'%String%',
		'weight'        =>'%Int%',
	];

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

		/* Ask for the theme */
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

		/* Get the path to the theme */
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
		$question->setMaxAttempts(5);

		/* Safe data */
		$this->shortcut['name'] = $helper->ask($input, $output, $question);
		$this->shortcut['slug'] = snake_case($this->shortcut['name']);
		$this->shortcut['class']= ucfirst(camel_case($this->shortcut['name']));
		$this->shortcut['namespace']='Themes\\'.ucfirst(camel_case($theme)).'\\App\\ShortCodes';

		/* Confirm Slug */
		$question = new Question('Enter the slug (alphanum, lowercase and `_`) [<info>'.$this->shortcut['slug'].'</info>]: ', $this->shortcut['slug']);
		$question->setValidator(function ($answer) {
			if (! is_string($answer) || ! preg_match('/^[a-z][a-z0-9_]+[a-z0-9]$/',$answer)) {
				throw new \RuntimeException('The slug can only contain lowercase letters and numbers and underscores'.PHP_EOL.'Full Regex: ^[a-z][a-z0-9_]+[a-z0-9]$');
			}
			if( strlen($answer) < 5 ){
				throw new \RuntimeException('The name must be longer than 5');
			}

			return $answer;
		});
		$question->setMaxAttempts(5);
		$this->shortcut['slug'] = $helper->ask($input, $output, $question);

		/* Ask for Visual Composer */
		$question = new ConfirmationQuestion('Is this a visual composer component? [<info>y/n</info>]: ', false);
		$this->is_vc_component = $helper->ask($input, $output, $question);

		/* Show output to user */
		$output->writeln('Shortcurt name:  <info>'.$this->shortcut['name'].'</info>');
		$output->writeln('Shortcurt slug:  <info>'.$this->shortcut['slug'].'</info>');
		$output->writeln('Shortcurt class: <info>'.$this->shortcut['class'].'</info>');
		$output->writeln('In namespace:  : <info>'.$this->shortcut['namespace'].'</info>');

		/* Generate The Class */
		$this->generateControllerClass();

		/* Generate the twig template */
		$this->generateTwigTemplate();

		/* Show Output */
		$output->writeln('<info>Successfully generated</info>');
		$output->writeln('<info>Don\'t forget to register the Class in App\\WordPressExtender</info>');

		return;

	}

	/**
	 * @return string
	 */
	protected function getControllerSavePath():string{
		return $this->theme_path.'/App/ShortCodes/'.$this->shortcut['class'].'.php';
	}

	/**
	 * @return string
	 */
	protected function getTwigTemplateSavePath():string{
		return $this->theme_path.'/resources/views/shortcodes/'.$this->shortcut['slug'].'.twig';
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
	 * @return void
	 */
	protected function generateControllerClass(){

		/* Namespace Usages */
		$uses = [
			'le0daniel\\System\\WordPress\\ShortCode',
			'le0daniel\\System\\Contracts\\VisualComposerComponent',
			'le0daniel\\System\\Traits\\isVisualComposerComponent',
			'le0daniel\\System\\WordPress\\VisualComposer\\ParameterHelper'
		];

		/* Implemented $interfaces and traits */
		$interfaces = [];
		$traits = [];
		$content = [
			PHP_EOL,
			sprintf('protected $name = \'%s\';',$this->shortcut['name']),
			sprintf('protected $slug = \'%s\';',$this->shortcut['slug']),
		];
		if($this->is_vc_component){
			$interfaces[] = 'VisualComposerComponent';
			$traits[] = 'isVisualComposerComponent';

			$content[] = '';
			$content = array_merge($content,File::generateCommentBlockArray([
				'Visual Composer Specific Settings',
				'',
				'Category, Description and Name are translated by default',
			]));


			$parameter_lenght = max(array_map('strlen', $this->vc_required_variables));
			/* Show all required Variables */
			foreach($this->vc_required_variables as $name=>$value){
				$content[] = sprintf('protected $%s = "%s";',str_pad($name,($parameter_lenght + 3),' '),$value);
			}

			$content[] = PHP_EOL;
			$content = array_merge($content,File::generateCommentBlockArray([
				'Add parameters to your component in a fluid way',
				'using the Parameter builder'.PHP_EOL,
				'Important, this configuration is cached if',
				'WP_DEBUG is false. Clear the cache after a',
				'change! ',
				'user$ php console clear:cache:vc',
				'',
				'@param ParameterHelper $param',
				'',
				'@return void'
			]));
			$content = array_merge($content,File::generatePhpMethod(
				'createVisualComposerParams',
				'public',
				[
					'ParameterHelper $param'
				],
				false,
				File::generateCommentBlockArray([
					'Usage: $param->add%Type%(name) returns a Parameter object',
					'',
					'If your parameter option is not available by default use',
					'the set method: set(string key,value)',
					'',
					'Important: addHeading, addDescription are required!',
					'',
					'Translation is on by default for heading and description',
					'Disable using disableAutotranslate()'
				])
			));
		}

		$header = File::generatePhpFileHeader(
			$this->shortcut['namespace'],
			$uses
		);

		$class = File::generatePhpClass(
			$this->shortcut['class'],
			$content,
			'ShortCode',
			$interfaces,
			$traits
		);

		$compiled = File::generateFromArray(
			array_merge($header,$class)
		);

		file_put_contents($this->getControllerSavePath(),$compiled);

		return;
	}

	/**
	 * Generate the twig template
	 */
	protected function generateTwigTemplate() {

		$now = Carbon::now();

		$lines = [
			'{# ',
			' # This is the Template file for the Shortcode '.$this->shortcut['name'],
			' # ',
			' # The identifiere is: '.$this->shortcut['slug'],
			' # The context is disabled by default, if you want to change that,',
			' # you need to edit it\'s Controller ('.$this->shortcut['class'].')',
			' # which can be found in ~App/ShortCodes',
			' # ',
			' # Date: '.$now->toDateString(),
			' # Time: '.$now->toTimeString(),
			' #}',
		];

		$compiled = implode(PHP_EOL,$lines);
		file_put_contents($this->getTwigTemplateSavePath(),$compiled);
	}

}