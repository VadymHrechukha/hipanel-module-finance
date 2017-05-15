<?php

namespace hipanel\modules\finance\widgets;

use yii\base\Widget;
use yii\helpers\Html;

class LinkToObjectResolver extends Widget
{
    public $model;

    public $linkOptions = [
        'class' => 'text-bold',
    ];

    private $links = [
        'ip' => '@ip/view',
        'client' => '@client/view',
        'account' => '@account/view',
        'server' => '@server/view',
    ];

    public function run()
    {
        return Html::a($this->getLabel(), $this->getLink(), $this->linkOptions);
    }

    private function getLabel()
    {
        return $this->model->object;
    }

    private function getLink()
    {
        return [$this->links[$this->model->tariff_type], 'id' => $this->model->object_id];
    }
}