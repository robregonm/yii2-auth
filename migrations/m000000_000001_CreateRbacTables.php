<?php
class m000000_000001_CreateRbacTables extends \yii\db\Migration
{
	private $tableMap;

	public function safeUp()
	{
		$path = __DIR__ . '/sql';
		if (!is_readable($path)) {
			throw new \yii\console\Exception('Missing "sql" directory.');
		}

		/** @var \yii\rbac\DbManager $authManager */
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

	public function safeDown()
	{
		$authManager = Yii::$app->getComponent('authManager');
		$this->dropTable($authManager->assignmentTable);
		$this->dropTable($authManager->itemChildTable);
		$this->dropTable($authManager->itemTable);
	}
}
