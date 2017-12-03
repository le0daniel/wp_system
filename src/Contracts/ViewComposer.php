<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 07.11.17
 * Time: 14:28
 */

namespace le0daniel\System\Contracts;


interface ViewComposer {

	public function render(string $filename,array $data):string;

}