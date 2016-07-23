<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = \Yii::t('auth.user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'registration-form',
                ]); ?>

                <?php if (false === Yii::$app->getModule('auth')->signupWithEmailOnly): ?>
                    <?= $form->field($model, 'username') ?>
                <?php endif ?>

                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= Html::submitButton(\Yii::t('auth.user', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <p class="text-center">
            <?= Html::a(\Yii::t('auth.user', 'Already registered? Sign in!'), ['/auth/default/login']) ?>
        </p>
    </div>
</div>
