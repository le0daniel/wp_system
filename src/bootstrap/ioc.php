<?php

/* Create the IoC Container and Bind the most needed Interfaces */
$container = new Illuminate\Container\Container();

/* Bind container itself */
$container->instance(Illuminate\Container\Container::class, $container);
$container->bind(\Illuminate\Contracts\Container\Container::class,\Illuminate\Container\Container::class);

/**
 * Initial Bindings
 */
$container->singleton( \le0daniel\System\Http\Kernel::class );
$container->singleton( \le0daniel\System\Console\Kernel::class);

/**
 * Return the Instance of the container
 */
return $container;