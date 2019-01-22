<?php

use common\models\EmailMessage;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/**
 * @var yii\web\View                            $this
 * @var yii\data\ActiveDataProvider             $dataProvider
 * @var common\models\search\EmailMessageSearch $searchModel
 */
$this->title                   = Yii::t('email', 'Email History');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-message-index">
	<?php Pjax::begin();
	try {
		echo GridView::widget([
			'dataProvider' => $dataProvider,
			'filterModel'  => $searchModel,
			'columns'      => [
				'id',
				[
					'attribute' => 'to',
					'format'    => 'html',
					'value'     => function(EmailMessage $data) {
						return \common\helpers\EmailHelper::protectOff($data->to);
					},
				],
				'subject',
				[
					'attribute' => 'status',
					'filter'    => EmailMessage::STATUS,
					'value'     => function(EmailMessage $data) {
						return EmailMessage::STATUS[$data->status];
					},
				],
				[
					'attribute' => 'created_at',
					'format'    => [
						'datetime',
						(isset(Yii::$app->modules['datecontrol']['displaySettings']['datetime'])) ? Yii::$app->modules['datecontrol']['displaySettings']['datetime'] : 'd-m-Y H:i:s A',
					],
				],
				[
					'attribute' => 'sent_at',
					'format'    => [
						'datetime',
						(isset(Yii::$app->modules['datecontrol']['displaySettings']['datetime'])) ? Yii::$app->modules['datecontrol']['displaySettings']['datetime'] : 'd-m-Y H:i:s A',
					],
				],
				[
					'class'    => 'yii\grid\ActionColumn',
					'template' => '{view}{resend}',
					'buttons'  => [
						'resend' => function($url, $model) {
							return Html::a('<i class="fa fa-refresh"></i>', Url::to([
								'/mailer/message/resend',
								'id' => $model->id,
							]), ['title' => Yii::t('yii', 'Resend'),]);
						},
					],
				],
			],
			'responsive'   => true,
			'hover'        => true,
			'condensed'    => true,
			'floatHeader'  => true,
			'panel'        => [
				'heading'    => '',
				'type'       => 'info',
				'before'     => '',
				'after'      => Html::a('<i class="glyphicon glyphicon-repeat"></i> Reset List', ['index'], ['class' => 'btn btn-info']),
				'showFooter' => false,
			],
		]);
	} catch (Exception $e) {
	}
	Pjax::end(); ?>

</div>
