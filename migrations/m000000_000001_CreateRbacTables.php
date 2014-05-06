<?php
use yii\db\Schema;

class m000000_000001_CreateRbacTables extends \yii\db\Migration
{
	private $tableMap;

	public function safeUp()
	{
		/** @var \yii\rbac\DbManager $authManager */
		$authManager = Yii::$app->get('authManager');

		$this->createTable($authManager->ruleTable, [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'data' => Schema::TYPE_TEXT,
			'created_at' => Schema::TYPE_INTEGER,
			'updated_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (name)',
		]);

		$this->createTable($authManager->itemTable, [
			'name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'type' => Schema::TYPE_INTEGER . ' NOT NULL',
			'description' => Schema::TYPE_TEXT,
			'rule_name' => Schema::TYPE_STRING . '(64)',
			'data' => Schema::TYPE_TEXT,
			'created_at' => Schema::TYPE_INTEGER,
			'updated_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (name)',
		]);

		$this->addForeignKey('AuthItem_rule_name_fk', $authManager->itemTable, 'rule_name', $authManager->ruleTable, 'name', 'SET NULL', 'CASCADE');

		$this->createIndex('AuthItem_type_idx', $authManager->itemTable, 'type');

		$this->createTable($authManager->itemChildTable, [
			'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
			'child' => Schema::TYPE_STRING . '(64) NOT NULL',
			'PRIMARY KEY (parent,child)',
		]);

		$this->addForeignKey('AuthItemChild_parent_fk', $authManager->itemChildTable, 'parent', $authManager->itemTable, 'name', 'CASCADE', 'CASCADE');
		$this->addForeignKey('AuthItemChild_child_fk', $authManager->itemChildTable, 'child', $authManager->itemTable, 'name', 'CASCADE', 'CASCADE');

		$this->createTable($authManager->assignmentTable, [
			'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
			'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
			'created_at' => Schema::TYPE_INTEGER,
			'PRIMARY KEY (item_name,user_id)',
		]);

		$this->addForeignKey('AuthAssignment_item_name_fk', $authManager->assignmentTable, 'item_name', $authManager->itemTable, 'name', 'CASCADE', 'CASCADE');
	}

	public function safeDown()
	{
		/** @var \yii\rbac\DbManager $authManager */
		$authManager = Yii::$app->get('authManager');
		$this->dropTable($authManager->assignmentTable);
		$this->dropTable($authManager->itemChildTable);
		$this->dropTable($authManager->itemTable);
		$this->dropTable($authManager->ruleTable);
	}
}
