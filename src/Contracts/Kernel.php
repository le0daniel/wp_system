<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:57
 */

namespace le0daniel\System\Contracts;


use Illuminate\Container\Container;
use le0daniel\System\App;

interface Kernel {

	/* Construct */
	public function __construct(App $container);

	/* Boot */
	public function boot();

	/* Run */
	public function run();
}