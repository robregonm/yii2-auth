<?php
/**
 *
 * @author Ricardo Obregón <ricardo@obregon.co>
 * @created 28/11/13 11:27 AM
 */

namespace auth\controllers;


use Yii;
use yii\web\Controller;
use yii\helpers\Security;
use auth\models\User;
use yii\web\NotFoundHttpException;

class ProfileController extends Controller
{
	/**
	 * @var string the ID of the action that is used when the action ID is not specified
	 * in the request. Defaults to 'index'.
	 */
	public $defaultAction = 'view';

	public function init()
	{
		$layout = $this->module->layoutLogged;
		if (!empty($layout)) {
			$this->layout = $layout;
		}
	}

	/**
	 * @var \auth\Module
	 */
	public $module;

	public function behaviors()
	{
		return [
			'access' => [
				'class' => \yii\filters\AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}

	/**
	 * Displays current User model.
	 *
	 * @return mixed
	 */
	public function actionView()
	{
		return $this->render('view', [
			'model' => $this->findModel(),
		]);
	}

	/**
	 * Updates the current User model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate()
	{
		$model = $this->findModel();
		$model->setScenario('profile');

		if ($model->load($_POST) && $model->save()) {
			return $this->redirect(['view', 'id' => $model->id]);
		} else {
			return $this->render('update', [
				'model' => $model,
			]);
		}
	}

	/**
	 * Finds the logged in User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 *
	 * @return User the loaded model
	 * @throws HttpException if the model cannot be found
	 */
	protected function findModel()
	{
		if (($model = Yii::$app->user->getIdentity()) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}

} 