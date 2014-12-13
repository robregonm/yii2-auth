<?php
namespace auth\models;

use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
	public $email;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'exist',
				'targetClass' => '\auth\models\User',
				'filter' => ['status' => User::STATUS_ACTIVE],
				'message' => Yii::t('auth.reset-password', 'There is no user with such email.')
			],
		];
	}

	/**
	 * Sends an email with a link, for resetting the password.
	 *
	 * @return boolean whether the email was send
	 */
	public function sendEmail()
	{
		/* @var $user User */
		$user = User::findOne([
			'status' => User::STATUS_ACTIVE,
			'email' => $this->email,
		]);

		if ($user) {
			$user->generatePasswordResetToken();
			if ($user->save()) {
				return \Yii::$app->mailer->compose('@auth/views/mail/passwordResetToken', ['user' => $user])
										 ->setFrom([\Yii::$app->getModule('auth')->supportEmail => \Yii::$app->name])
										 ->setTo($this->email)
										 ->setSubject(Yii::t('auth.reset-password', 'Password reset for {name}', ['name' => \Yii::$app->name]))
										 ->send();
			}
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'email' => Yii::t('auth.user', 'Email')
		];
	}
}
