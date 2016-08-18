<?php

/*
 * Finance module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\models;

use hipanel\modules\finance\models\query\TariffQuery;

/**
 * Class Tariff
 * @package hipanel\modules\finance\models
 * @property Resource[]|DomainResource[] $resources
 */
class Tariff extends \hipanel\base\Model
{
    use \hipanel\base\ModelTrait;

    const TYPE_DOMAIN = 'domain';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['client_id', 'seller_id', 'id'],      'integer'],
            [['client', 'seller', 'bill', 'name'],  'safe'],
            [['domain', 'server'],                  'safe'],
            [['tariff', 'tariff_id'],               'safe'],
            [['type_id', 'state_id'],               'integer'],
            [['type', 'state'],                     'safe'],
            [['used'],                              'integer'],
            [['note', 'label'],                     'safe'],
        ];
    }

    public function getResources()
    {
        if ($this->type === self::TYPE_DOMAIN) {
            return $this->hasMany(DomainResource::class, ['tariff_id' => 'id']);
        }

        return $this->hasMany(Resource::class, ['tariff_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return $this->mergeAttributeLabels([
        ]);
    }

    /**
     * {@inheritdoc}
     * @return TariffQuery
     */
    public static function find($options = [])
    {
        return new TariffQuery(get_called_class(), [
            'options' => $options,
        ]);
    }
}
