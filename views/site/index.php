<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\NewsletterForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Newsletter';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <?php if (Yii::$app->session->hasFlash('msg')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->get('msg'); ?>
        </div>
    <?php endif; ?>

    <h1 class="my-5">
        <?= Html::encode($this->title) ?>
    </h1>

    <div class="row">
        <div class="col-md-12">

            <?php $form = ActiveForm::begin(['id' => 'newsletter-form']); ?>

            <?= $form->field($model, 'email')->input('email', ['required' => true]); ?>

            <?= $form->field($model, 'subject')->input('text', ['required' => true]); ?>

            <?= $form->field($model, 'body')->textarea(['rows' => 6, ['required' => true]]) ?>

            <?= $form->field($model, 'file')->fileInput([
                'accept'=>'.csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel'
            ]) ?>

            <div class="form-group">
                <?= Html::submitButton('Send', ['class' => 'btn btn-primary px-5', 'name' => 'newsletter-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

    </div>

</div>