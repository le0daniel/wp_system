<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:22
 */

namespace le0daniel\System\WordPress;

/* Use Timber as Base */
use Timber\Site as TimberSite;

class Site extends TimberSite {

	public function __construct() {

		/* Cunstruct the Parent */
		parent::__construct();
	}

}