<?php
/**
 * Created by PhpStorm.
 * User: robregonm
 * Date: 18/05/15
 * Time: 07:51 PM
 */

namespace auth\models;

use yii\base\Model;
use Yii;

class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            [
                'username',
                'unique',
                'targetClass' => '\auth\models\User',
                'message' => Yii::t('auth.user', 'This username has already been taken.')
            ],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [
                'email',
                'unique',
                'targetClass' => '\auth\models\User',
                'message' => Yii::t('auth.user', 'This email address has already been taken.')
            ],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            if (Yii::$app->getModule('auth')->signupWithEmailOnly) {
                $this->username = $this->email;
            }

            return true;
        }

        return false;
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new User();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }

    public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'username' => Yii::t('auth.user', 'Username'),
			'email' => Yii::t('auth.user', 'Email'),
			'password_hash' => Yii::t('auth.user', 'Password Hash'),
			'password_reset_token' => Yii::t('auth.user', 'Password Reset Token'),
			'auth_key' => Yii::t('auth.user', 'Auth Key'),
			'status' => Yii::t('auth.user', 'Status'),
			'last_visit_time' => Yii::t('auth.user', 'Last Visit Time'),
			'create_time' => Yii::t('auth.user', 'Create Time'),
			'update_time' => Yii::t('auth.user', 'Update Time'),
			'delete_time' => Yii::t('auth.user', 'Delete Time'),
		];
	}
}
