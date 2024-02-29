<?php

namespace Bx\Model\Service\Ext\Traits;

use Bitrix\Main\Result;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Interfaces\UserContextInterface;

trait SaveModelHelperWithReload
{
    use SaveModelHelper {
        internalSave as simpleInternalSave;
    }

    abstract public function getById($id, UserContextInterface $userContext = null): ?AbsOptimizedModel;

    protected function internalSave(array $data, AbsOptimizedModel $model): Result
    {
        $result = $this->simpleInternalSave($data, $model);
        if (!$result->isSuccess()) {
            return $result;
        }

        $dataProvider = $this->getDataProvider();
        $pkName = $dataProvider->getPkName();
        $pk = $model->getValueByKey($pkName);
        $modelFromDb = $this->getById($pk);
        if (empty($modelFromDb)) {
            return $result;
        }

        foreach ($modelFromDb as $key => $value) {
            $model[$key] = $value;
        }

        return $result;
    }
}
