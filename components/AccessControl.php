<?php
/**
 * Auth AccessControl class file.
 * Behavior that automatically checks if the user has access to the current controller action.
 *
 * @author Ricardo Obregón <ricardo@obregon.co>
 * @copyright Copyright &copy; Ricardo Obregón 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package auth.components
 */

namespace auth\components;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;

/**
 * Auth AccessControl provides RBAC access control.
 *
 * Auth AccessControl is an action filter. It will check the item names to find
 * the first match that will dictate whether to allow or deny the access to the requested controller
 * action. If no matches, the access will be denied.
 *
 * To use Auth AccessControl, declare it in the `behaviors()` method of your controller class.
 * For example, the following declaration will enable rbac filtering in your controller.
 *
 * ~~~
 * public function behaviors()
 * {
 *     return [
 *         'access' => [
 *             'class' => \auth\AccessControl::className(),
 *         ],
 *     ];
 * }
 * ~~~
 *
 * @author Ricardo Obregón <robregonm@gmail.com>
 * @since 2.0
 */
class AccessControl extends ActionFilter
{
	/**
	 * @var array name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user.
	 */
	public $params = [];

	/**
	 * @var callback a callback that will be called if the access should be denied
	 * to the current user. If not set, [[denyAccess()]] will be called.
	 *
	 * The signature of the callback should be as follows:
	 *
	 * ~~~
	 * function ($item, $action)
	 * ~~~
	 *
	 * where `$item` is this item name, and `$action` is the current [[Action|action]] object.
	 */
	public $denyCallback;

	private $separator = '.';

	private function getItemName($component)
	{
		return strtr($component->getUniqueId(), '/', $this->separator);
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 *
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		$user = Yii::$app->getUser();

		$controller = $action->controller;

		if ($controller->module !== null) {
			if ($user->checkAccess($this->getItemName($controller->module) . $this->separator . '*', $this->params)) {
				return true;
			}
		}

		if ($user->checkAccess($this->getItemName($controller) . $this->separator . '*', $this->params)) {
			return true;
		}

		if ($user->checkAccess($itemName = $this->getItemName($action), $this->params)) {
			return true;
		}

		if (isset($this->denyCallback)) {
			call_user_func($this->denyCallback, $itemName, $action);
		} else {
			$this->denyAccess($user);
		}
		return false;
	}

	/**
	 * Denies the access of the user.
	 * The default implementation will redirect the user to the login page if he is a guest;
	 * if the user is already logged, a 403 HTTP exception will be thrown.
	 *
	 * @param User $user the current user
	 * @throws HttpException if the user is already logged in.
	 */
	protected function denyAccess($user)
	{
		if ($user->getIsGuest()) {
			$user->loginRequired();
		} else {
			throw new \yii\web\ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
		}
	}

}
