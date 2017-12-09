<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 09.12.17
 * Time: 10:17
 */

namespace le0daniel\System\ServiceProviders;


use Carbon\Carbon;
use Illuminate\Container\Container;
use le0daniel\System\App;
use le0daniel\System\Contracts\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log implements ServiceProvider {

	public static $name = 'le0daniel\\System';

	/**
	 * Boot
	 */
	public function boot(App $app) {}

	/**
	 * @param Container $container
	 */
	public function register( Container $container ) {

		/* Monolog Logger */
		$container->singleton( Logger::class, function(Container $container):Logger{
			$log = new Logger(self::$name);
			$log->pushHandler(

				$container->make(StreamHandler::class,
					[
						'stream'=>$container->get('system.root_dir').'/storage/log/' . Carbon::now()->toDateString() . '.log'
					]
				)

			);
			return $log;
		});

		/* Setup the Stream Logger */
		$container->resolving(StreamHandler::class, function (StreamHandler $logger) {
			$logger->setLevel(Logger::DEBUG);
			$logger->setFormatter(new LineFormatter(null, null, false, true));
		});

	}

}