<?php

use yii\helpers\Html;
use hipanel\helpers\Url;
use hipanel\modules\finance\grid\PlanGridView;
use hipanel\modules\finance\menus\PlanDetailMenu;
use hipanel\widgets\IndexPage;


/**
 * @var \yii\web\View $this
 * @var \hipanel\modules\finance\models\Plan $model
 * @var \hipanel\modules\finance\helpers\PlanInternalsGrouper $grouper
 */

$this->title = Html::encode($model->name);
$this->params['breadcrumbs'][] = ['label' => Yii::t('hipanel:finance', 'Plans'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .profile-block {
        text-align: center;
    }
");

?>

<div class="row">
    <div class="col-md-3">
        <div class="box box-solid">
            <div class="box-body no-padding">
                <div class="profile-user-img text-center">
                    <i class="fa fa-bar-chart fa-5x"></i>
                </div>
                <p class="text-center">
                    <span class="profile-user-role">
                        <?= $this->title ?>
                    </span>
                    <br>
                    <span class="profile-user-name"><?= $model->type ?></span>
                </p>

                <div class="profile-usermenu" style="border-top: 1px solid #f4f4f4;">
                    <?= PlanDetailMenu::widget(['model' => $model]) ?>
                </div>
            </div>
            <div class="box-footer no-padding">
                <?= PlanGridView::detailView([
                    'model' => $model,
                    'boxed' => false,
                    'columns' => array_filter([
                        'simple_name',
                        'client',
                        'type',
                        'state',
                        'note',
                    ]),
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <?php $page = IndexPage::begin(['model' => $model, 'layout' => 'noSearch']) ?>
            <?php $page->beginContent('show-actions') ?>
                <h4 class="box-title" style="display: inline-block;">&nbsp;<?= Yii::t('hipanel:finance', 'Prices') ?></h4>
            <?php $page->endContent() ?>

            <?php $page->beginContent('bulk-actions') ?>
                <?= $page->renderBulkButton(Yii::t('hipanel', 'Update'), Url::to(['@price/update']), 'warning') ?>
                <?= $page->renderBulkButton(Yii::t('hipanel', 'Delete'), Url::to(['@price/delete']), 'danger') ?>
            <?php $page->endContent() ?>

                <?php if ($model->type === 'server'): ?>
                    <?= $this->render('view/_server', compact('model', 'grouper', 'page')) ?>
                <?php elseif ($model->type === 'template'): ?>
                    <?= $this->render('view/_template', compact('model', 'grouper', 'page')) ?>
                <?php else: ?>
                    <?php $page->beginContent('table') ?>
                        <div class="col-md-12">
                            <h2><?= Yii::t('hipanel:finance', 'This plan type viewing is not implemented yet') ?></h2>
                        </div>
                    <?php $page->endContent() ?>
                <?php endif ?>
        <?php $page->end() ?>
    </div>
</div>