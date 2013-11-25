<?php
class m000000_000001_CreateDefaultTables extends \yii\db\Migration
{
	private $tableMap;

	private function createAuthTables()
	{
		$path = __DIR__ . '/sql';
		if (!is_readable($path)) {
			throw new \yii\console\Exception('Missing "sql" directory.');
		}

		$authManager = Yii::$app->getComponent('authManager');

		if (!$authManager) {
			throw new \yii\console\Exception('"authManager" component must be configured in console config file (e.g. config/console.php)');
		}

		$scriptFile = $path . DIRECTORY_SEPARATOR . 'schema-' . $this->db->driverName . '.sql';
		$script = file_get_contents($scriptFile);
		$script = str_replace(
			['{AuthAssignment}', '{AuthItemChild}', '{AuthItem}'],
			[$authManager->assignmentTable, $authManager->itemChildTable, $authManager->itemTable],
			$script
		);
		if ($script === false) {
			throw new \yii\console\Exception('Cannot read SQL script file');
		}
		$sqlScript = explode(';', $script);
		foreach ($sqlScript as $sql) {
			if (trim($sql) != '') {
				$this->execute($sql);
			}
		}
	}

	public function safeUp()
	{
		$this->createAuthTables();
		$tableMap = Yii::$app->getModule('auth')->tableMap;

		$this->createTable(
			 $tableMap['User'],
				 array(
					 'id' => \yii\db\Schema::TYPE_PK,
					 'username' => \yii\db\Schema::TYPE_STRING . '(64) NOT NULL',
					 'email' => \yii\db\Schema::TYPE_STRING . '(128) NOT NULL',
					 'password_hash' => \yii\db\Schema::TYPE_STRING . '(128) NOT NULL',
					 'password_reset_token' => \yii\db\Schema::TYPE_STRING . '(32)',
					 'auth_key' => \yii\db\Schema::TYPE_STRING . '(128)',
					 'status' => \yii\db\Schema::TYPE_INTEGER . ' NOT NULL DEFAULT ' . \app\modules\user\models\User::STATUS_ACTIVE,
					 'last_visit_time' => \yii\db\Schema::TYPE_TIMESTAMP,
					 'create_time' => \yii\db\Schema::TYPE_TIMESTAMP . ' NOT NULL',
					 'update_time' => \yii\db\Schema::TYPE_TIMESTAMP,
					 'delete_time' => \yii\db\Schema::TYPE_TIMESTAMP,
				 )
		);
		$this->createIndex('User_status_ix', $tableMap['User'], 'status');

		$this->createTable(
			 $tableMap['ProfileFieldType'],
				 array(
					 'id' => \yii\db\Schema::TYPE_PK,
					 'name' => \yii\db\Schema::TYPE_STRING . ' NOT NULL',
					 'title' => \yii\db\Schema::TYPE_STRING . ' NOT NULL',
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
					 'id' => \yii\db\Schema::TYPE_PK,
					 'name' => \yii\db\Schema::TYPE_STRING . '(32) NOT NULL',
					 'title' => \yii\db\Schema::TYPE_STRING,
					 'type_id' => \yii\db\Schema::TYPE_INTEGER . ' NOT NULL', // Field Type
					 'position' => \yii\db\Schema::TYPE_INTEGER . ' NOT NULL',
					 'required' => \yii\db\Schema::TYPE_BOOLEAN . ' NOT NULL',
					 'configuration' => \yii\db\Schema::TYPE_TEXT,
					 //'size' => 'integer', //Field Size
					 /*'min_length' => 'integer',
					'max_length' => 'integer',
					'match' => 'text',
					'range' => 'string',*/
					 'error_message' => \yii\db\Schema::TYPE_STRING,
					 'default_value' => \yii\db\Schema::TYPE_STRING,
					 'read_only' => \yii\db\Schema::TYPE_BOOLEAN . ' NOT NULL',
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
					 'id' => \yii\db\Schema::TYPE_PK,
					 'user_id' => \yii\db\Schema::TYPE_INTEGER . ' NOT NULL',
					 'field_id' => \yii\db\Schema::TYPE_INTEGER . ' NOT NULL',
					 'value' => \yii\db\Schema::TYPE_TEXT,
				 )
		);
		$this->createIndex('Profile_field_ix', $tableMap['ProfileFieldValue'], 'field_id');
		$this->createIndex('Profile_value_ix', $tableMap['ProfileFieldValue'], 'value');
		$this->addForeignKey('Profile_user_fk', $tableMap['ProfileFieldValue'], 'user_id', $tableMap['User'], 'id');
		$this->addForeignKey('Profile_field_fk', $tableMap['ProfileFieldValue'], 'field_id', $tableMap['ProfileField'], 'id');


		// Creates the default admin user
		$adminUser = new app\modules\user\models\User();
		$adminUser->setScenario('signup');

		echo 'Please type the admin user info: ' . PHP_EOL;
		$this->readStdinUser('Email (e.g. admin@mydomain.com)', $adminUser, 'email');
		$this->readStdinUser('Type Username', $adminUser, 'username', $adminUser->email);
		$this->readStdinUser('Type Password', $adminUser, 'password', 'admin');

		if (!$adminUser->save()) {
			throw new \yii\console\Exception('Error when creating admin user.');
		}
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

	private function dropAuthTables()
	{
		$authManager = Yii::$app->getComponent('authManager');
		$this->dropTable($authManager->assignmentTable);
		$this->dropTable($authManager->itemChildTable);
		$this->dropTable($authManager->itemTable);

	}

	public function safeDown()
	{
		$tableMap = Yii::$app->getModule('auth')->tableMap;
		$this->dropTable($tableMap['ProfileFieldValue']);
		$this->dropTable($tableMap['ProfileField']);
		$this->dropTable($tableMap['ProfileFieldType']);
		$this->dropTable($tableMap['User']);
		$this->dropAuthTables();
	}
}
