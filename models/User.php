<?php

namespace auth\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
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
	Return user Roles
	**/
	public function getRole($id = null)
	{
		/** @var \yii\rbac\DbManager $authManager */
		$authManager = Yii::$app->get('authManager');
			
		if($id===null)
		{
			
			$Ridentity = $authManager->getRolesByUser($this->id);

		}
		else
		{
			$Ridentity = $authManager->getRolesByUser($id);
		}
		
		if($Ridentity)
		{
			foreach ($Ridentity as $item)
			{
			   $role[$item->name] = $item->name;
			   
			}
		}
		else
		{
			$role=null;
		}
		
		
		return $role;
		
	}
	
	function getRoleArray()
	{
		return implode(',', $this->role);
	}
	
	public function getRoleTypes()
	{
		/** @var \yii\rbac\DbManager $authManager */
		$roller = Yii::$app->get('authManager')->getRoles();
	
		foreach ($roller as $item)
		{
		   $role[$item->name] = $item->name;
		   
		}
		

		return $role;
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
		return static::findOne(['username' => $username, 'status' => static::STATUS_ACTIVE]);
	}

	/**
	 * @inheritdoc
	 */
	public static function findIdentityByAccessToken($token, $type = null)
	{
		throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 * @return static|null
	 */
	public static function findByPasswordResetToken($token)
	{
		$expire = Yii::$app->params['user.passwordResetTokenExpire'];
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
			['status', 'safe'],
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['email', 'unique', 'message' => Yii::t('auth.user', 'This username has already been taken.')],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'unique', 'message' => Yii::t('auth.user', 'This email address has already been taken.')],
			['email', 'exist', 'message' => Yii::t('auth.user', 'There is no user with such email.'), 'on' => 'requestPasswordResetToken'],

			['password', 'required', 'on' => 'signup'],
			['password', 'string', 'min' => 6],
		];
	}

	public function scenarios()
	{
		return [
			'signup' => ['username', 'email', 'password'],
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
			'role' => Yii::t('auth.user', 'Role'),
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

	public function beforeSave($insert)
	{
		if (parent::beforeSave($insert)) {
			if (($this->isNewRecord || in_array($this->getScenario(), ['resetPassword', 'profile'])) && !empty($this->password)) {
				$this->password_hash = Security::generatePasswordHash($this->password);
			}
			if ($this->isNewRecord) {
				$this->auth_key = Security::generateRandomKey();
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

		$this->_isSuperAdmin = in_array($this->role, Yii::$app->getModule('auth')->superAdmins);
		return $this->_isSuperAdmin;
	}

	public function login($duration = 0)
	{
		return Yii::$app->user->login($this, $duration);
	}
}
