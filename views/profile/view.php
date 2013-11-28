<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var auth\models\User $model
 */

$this->title = 'View Profile';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="panel panel-primary user-view">
	<div class="panel-heading">
		<h3 class="panel-title">
			<?= Html::encode($this->title) ?>
			<?= Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['update'], ['class' => 'pull-right']) ?>
		</h3>
	</div>
	<?php echo DetailView::widget([
		'model' => $model,
		'attributes' => [
			//'id',
			'username',
			'email:email',
			[
				'name' => 'status',
				'value' => $model->getStatus()
			],
			'last_visit_time',
			'create_time',
			'update_time',
		],
	]); ?>
</div>
