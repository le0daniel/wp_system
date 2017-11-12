<?php
/**
 * Register the Global App Helper Function
 * The app is lazy loaded, as long as app() wasn't called, nothing will happen
 *
 * @param mixed|null $abstract
 * @param array $params
 *
 * @return \le0daniel\System\App
 */
function app():\le0daniel\System\App{
	return \le0daniel\System\App::getInstance();
}