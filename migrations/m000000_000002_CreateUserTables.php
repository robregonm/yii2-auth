<?php
use yii\db\Schema;

class m000000_000002_CreateUserTables extends \yii\db\Migration
{
	private $tableMap;

	public function safeUp()
	{
		$tableMap = Yii::$app->getModule('auth')->tableMap;

		$this->createTable(
			 $tableMap['User'],
				 array(
					 'id' => Schema::TYPE_PK,
					 'username' => Schema::TYPE_STRING . '(64) NOT NULL',
					 'email' => Schema::TYPE_STRING . '(128) NOT NULL',
					 'password_hash' => Schema::TYPE_STRING . '(128) NOT NULL',
					 'password_reset_token' => Schema::TYPE_STRING . '(32)',
					 'auth_key' => Schema::TYPE_STRING . '(128)',
					 'status' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT ' . \auth\models\User::STATUS_ACTIVE,
					 'last_visit_time' => Schema::TYPE_TIMESTAMP,
					 'create_time' => Schema::TYPE_TIMESTAMP . ' NOT NULL',
					 'update_time' => Schema::TYPE_TIMESTAMP,
					 'delete_time' => Schema::TYPE_TIMESTAMP,
				 )
		);
		$this->createIndex('User_status_ix', $tableMap['User'], 'status');

		$this->createTable(
			 $tableMap['ProfileFieldType'],
				 array(
					 'id' => Schema::TYPE_PK,
					 'name' => Schema::TYPE_STRING . ' NOT NULL',
					 'title' => Schema::TYPE_STRING . ' NOT NULL',
				 )
		);
		$this->createIndex('ProfileFieldType_name_uk', $tableMap['ProfileFieldType'], 'name', true);

		$this->insert($tableMap['ProfileFieldType'], array('name' => 'integer', 'title' => 'Integer')); //1
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'string', 'title' => 'String')); //2
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'text', 'title' => 'Text')); //3
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'boolean', 'title' => 'Boolean')); //4
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'decimal', 'title' => 'Decimal')); //5
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'money', 'title' => 'Money')); //6
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'date', 'title' => 'Date only')); //7
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'datetime', 'title' => 'Date and Time')); //8
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'time', 'title' => 'Time only')); //9
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'url', 'title' => 'Url Address')); //10
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'email', 'title' => 'Email')); //11
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'lookup', 'title' => 'Lookup')); //12
		$this->insert($tableMap['ProfileFieldType'], array('name' => 'list', 'title' => 'List')); //13

		$this->createTable(
			 $tableMap['ProfileField'],
				 array(
					 'id' => Schema::TYPE_PK,
					 'name' => Schema::TYPE_STRING . '(32) NOT NULL',
					 'title' => Schema::TYPE_STRING,
					 'type_id' => Schema::TYPE_INTEGER . ' NOT NULL', // Field Type
					 'position' => Schema::TYPE_INTEGER . ' NOT NULL',
					 'required' => Schema::TYPE_BOOLEAN . ' NOT NULL',
					 'configuration' => Schema::TYPE_TEXT,
					 //'size' => 'integer', //Field Size
					 /*'min_length' => 'integer',
					'max_length' => 'integer',
					'match' => 'text',
					'range' => 'string',*/
					 'error_message' => Schema::TYPE_STRING,
					 'default_value' => Schema::TYPE_STRING,
					 'read_only' => Schema::TYPE_BOOLEAN . ' NOT NULL',
				 )
		);
		$this->createIndex('ProfileField_name_uk', $tableMap['ProfileField'], 'name', true);
		$this->createIndex('ProfileField_type_ix', $tableMap['ProfileField'], 'type_id');
		$this->addForeignKey('ProfileField_type_fk', $tableMap['ProfileField'], 'type_id', $tableMap['ProfileFieldType'], 'id');

		$this->insert(
			 $tableMap['ProfileField'],
				 array('name' => 'first_name', 'title' => 'First Name', 'type_id' => 2, 'position' => 1, 'required' => 0, 'read_only' => 0)
		);
		$this->insert(
			 $tableMap['ProfileField'],
				 array('name' => 'last_name', 'title' => 'Last Name', 'type_id' => 2, 'position' => 2, 'required' => 0, 'read_only' => 0)
		);
		$this->insert(
			 $tableMap['ProfileField'],
				 array('name' => 'website', 'title' => 'Website', 'type_id' => 2, 'position' => 3, 'required' => 0, 'read_only' => 0)
		);


		$this->createTable(
			 $tableMap['ProfileFieldValue'],
				 array(
					 'id' => Schema::TYPE_PK,
					 'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
					 'field_id' => Schema::TYPE_INTEGER . ' NOT NULL',
					 'value' => Schema::TYPE_TEXT,
				 )
		);
		$this->createIndex('Profile_field_ix', $tableMap['ProfileFieldValue'], 'field_id');
		//$this->createIndex('Profile_value_ix', $tableMap['ProfileFieldValue'], 'value');
		$this->addForeignKey('Profile_user_fk', $tableMap['ProfileFieldValue'], 'user_id', $tableMap['User'], 'id');
		$this->addForeignKey('Profile_field_fk', $tableMap['ProfileFieldValue'], 'field_id', $tableMap['ProfileField'], 'id');


		// Creates the default admin user
		$adminUser = new \auth\models\User();
		$adminUser->setScenario('signup');

		echo 'Please type the admin user info: ' . PHP_EOL;
		$this->readStdinUser('Email (e.g. admin@mydomain.com)', $adminUser, 'email');
		$this->readStdinUser('Type Username', $adminUser, 'username', $adminUser->email);
		$this->readStdinUser('Type Password', $adminUser, 'password', 'admin');

		if (!$adminUser->save()) {
			throw new \yii\console\Exception('Error when creating admin user.');
		}
		echo 'User created successfully.' . PHP_EOL;
	}

	private function readStdinUser($prompt, $model, $field, $default = '')
	{
		while (!isset($input) || !$model->validate(array($field))) {
			echo $prompt . (($default) ? " [$default]" : '') . ': ';
			$input = (trim(fgets(STDIN)));
			if (empty($input) && !empty($default)) {
				$input = $default;
			}
			$model->$field = $input;
		}
		return $input;
	}

	public function safeDown()
	{
		$tableMap = Yii::$app->getModule('auth')->tableMap;
		$this->dropTable($tableMap['ProfileFieldValue']);
		$this->dropTable($tableMap['ProfileField']);
		$this->dropTable($tableMap['ProfileFieldType']);
		$this->dropTable($tableMap['User']);
	}
}
