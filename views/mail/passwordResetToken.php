<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user auth\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/default/reset-password', 'token' => $user->password_reset_token]);
?>
	<p>Hello <?= Html::encode($user->username) ?>,</p>
	<p>Follow the link below to reset your password:</p>

<p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>