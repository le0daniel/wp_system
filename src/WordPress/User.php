<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:49
 */

namespace le0daniel\System\WordPress;

use le0daniel\System\Traits\isGettable;

/**
 * Adds an abstraction layer between the wordpress WP_User and the user available in twig!
 *
 * Class User
 * @package le0daniel\System\WordPress
 *
 * Accessible Properties
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $username
 */
class User {
	use isGettable;

	/**
	 * @var \WP_User
	 */
	protected $user;

	/**
	 * User Attributes
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * User logged in
	 *
	 * @var bool
	 */
	protected $is_logged_in = false;

	/**
	 * User constructor.
	 */
	public function __construct(int $user_id = 0) {

		if($user_id === 0){
			/** @var \WP_User $user */
			$user = wp_get_current_user();
		}
		else{
			/** @var \WP_User $user */
			$user = get_userdata($user_id);
		}

		/**
		 * Set User
		 */
		$this->user = $user;

		/* Check if user is logged in! */
		if( $user->exists() ){
			$this->is_logged_in = $this->getLoggedInStatus();
			$this->setAttributes();
		}

	}

	/**
	 * Checks if the User casted user is
	 * really logged in!
	 */
	protected function getLoggedInStatus(){
		return ( $this->user->ID === wp_get_current_user()->ID );
	}

	/**
	 * Sets the available Attributes
	 * Limits the template display capabilities!
	 */
	protected function setAttributes(){
		$this->attributes = [
			'id'=>$this->user->ID,
			'first_name'=>$this->user->first_name,
			'last_name'=>$this->user->last_name,

			'username'=>$this->user->user_login,
		];
	}

	/**
	 * @return bool
	 */
	public function loggedIn(): bool {
		return $this->is_logged_in;
	}

	/**
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public function can(string $attribute): bool{
		return current_user_can($attribute);
	}

	/**
	 * @param string $role
	 *
	 * @return bool
	 */
	public function is(string $role):bool{

		if ( in_array( $role, (array) $this->user->roles ) ) {
			return true;
		}

		return false;
	}

}