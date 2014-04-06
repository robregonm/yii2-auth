<?php

namespace auth\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\helpers\Security;
use auth\models\LoginForm;
use auth\models\User;

class DefaultController extends Controller
{
	/**
	 * @var \auth\Module
	 */
	public $module;

	private $loginAttemptsVar = '__LoginAttemptsCount';

	public function behaviors()
	{
		return [
			'access' => [
				'class' => \yii\filters\AccessControl::className(),
				'only' => ['logout', 'signup'],
				'rules' => [
					[
						'actions' => ['signup'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
		];
	}

	public function actionLogin()
	{
		if (!\Yii::$app->user->isGuest) {
			$this->goHome();
		}

		$model = new LoginForm();

		//make the captcha required if the unsuccessful attempts are more of thee
		if ($this->getLoginAttempts() >= $this->module->attemptsBeforeCaptcha) {
			$model->scenario = 'withCaptcha';
		}

		if ($model->load($_POST) and $model->login()) {
			$this->setLoginAttempts(0); //if login is successful, reset the attempts
			return $this->goBack();
		}
		//if login is not successful, increase the attempts
		$this->setLoginAttempts($this->getLoginAttempts() + 1);

		return $this->render('login', [
			'model' => $model,
		]);
	}

	private function getLoginAttempts()
	{
		return Yii::$app->getSession()->get($this->loginAttemptsVar, 0);
	}

	private function setLoginAttempts($value)
	{
		Yii::$app->getSession()->set($this->loginAttemptsVar, $value);
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
	}

	public function actionSignup()
	{
		$model = new User();
		$model->setScenario('signup');
		if ($model->load($_POST) && $model->save()) {
			if (Yii::$app->getUser()->login($model)) {
				return $this->goHome();
			}
		}

		return $this->render('signup', [
			'model' => $model,
		]);
	}

	public function actionRequestPasswordReset()
	{
		$model = new User();
		$model->scenario = 'requestPasswordResetToken';
		if ($model->load($_POST) && $model->validate()) {
			if ($this->sendPasswordResetEmail($model->email)) {
				Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');
				return $this->goHome();
			} else {
				Yii::$app->getSession()->setFlash('error', 'There was an error sending email.');
			}
		}
		return $this->render('requestPasswordResetToken', [
			'model' => $model,
		]);
	}

	public function actionResetPassword($token)
	{
		$model = User::find([
			'password_reset_token' => $token,
			'status' => User::STATUS_ACTIVE,
		]);

		if (!$model) {
			throw new BadRequestHttpException('Wrong password reset token.');
		}

		$model->scenario = 'resetPassword';
		if ($model->load($_POST) && $model->save()) {
			Yii::$app->getSession()->setFlash('success', 'New password was saved.');
			return $this->goHome();
		}

		return $this->render('resetPassword', [
			'model' => $model,
		]);
	}

	private function sendPasswordResetEmail($email)
	{
		$user = User::find([
			'status' => User::STATUS_ACTIVE,
			'email' => $email,
		]);

		if (!$user) {
			return false;
		}

		$user->password_reset_token = Security::generateRandomKey();
		if ($user->save(false)) {
			// todo: refactor it with mail component. pay attention to the arrangement of mail view files
			$fromEmail = \Yii::$app->params['supportEmail'];
			$name = '=?UTF-8?B?' . base64_encode(\Yii::$app->name . ' robot') . '?=';
			$subject = '=?UTF-8?B?' . base64_encode('Password reset for ' . \Yii::$app->name) . '?=';
			$body = $this->renderPartial('/emails/passwordResetToken', [
				'user' => $user,
			]);
			$headers = "From: $name <{$fromEmail}>\r\n" .
				"MIME-Version: 1.0\r\n" .
				"Content-type: text/plain; charset=UTF-8";
			return mail($email, $subject, $body, $headers);
		}

		return false;
	}
}
