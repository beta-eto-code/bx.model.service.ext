<?php

namespace Bx\Model\Service\Ext\Traits;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Interfaces\UserContextInterface;
use Data\Provider\Interfaces\CompareRuleInterface;
use Data\Provider\Interfaces\DataProviderInterface;
use Data\Provider\QueryCriteria;

trait SaveModelHelper
{
    abstract public function getDataProvider(): DataProviderInterface;

    abstract protected function getDataMapForSave(): array;

    abstract protected function getCompareClassMapForSave(): array;

    /**
     * @param AbsOptimizedModel $model
     * @param UserContextInterface|null $userContext
     * @return Result
     */
    public function save(AbsOptimizedModel $model, UserContextInterface $userContext = null): Result
    {
        $data = $this->getDataByMapKeys($model, $this->getDataMapForSave(), $this->getCompareClassMapForSave());
        return $this->internalSave($data, $model);
    }

    protected function internalSave(array $data, AbsOptimizedModel $model): Result
    {
        $dataProvider = $this->getDataProvider();
        $pkName = $dataProvider->getPkName();
        $pk = $model->getValueByKey($pkName);
        if (!empty($pk)) {
            $query = new QueryCriteria();
            $query->addCriteria($this->getDataProvider()->getPkName(), CompareRuleInterface::EQUAL, $pk);
        }

        $result = new Result();
        $saveResult = $dataProvider->save($data, $query);
        if ($saveResult->hasError()) {
            return $result->addError(new Error($saveResult->getErrorMessage()));
        }

        if (empty($pk)) {
            $model[$pkName] = $saveResult->getPk();
        }

        return $result;
    }

    protected function getDataByMapKeys(AbsOptimizedModel $model, array $mapKeys, array $compareMapClass = []): array
    {
        $data = [];
        foreach ($mapKeys as $key => $value) {
            if (empty($key) || is_int($key)) {
                $key = $value;
            }

            $compareClass = $compareMapClass[$key] ?? null;
            $data = $this->getUpdatedData($data, $model, $key, $value, $compareClass);
        }
        return $data;
    }

    protected function getUpdatedDataAsDateTime(
        array $originalData,
        AbsOptimizedModel $model,
        string $modelKey,
        ?string $dataKey = null
    ): array {
        return $this->getUpdatedData($originalData, $model, $modelKey, $dataKey, DateTime::class);
    }

    protected function getUpdatedData(
        array $originalData,
        AbsOptimizedModel $model,
        string $modelKey,
        ?string $dataKey = null,
        ?string $compareClass = null
    ): array {
        if ($model->hasValueKey($modelKey)) {
            $dataKey = $dataKey ?? $modelKey;
            $value = $model->getValueByKey($modelKey);
            if (empty($compareClass) || is_a($value, $compareClass)) {
                $originalData[$dataKey] = $model->getValueByKey($modelKey);
            }
        }

        return $originalData;
    }
}