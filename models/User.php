<?php

namespace auth\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "User".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $auth_key
 * @property integer $status
 * @property string $last_visit_time
 * @property string $create_time
 * @property string $update_time
 * @property string $delete_time
 *
 * @property ProfileFieldValue $profileFieldValue
 */
class User extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
	const STATUS_INACTIVE = 1;
	const STATUS_ACTIVE = 2;
	const STATUS_SUSPENDED = 3;

	private $_isSuperAdmin = null;

	private $statuses = [
		self::STATUS_DELETED => 'Deleted',
		self::STATUS_INACTIVE => 'Inactive',
		self::STATUS_ACTIVE => 'Active',
		self::STATUS_SUSPENDED => 'Suspended',
	];

	public function behaviors()
	{
		return [
			'timestamp' => [
				'class' => 'yii\behaviors\TimestampBehavior',
				'attributes' => [
					self::EVENT_BEFORE_INSERT => ['create_time', 'update_time'],
					self::EVENT_BEFORE_DELETE => 'delete_time',
				],
				'value' => function () {
					return new Expression('CURRENT_TIMESTAMP');
				}
			],
		];
	}

	public function getStatus($status = null)
	{
		if ($status === null) {
			return Yii::t('auth.user', $this->statuses[$this->status]);
		}
		return Yii::t('auth.user', $this->statuses[$status]);
	}

	/**
	 * Finds an identity by the given ID.
	 *
	 * @param string|integer $id the ID to be looked for
	 * @return IdentityInterface|null the identity object that matches the given ID.
	 */
	public static function findIdentity($id)
	{
		return static::findOne($id);
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return null|User
	 */
	public static function findByUsername($username)
	{
		return static::find()
					 ->andWhere(['and', ['or', ['username' => $username], ['email' => $username]], ['status' => static::STATUS_ACTIVE]])
					 ->one();
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		throw new \yii\base\NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token)
	{
		$expire = Yii::$app->getModule('auth')->passwordResetTokenExpire;
		$parts = explode('_', $token);
		$timestamp = (int)end($parts);
		if ($timestamp + $expire < time()) {
			// token expired
			return null;
		}

		return static::findOne([
			'password_reset_token' => $token,
			'status' => self::STATUS_ACTIVE,
		]);
	}

	/**
	 * @return int|string current user ID
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return string current user auth key
	 */
	public function getAuthKey()
	{
		return $this->auth_key;
	}

	/**
	 * @param string $authKey
	 * @return boolean if auth key is valid for current user
	 */
	public function validateAuthKey($authKey)
	{
		return $this->auth_key === $authKey;
	}

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return Yii::$app->getModule('auth')->tableMap['User'];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['status', 'default', 'value' => self::STATUS_ACTIVE],
            [
                'status',
                'in',
                [self::STATUS_ACTIVE, self::STATUS_DELETED, self::STATUS_INACTIVE, self::STATUS_SUSPENDED]
            ],
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['username', 'unique', 'message' => Yii::t('auth.user', 'This username has already been taken.')],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'unique', 'message' => Yii::t('auth.user', 'This email address has already been taken.')],
			['email', 'exist', 'message' => Yii::t('auth.user', 'There is no user with such email.'), 'on' => 'requestPasswordResetToken'],

			['password', 'string', 'min' => 6],
		];
	}

	public function scenarios()
	{
		return [
			'profile' => ['username', 'email', 'password'],
			'resetPassword' => ['password'],
			'requestPasswordResetToken' => ['email'],
			'login' => ['last_visit_time'],
		] + parent::scenarios();
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'username' => Yii::t('auth.user', 'Username'),
			'email' => Yii::t('auth.user', 'Email'),
			'password' => Yii::t('auth.user', 'Password'),
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

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getProfileFieldValue()
	{
		return $this->hasOne(ProfileFieldValue::className(), ['id' => 'user_id']);
	}

	public function beforeValidate()
	{
		if (parent::beforeValidate()) {

			return true;
		}

		return false;
	}

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			if (($this->isNewRecord || in_array($this->getScenario(), ['resetPassword', 'profile'])) && !empty($this->password)) {
				$this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($this->password);
			}
			if ($this->isNewRecord) {
				$this->auth_key = Yii::$app->getSecurity()->generateRandomString();
			}
			if ($this->getScenario() !== \yii\web\User::EVENT_AFTER_LOGIN) {
				$this->setAttribute('update_time', new Expression('CURRENT_TIMESTAMP'));
			}

			return true;
		}
		return false;
	}

	public function delete()
	{
		$db = static::getDb();
		$transaction = $this->isTransactional(self::OP_DELETE) && $db->getTransaction() === null ? $db->beginTransaction() : null;
		try {
			$result = false;
			if ($this->beforeDelete()) {
				$this->setAttribute('status', static::STATUS_DELETED);
				$this->save(false);
			}
			if ($transaction !== null) {
				if ($result === false) {
					$transaction->rollback();
				} else {
					$transaction->commit();
				}
			}
		} catch (\Exception $e) {
			if ($transaction !== null) {
				$transaction->rollback();
			}
			throw $e;
		}
		return $result;
	}

	/**
	 * Returns whether the logged in user is an administrator.
	 *
	 * @return boolean the result.
	 */
	public function getIsSuperAdmin()
	{
		if ($this->_isSuperAdmin !== null) {
			return $this->_isSuperAdmin;
		}

		$this->_isSuperAdmin = in_array($this->username, Yii::$app->getModule('auth')->superAdmins);
		return $this->_isSuperAdmin;
	}

	public function login($duration = 0)
	{
		return Yii::$app->user->login($this, $duration);
	}

	/**
	 * Generates new password reset token
	 */
	public function generatePasswordResetToken()
	{
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken()
	{
		$this->password_reset_token = null;
	}
}
