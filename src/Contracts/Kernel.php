<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:57
 */

namespace le0daniel\System\Contracts;


use Illuminate\Container\Container;

interface Kernel {

	/* Construct */
	public function __construct(Container $container);

	/* Boot */
	public function boot();

	/* Run */
	public function run();
}