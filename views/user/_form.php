<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use auth\models\User;

/**
 * @var yii\web\View $this
 * @var auth\models\User $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="user-form">

	<?php $form = ActiveForm::begin(); ?>

		<?= $form->field($model, 'username')->textInput(['maxlength' => 64]) ?>

		<?= $form->field($model, 'email')->textInput(['maxlength' => 128, 'type' => 'email']) ?>

		<?= $form->field($model, 'password')->passwordInput(['maxlength' => 128]) ?>

	<?=	$form->field($model, 'status')->dropDownList([
		User::STATUS_INACTIVE => $model->getStatus(User::STATUS_INACTIVE),
		User::STATUS_ACTIVE => $model->getStatus(User::STATUS_ACTIVE),
		User::STATUS_SUSPENDED => $model->getStatus(User::STATUS_SUSPENDED),
		User::STATUS_DELETED => $model->getStatus(User::STATUS_DELETED),
	]) ?>

	<div class="form-group">
			<?= Html::submitButton($model->isNewRecord ? Yii::t('auth.user', 'Create') : Yii::t('auth.user', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>

	<?php ActiveForm::end(); ?>

</div>
