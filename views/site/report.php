<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\NewsletterForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Report';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">

    <?php if (Yii::$app->session->hasFlash('msg')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->get('msg'); ?>
        </div>
    <?php endif; ?>

    <h1 class="my-5">
        <?= Html::encode($this->title) ?>
    </h1>

    <div class="row">
        <div class="col-md-12">
            <?php if (!empty($valideEmails) || !empty($invalidEmails)){ ?>
                <div class="table-responsive w-100 text-break">
                    <table class="table table-striped table-bordered table-hover">
                        <thead >
                            <tr class="table-dark">
                                <th class="w-50">Valid Emails</th>
                                <th class="w-50">Invalid Emails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="w-50"><?= implode(", ", $valideEmails) ?></td>
                                <td class="w-50"><?= implode(", ", $invalidEmails) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>

</div>