<?php
/**
 * Finance module for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-finance
 * @package   hipanel-module-finance
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\finance\providers;

use hipanel\helpers\ArrayHelper;
use hipanel\models\Ref;
use hipanel\modules\finance\models\Bill;
use yii\base\Application;

/**
 * Class BillTypesProvider.
 */
class BillTypesProvider
{
    /**
     * @var Application
     */
    private Application $app;

    /**
     * @var bool
     */
    private bool $showUnusedTypes = false;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Returns key-value list of bill types.
     * `key` - type name
     * `value` - type label (translated).
     * @return array
     */
    public function getTypesList(): array
    {
        return ArrayHelper::map($this->getTypes(), 'name', 'label');
    }

    /**
     * Returns array of types.
     * When user can not support, filters out unused types.
     * @return Ref[]
     */
    public function getTypes(): array
    {
        $options = ['select' => 'full', 'orderby' => 'name_asc', 'with_hierarchy' => true];
        $types = Ref::findCached('type,bill', 'hipanel.finance.billTypes', $options);

        if (!$this->app->user->can('owner-staff')) {
            $types = $this->removeUnusedTypes($types);
        }

        return $types;
    }

    /**
     * @param Ref[] $types
     * @return Ref[]
     */
    private function removeUnusedTypes(array $types): array
    {
        if ($this->showUnusedTypes) {
            return $types;
        }
        $ids = $this->app->cache->getOrSet([__METHOD__, $this->app->user->id], function () use ($types) {
            return ArrayHelper::getColumn(Bill::perform('get-used-types', [], ['batch' => true]), 'id');
        }, 3600);

        return array_filter($types, function ($model) use ($ids) {
            return in_array($model->id, $ids, true);
        });
    }

    public function getGroupedList(): array
    {
        $billTypes = [];
        $billGroupLabels = [];

        $types = $this->getTypesList();

        foreach ($types as $key => $title) {
            @[$type, $name] = explode(',', $key);

            if (!isset($billTypes[$type])) {
                $billTypes[$type] = [];
                $billGroupLabels[$type] = ['label' => $title];
            }

            if (isset($name)) {
                foreach ($types as $k => $t) {
                    if (str_starts_with($k, $type . ',')) {
                        $billTypes[$type][$k] = $t;
                    }
                }
            }
        }

        return [$billTypes, $billGroupLabels];
    }

    /**
     * This method prevents removing unused user types of payments.
     */
    public function keepUnusedTypes(): void
    {
        $this->showUnusedTypes = true;
    }
}
