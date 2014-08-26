<?php
/**
 *
 * @author Ricardo Obregón <ricardo@obregon.co>
 * @created 25/08/14 07:26 PM
 */

use yii\db\Schema;

class m000000_000003_ChangeTokenColumn extends \yii\db\Migration
{
	public function safeUp()
	{
		$tableMap = Yii::$app->getModule('auth')->tableMap;
		$this->alterColumn($tableMap['User'], 'password_reset_token', Schema::TYPE_STRING . '(48)');
	}

	public function safeDown()
	{
		$tableMap = Yii::$app->getModule('auth')->tableMap;
		$this->alterColumn($tableMap['User'], 'password_reset_token', Schema::TYPE_STRING . '(32)');
	}
} 