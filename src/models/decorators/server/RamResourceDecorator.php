<?php

namespace hipanel\modules\finance\models\decorators\server;

use Yii;

class RamResourceDecorator extends AbstractServerResourceDecorator
{
    public function displayTitle()
    {
        return Yii::t('hipanel/server/order', 'RAM');
    }

    public function getPrepaidQuantity()
    {
        $part = $this->resource->part;
        preg_match('/((\d{1,5}) MB)$/i', $part->partno, $matches);
        return $matches[2] / 1024;
    }

    public function getOverusePrice()
    {
        return 4; // TODO: move to config
    }

    public function displayUnit()
    {
        return Yii::t('hipanel', 'GB');
    }

    public function displayPrepaidAmount()
    {
        return Yii::t('yii', '{nFormatted} GB', ['nFormatted' => $this->getPrepaidQuantity()]);
    }
}
