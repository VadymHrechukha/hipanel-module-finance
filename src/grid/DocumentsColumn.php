<?php
/**
 * Finance module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2017, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\grid;

use hipanel\widgets\ArraySpoiler;
use hipanel\helpers\FontIcon;
use hipanel\widgets\ModalButton;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

class DocumentsColumn extends \hipanel\grid\DataColumn
{
    public $format = 'raw';

    public $type;

    public function init()
    {
        if ($this->type === null) {
            throw new InvalidConfigException('Property "type" must be set');
        }
    }

    public function getDataCellValue($model, $key, $index)
    {
        return ArraySpoiler::widget([
            'mode' => ArraySpoiler::MODE_SPOILER,
            'data' => parent::getDataCellValue($model, $key, $index),
            'delimiter' => ' ',
            'formatter' => function ($doc) {
                return Html::a(
                    FontIcon::i('fa-file-pdf-o') . date(' M Y', strtotime($doc->validity_start)),
                    ["/file/{$doc->file_id}/{$doc->filename}", 'nocache' => 1],
                    [
                        'target' => '_blank',
                        'class' => 'btn btn-app pull-left',
                        'style' => 'width: 8em;'
                    ]
                );
            },
            'template' => '<div class="text-right clearfix" style="margin-bottom: 10px;padding-left: 10px;">' . $this->generateManagementButtons($model). '{button}</div><div>{visible}{hidden}</div>',
            'visibleCount' => 3,
            'button' => [
                'label' => FontIcon::i('fa-history') . ' ' . Yii::t('hipanel', 'Show all'),
                'class' => 'btn btn-xs btn-default',
                'data' => [
                    'toggle' => 'button'
                ],
            ],
        ]);
    }

    protected function generateManagementButtons($model)
    {
        if (!Yii::$app->user->can('manage')) {
            return null;
        }

        $buttons[] = $this->renderSeeNewLink($model);
        $buttons[] = $this->renderUpdateButton($model);

        return Html::tag('div', implode('', $buttons), ['class' => 'btn-group', 'style' => 'display: block;']);
    }

    protected function renderSeeNewLink($model)
    {
        return Html::a(
            Yii::t('hipanel:finance', 'See new'),
            $this->getSeeNewRoute($model),
            ['class' => 'btn btn-default btn-xs', 'target' => 'new-invoice']
        );
    }

    protected function renderUpdateButton($model)
    {
        return ModalButton::widget([
            'id' => "modal-{$model->id}-{$this->type}",
            'model' => $model,
            'form' => [
                'action' => $this->getUpdateButtonRoute($model),
            ],
            'button' => [
                'label' => Yii::t('hipanel:finance', 'Update'),
                'class' => 'btn btn-default btn-xs',
            ],
            'body' => implode('', [
                Html::activeHiddenInput($model, 'type', ['value' => $this->type]),
                Yii::t('hipanel:finance', 'Are you sure you want to update document?') . '<br>',
                Yii::t('hipanel:finance', 'Current document will be substituted with newer version!'),
            ]),
            'modal' => [
                'header' => Html::tag('h4', Yii::t('hipanel:finance', 'Confirm document updating')),
                'headerOptions' => ['class' => 'label-warning'],
                'footer' => [
                    'label' => Yii::t('hipanel', 'Update'),
                    'class' => 'btn btn-warning',
                    'data-loading-text' => Yii::t('hipanel', 'Updating...'),
                ],
            ],
        ]);
    }

    protected function getSeeNewRoute($model)
    {
        return [
            '@purse/generate-document',
            'id' => $model->id,
            'type' => $this->type,
            'login' => $model->client,
            'currency' => $model->currency,
        ];
    }

    protected function getUpdateButtonRoute($model)
    {
        return ['@purse/generate-and-save-document'];
    }
}
