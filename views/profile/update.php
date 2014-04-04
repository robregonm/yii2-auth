<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use auth\models\User;

/**
 * @var yii\web\View $this
 * @var auth\models\User $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = \Yii::t('auth.user', 'Update Profile');
$this->params['breadcrumbs'][] = ['label' => Yii::t('auth.user', 'Profile'), 'url' => ['view']];
$this->params['breadcrumbs'][] = Yii::t('auth.user', 'Update');
?>
<?php $form = ActiveForm::begin(); ?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">
			<?= Html::encode($this->title) ?>
		</h3>
	</div>
	<div class="panel-body user-update">
		<?= $form->field($model, 'username')->textInput(['maxlength' => 64]) ?>

		<?= $form->field($model, 'email')->textInput(['maxlength' => 128, 'type' => 'email']) ?>

		<?= $form->field($model, 'password')->passwordInput() ?>

		<?php if ($model->getIsSuperAdmin()): // ToDo: Allow admins too ?>
			<?=
			$form->field($model, 'status')->dropDownList([
				User::STATUS_INACTIVE => $model->getStatus(User::STATUS_INACTIVE),
				User::STATUS_ACTIVE => $model->getStatus(User::STATUS_ACTIVE),
				User::STATUS_SUSPENDED => $model->getStatus(User::STATUS_SUSPENDED),
				User::STATUS_DELETED => $model->getStatus(User::STATUS_DELETED),
			]) ?>
		<?php endif; ?>

		<div class="">
		</div>

	</div>
	<div class="panel-footer">
		<?= Html::submitButton(Yii::t('auth.user', $model->isNewRecord ? 'Create' : 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>
</div>
<?php ActiveForm::end(); ?>
