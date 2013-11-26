<?php

namespace auth\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use auth\models\User;

/**
 * UserSearch represents the model behind the search form about User.
 */
class UserSearch extends Model
{
	public $id;
	public $username;
	public $email;
	public $password_hash;
	public $password_reset_token;
	public $auth_key;
	public $status;
	public $last_visit_time;
	public $create_time;
	public $update_time;
	public $delete_time;

	public function rules()
	{
		return [
			[['id', 'status'], 'integer'],
			[['username', 'email', 'password_hash', 'password_reset_token', 'auth_key', 'last_visit_time', 'create_time', 'update_time', 'delete_time'], 'safe'],
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

	public function search($params)
	{
		$query = User::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		if (!($this->load($params) && $this->validate())) {
			return $dataProvider;
		}

		$this->addCondition($query, 'id');
		$this->addCondition($query, 'username', true);
		$this->addCondition($query, 'email', true);
		$this->addCondition($query, 'password_hash', true);
		$this->addCondition($query, 'password_reset_token', true);
		$this->addCondition($query, 'auth_key', true);
		$this->addCondition($query, 'status');
		$this->addCondition($query, 'last_visit_time', true);
		$this->addCondition($query, 'create_time', true);
		$this->addCondition($query, 'update_time', true);
		$this->addCondition($query, 'delete_time', true);
		return $dataProvider;
	}

	protected function addCondition($query, $attribute, $partialMatch = false)
	{
		$value = $this->$attribute;
		if (trim($value) === '') {
			return;
		}
		if ($partialMatch) {
			$value = '%' . strtr($value, ['%'=>'\%', '_'=>'\_', '\\'=>'\\\\']) . '%';
			$query->andWhere(['like', $attribute, $value]);
		} else {
			$query->andWhere([$attribute => $value]);
		}
	}
}
