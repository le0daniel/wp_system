<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 01.12.17
 * Time: 19:44
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

class MakePostType extends Command {

	/**
	 * @var string
	 */
	protected $theme_path = '';

	/**
	 * @var array
	 */
	protected $attributes = [
		'name'=>'',
		'singular_name'=>'',
		'prefix'=>'',
		'description'=>'',

		'namespace'=>'',
		'class'=>'',
	];

	/**
	 *
	 */
	protected function configure()
	{
		$this
			->setName('make:posttype')
			->setDescription('Generates a new Post Type')
			->setHelp('This command allows you to generate all files needed to add a new post type!');
	}

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

		/* Set default Prefix */
		$this->attributes['prefix']= '';
		$output->writeln('Default prefix > <info>'.$this->attributes['prefix'].'</info>');

		/* Check if app was found */
		if( ! file_exists($this->theme_path .'/App/WordPressExtender.php') ){
			throw new RuntimeException('WordPressExtender.php not found in ~App/'.PHP_EOL.'Are you sure this theme is using le0daniel/wp_system?');
		}

		/* Check if dir exists */
		if( ! file_exists($this->theme_path.'/App/PostTypes') ){
			mkdir($this->theme_path.'/App/PostTypes',0777,true);
		}

		$question = new Question('Enter the name of your post type [<info>Human readable, plural</info>]: ', null);
		$question->setValidator([$this,'stringValidator']);
		$question->setNormalizer([$this,'stringNormalizer']);
		$question->setMaxAttempts(5);

		$this->attributes['name'] = $helper->ask($input, $output, $question);

		$question = new Question('Enter the singular form of <info>'.$this->attributes['name'].'</info>: ', $this->attributes['name']);
		$question->setValidator([$this,'stringValidator']);
		$question->setNormalizer([$this,'stringNormalizer']);
		$question->setMaxAttempts(5);

		$this->attributes['singular_name'] = $helper->ask($input, $output, $question);

		$question = new Question('Enter the prefix [<info>'.$this->attributes['prefix'].'</info>]: ', $this->attributes['prefix']);
		$question->setValidator([$this,'stringValidator']);
		$question->setNormalizer([$this,'stringNormalizer']);
		$question->setMaxAttempts(5);

		$this->attributes['prefix'] = $helper->ask($input, $output, $question);

		$question = new Question('Enter the description [<info>optional</info>]: ', '');
		$question->setNormalizer([$this,'stringNormalizer']);
		$question->setMaxAttempts(5);

		$this->attributes['description'] = $helper->ask($input, $output, $question);

		/* Generated Attributes */
		$this->attributes['namespace'] = 'Themes\\'.ucfirst(camel_case($theme)).'\\App\\PostTypes';
		$this->attributes['class']= ucfirst( camel_case( $this->attributes['name'] ) );
		$this->attributes['slug']= snake_case($this->attributes['name']);

		$this->generateControllerClass();
	}

	/**
	 * @return string
	 */
	protected function getControllerSavePath():string{
		return $this->theme_path.'/App/PostTypes/'.$this->attributes['class'].'.php';
	}

	/**
	 *
	 */
	protected function generateControllerClass(){
		/* Namespace Usages */
		$uses = [
			'le0daniel\\System\\WordPress\\PostType',
		];
		$content = [
			'/* Prefix */',
			sprintf('protected $prefix = \'%s\';',$this->attributes['prefix']),
			'',
			sprintf('protected $name = \'%s\';',$this->attributes['name']),
			sprintf('protected $singular_name = \'%s\';',$this->attributes['singular_name']),
			sprintf('protected $slug = \'%s\';',$this->attributes['slug']),
			sprintf('protected $description = \'%s\';',$this->attributes['description']),
		];

		$header = File::generatePhpFileHeader(
			$this->attributes['namespace'],
			$uses
		);

		$class = File::generatePhpClass(
			$this->attributes['class'],
			$content,
			'PostType',
			[]
		);

		$compiled = File::generateFromArray(
			array_merge($header,$class)
		);

		file_put_contents($this->getControllerSavePath(),$compiled);

		return;
	}

	/**
	 * @param $answer
	 *
	 * @return mixed
	 */
	public function stringValidator($answer){
		if (! is_string($answer) ) {
			throw new \RuntimeException('Enter a string of minimum lenght 5');
		}
		if( strlen($answer) < 5 ){
			throw new \RuntimeException('The name must be longer than 5');
		}

		return $answer;
	}

	/**
	 * @param $string
	 *
	 * @return string
	 */
	public function stringNormalizer($string){
		if( is_string($string) ){
			return rtrim( trim( $string ) );
		}

		return $string;
	}

}