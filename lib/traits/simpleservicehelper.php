<?php

namespace Bx\Model\Service\Ext\Traits;

use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;

trait SimpleServiceHelper
{
    abstract protected function getInternalList(
        array $params,
        ?array $fetchList,
        UserContextInterface $userContext = null
    ): ModelCollection;

    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        $fetchList = $params['fetch'] ?? null;
        unset($params['fetch']);

        return $this->getInternalList($params, $fetchList, $userContext);
    }
}
