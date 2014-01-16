<?php
/**
 *
 * @author Ricardo Obregón <ricardo@obregon.co>
 * @created 24/11/13 07:40 PM
 */

namespace auth\components;

use yii\web\User as BaseUser;
use yii\db\Expression;

/**
 * User is the class for the "user" application component that manages the user authentication status.
 *
 * @property \auth\models\User $identity The identity object associated with the currently logged user. Null
 * is returned if the user is not logged in (not authenticated).
 *
 * @author Ricardo Obregón <robregonm@gmail.com>
 */
class User extends BaseUser
{
	/**
	 * @inheritdoc
	 */
	public $identityClass = '\auth\models\User';

	/**
	 * @inheritdoc
	 */
	public $enableAutoLogin = true;

	/**
	 * @inheritdoc
	 */
	public $loginUrl = ['/auth/default/login'];

	/**
	 * @inheritdoc
	 */
	protected function afterLogin($identity, $cookieBased)
	{
		parent::afterLogin($identity, $cookieBased);
		$this->identity->setScenario(self::EVENT_AFTER_LOGIN);
		$this->identity->setAttribute('last_visit_time', new Expression('CURRENT_TIMESTAMP'));
		// $this->identity->setAttribute('login_ip', ip2long(\Yii::$app->getRequest()->getUserIP()));
		$this->identity->save(false);
	}

	public function getIsSuperAdmin()
	{
		if ($this->isGuest) {
			return false;
		}
		return $this->identity->getIsSuperAdmin();
	}

	public function checkAccess($operation, $params = [], $allowCaching = true)
	{
		// Always return true when SuperAdmin user
		if ($this->getIsSuperAdmin()) {
			return true;
		}
		return parent::checkAccess($operation, $params, $allowCaching);
	}
}