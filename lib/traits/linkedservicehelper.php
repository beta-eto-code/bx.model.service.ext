<?php

namespace Bx\Model\Service\Ext\Traits;

use Bx\Model\Interfaces\FetcherModelInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;
use Exception;

trait LinkedServiceHelper
{
    /**
     * @return FetcherModelInterface[]
     */
    abstract protected function getLinkedFields(): array;

    abstract protected function getInternalList(
        array $params,
        ?array $fetchList,
        UserContextInterface $userContext = null
    ): ModelCollection;

    /**
     * @throws Exception
     */
    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        $fetchList = $params['fetch'] ?? null;
        unset($params['fetch']);

        $collection = $this->getInternalList($params,  $fetchList, $userContext);
        $this->loadLinkedModel($collection, $fetchList);

        return $collection;
    }

    /**
     * @param ModelCollection $collection
     * @param array|null      $fetchList
     */
    public function loadLinkedModel(ModelCollection $collection, array $fetchList = null): void
    {
        $linkedFieldList = $this->getLinkedFields();
        $defaultFetchList = array_keys($linkedFieldList);
        $fetchList = $fetchList ?? $defaultFetchList;

        foreach ($fetchList as $key) {
            $linkedField = $linkedFieldList[$key] ?? null;
            if ($linkedField instanceof FetcherModelInterface) {
                $linkedField->fill($collection);
            }
        }
    }
}
