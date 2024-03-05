<?php

namespace Bx\Model\Service\Ext;

use BX\Data\Provider\BxQueryAdapter;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Interfaces\DerivativeModelInterface;
use Bx\Model\Interfaces\FetcherModelInterface;
use Bx\Model\Interfaces\ModelCollectionInterface;
use Bx\Model\Interfaces\ModelQueryInterface;
use Bx\Model\Interfaces\Models\QueryableModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;
use Bx\Model\Traits\FilterableHelper;
use Bx\Model\Traits\LimiterHelper;
use Bx\Model\Traits\SortableHelper;
use Data\Provider\Interfaces\CompareRuleInterface;
use Data\Provider\Interfaces\DataProviderInterface;
use Data\Provider\QueryCriteria;

abstract class BaseROModelService implements QueryableModelServiceInterface, ExtReadableModelServiceInterface
{
    use FilterableHelper;
    use SortableHelper;
    use LimiterHelper;

    /**
     * @var DataProviderInterface
     */
    protected DataProviderInterface $dataProvider;

    /**
     * @return string|AbsOptimizedModel
     * @psalm-suppress MismatchingDocblockReturnType
     */
    abstract protected function getModelClass(): string;

    abstract protected function getDefaultSelect(): array;

    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @param array $params
     * @param array|null $fetchList
     * @param UserContextInterface|null $userContext
     * @return ModelCollection
     */
    protected function getInternalList(
        array $params,
        ?array $fetchList,
        UserContextInterface $userContext = null
    ): ModelCollection {
        /**
         * @var string $className
         */
        $className = $this->getModelClass();
        $result = new ModelCollection([], $className);
        $params['select'] = $params['select'] ?: $this->getDefaultSelect();
        $queryAdapter = BxQueryAdapter::initFromArray($params);
        $dataList = $this->dataProvider->getData($queryAdapter->getQuery());
        foreach ($dataList as $itemData) {
            $result->add($itemData);
        }

        return $result;
    }

    /**
     * @param  array                     $params
     * @param  UserContextInterface|null $userContext
     * @return int
     */
    public function getCount(array $params, UserContextInterface $userContext = null): int
    {
        $queryAdapter = BxQueryAdapter::initFromArray($params);
        return $this->dataProvider->getDataCount($queryAdapter->getQuery());
    }

    /**
     * @return DataProviderInterface
     */
    public function getDataProvider(): DataProviderInterface
    {
        return $this->dataProvider;
    }

    /**
     * @param     mixed                     $id
     * @param     UserContextInterface|null $userContext
     * @return    AbsOptimizedModel|null
     * @deprected
     * @see       BaseModelService::getByPk($pkValue, UserContextInterface $userContext = null)
     */
    public function getById($id, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        $pkName = $this->dataProvider->getPkName() ?? 'ID';
        $model =  $this->getList(['filter' => ['=' . $pkName => $id]], $userContext)->first();
        return $model instanceof AbsOptimizedModel ? $model : null;
    }

    /**
     * @param  mixed $pkValue
     * @param  UserContextInterface|null $userContext
     * @return AbsOptimizedModel|null
     */
    public function getByPk($pkValue, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        $pkName = $this->dataProvider->getPkName() ?? 'ID';
        $query = new QueryCriteria();
        $query->addCriteria($pkName, CompareRuleInterface::EQUAL, $pkValue);
        $itemData = current($this->dataProvider->getData($query));
        if (empty($itemData)) {
            return null;
        }

        /**
         * @var string $className
         */
        $className = $this->getModelClass();
        /**
         * @var            AbsOptimizedModel $model
         * @psalm-suppress InvalidStringClass
         */
        $model = new $className($itemData);

        return $model;
    }

    /**
     * @param DerivativeModelInterface|string $class
     * @param array|null $filter
     * @param array|null $sort
     * @param int|null $limit
     * @param array|null $runtime
     * @return         ModelCollectionInterface
     * @psalm-suppress InvalidStringClass,MismatchingDocblockParamType
     */
    public function getModelCollection(
        string $class,
        array $filter = null,
        array $sort = null,
        int $limit = null,
        ?array $runtime = null
    ): ModelCollectionInterface {
        $params = [];
        if (!empty($filter)) {
            $params['filter'] = $filter;
        }

        if (!empty($sort)) {
            $params['order'] = $sort;
        }

        if (!empty($limit)) {
            $params['limit'] = $limit;
        }

        if (!empty($runtime)) {
            $params['runtime'] = $runtime;
        }

        $select = $class::getSelect();
        if (!empty($select)) {
            $params['select'] = $select;
        }

        $fetchNamesList = $class::getFetchNamesList();
        if (is_array($fetchNamesList)) {
            $params['fetch'] = $fetchNamesList;
        }

        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        $collection = new ModelCollection([], $class);
        $originalCollection = $this->getList($params);
        foreach ($originalCollection as $originalModel) {
            $collection->append($class::init($originalModel));
        }

        $fetchList = $class::getFetchList();
        foreach ($fetchList as $fetcher) {
            if ($fetcher instanceof FetcherModelInterface) {
                $fetcher->fill($collection);
            }
        }

        return $collection;
    }

    public function query(UserContextInterface $userContext = null): ModelQueryInterface
    {
        return new ExtendedQueryModel($this, $userContext);
    }

    /**
     * @param DerivativeModelInterface $class
     * @param array|null $filter
     * @param array|null $sort
     * @param int|null $limit
     * @param int|null $offset
     * @param UserContextInterface|null $userContext
     * @param array|null $runtime
     * @return ModelCollectionInterface
     */
    public function getModelCollectionByUserContext(
        string $class,
        array $filter = null,
        array $sort = null,
        int $limit = null,
        int $offset = null,
        ?UserContextInterface $userContext = null,
        ?array $runtime = null
    ): ModelCollectionInterface {
        $params = [];
        if (!empty($filter)) {
            $params['filter'] = $filter;
        }

        if (!empty($sort)) {
            $params['order'] = $sort;
        }

        if (!empty($limit)) {
            $params['limit'] = $limit;
        }

        if (!empty($offset)) {
            $params['offset'] = $offset;
        }

        if (!empty($runtime)) {
            $params['runtime'] = $runtime;
        }

        $select = $class::getSelect();
        if (!empty($select)) {
            $params['select'] = $select;
        }

        $fetchNamesList = $class::getFetchNamesList();
        if (is_array($fetchNamesList)) {
            $params['fetch'] = $fetchNamesList;
        }

        /**
         * @psalm-suppress PossiblyInvalidArgument
         */
        $collection = new ModelCollection([], $class);
        $originalCollection = $this->getList($params, $userContext);
        foreach ($originalCollection as $originalModel) {
            $collection->append($class::init($originalModel));
        }

        $fetchList = $class::getFetchList();
        foreach ($fetchList as $fetcher) {
            if ($fetcher instanceof FetcherModelInterface) {
                $fetcher->fill($collection);
            }
        }

        return $collection;
    }
}
