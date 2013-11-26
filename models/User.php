<?php

namespace auth\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Security;
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

	const EVENT_AFTER_LOGIN = 'afterLogin';

	/**
	 * @var string the raw password. Used to collect password input and isn't saved in database
	 */
	public $password;

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
				'class' => 'yii\behaviors\AutoTimestamp',
				'attributes' => [
					self::EVENT_BEFORE_INSERT => ['create_time', 'update_time'],
					self::EVENT_BEFORE_UPDATE => 'update_time',
					self::EVENT_BEFORE_DELETE => 'delete_time',
					self::EVENT_AFTER_LOGIN => 'last_visit_time',
				],
				'timestamp' => new Expression('CURRENT_TIMESTAMP')
			],
		];
	}

	public function getStatus($status = null)
	{
		if ($status === null) {
			return $this->statuses[$this->status];
		}
		return $this->statuses[$status];
	}

	/**
	 * Finds an identity by the given ID.
	 *
	 * @param string|integer $id the ID to be looked for
	 * @return IdentityInterface|null the identity object that matches the given ID.
	 */
	public static function findIdentity($id)
	{
		return static::find($id);
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return null|User
	 */
	public static function findByUsername($username)
	{
		return static::find(['username' => $username, 'status' => static::STATUS_ACTIVE]);
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
	 * @param string $password password to validate
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword($password)
	{
		return Security::validatePassword($password, $this->password_hash);
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
			['status', 'default', 'value' => static::STATUS_ACTIVE, 'on' => 'signup'],
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'unique', 'message' => 'This email address has already been taken.', 'on' => 'signup'],
			['email', 'exist', 'message' => 'There is no user with such email.', 'on' => 'requestPasswordResetToken'],

			['password', 'required'],
			['password', 'string', 'min' => 6],
		];
	}

	public function scenarios()
	{
		return [
			'signup' => ['username', 'email', 'password'],
			'resetPassword' => ['password'],
			'requestPasswordResetToken' => ['email'],
			'login' => ['last_visit_time'],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'username' => 'Username',
			'email' => 'Email',
			'password_hash' => 'Password Hash',
			'password_reset_token' => 'Password Reset Token',
			'auth_key' => 'Auth Key',
			'status' => 'Status',
			'last_visit_time' => 'Last Visit Time',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'delete_time' => 'Delete Time',
		];
	}

	/**
	 * @return \yii\db\ActiveRelation
	 */
	public function getProfileFieldValue()
	{
		return $this->hasOne(ProfileFieldValue::className(), ['id' => 'user_id']);
	}

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			if (($this->isNewRecord || $this->getScenario() === 'resetPassword') && !empty($this->password)) {
				$this->password_hash = Security::generatePasswordHash($this->password);
			}
			if ($this->isNewRecord) {
				$this->auth_key = Security::generateRandomKey();
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
}
