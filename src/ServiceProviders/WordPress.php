<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 27.12.17
 * Time: 13:03
 */

namespace le0daniel\System\ServiceProviders;


use le0daniel\System\RootServiceProvider;
use le0daniel\System\WordPress\Context;
use le0daniel\System\WordPress\MetaField;
use le0daniel\System\WordPress\Page;
use le0daniel\System\WordPress\Post;
use le0daniel\System\WordPress\Site;
use le0daniel\System\WordPress\User;
use le0daniel\System\Contracts\ShortCode;
use le0daniel\System\Contracts\AddLogicToWordpress;

class WordPress extends RootServiceProvider {

	/**
	 * @return void
	 */
	public function boot() {

	}

	/**
	 * Register all wordpress aliases and bindings
	 *
	 * @return void
	 */
	public function register() {
		/* WP Aliases */
		$this->app->alias(Context::class,             'wp.context');
		$this->app->alias(MetaField::class,           'wp.metafield');
		$this->app->alias(Page::class,                'wp.page');
		$this->app->alias(Post::class,                'wp.post');
		$this->app->alias(ShortCode::class,           'wp.shortcode');
		$this->app->alias(Site::class,                'wp.site');
		$this->app->alias(User::class,                'wp.user');
		$this->app->alias(AddLogicToWordpress::class, 'wp.extend');
	}
}