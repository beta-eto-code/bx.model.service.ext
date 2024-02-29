<?php

namespace Bx\Model\Service\Ext;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bx\Model\Interfaces\ModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Data\Provider\Interfaces\CompareRuleInterface;
use Data\Provider\QueryCriteria;

abstract class BaseModelService extends BaseROModelService implements ModelServiceInterface
{
    /**
     * @param     int                       $id
     * @param     UserContextInterface|null $userContext
     * @return    Result
     * @deprected
     * @see       BaseModelService::removeByPk($pkValue, UserContextInterface $userContext = null)
     */
    public function delete(int $id, UserContextInterface $userContext = null): Result
    {
        return $this->removeByPk($id);
    }

    /**
     * @param  mixed $pkValue
     * @param  UserContextInterface|null $userContext
     * @return Result
     */
    public function removeByPk($pkValue, UserContextInterface $userContext = null): Result
    {
        $result = new Result();
        $pkName = $this->dataProvider->getPkName() ?? 'ID';
        $query = new QueryCriteria();
        $query->addCriteria($pkName, CompareRuleInterface::EQUAL, $pkValue);
        $queryResult = $this->dataProvider->remove($query);
        if ($queryResult->hasError()) {
            return $result->addError(new Error($queryResult->getErrorMessage()));
        }

        return $result;
    }
}
