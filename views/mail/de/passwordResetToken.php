<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user auth\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['auth/default/reset-password', 'token' => $user->password_reset_token]);
?>
    <p>Hallo <?= Html::encode($user->username) ?>,</p>
    <p>Über den folgenden Link kannst du dein Passwort zurücksetzen:</p>

<p><?= Html::a(Html::encode($resetLink), $resetLink) ?></p>