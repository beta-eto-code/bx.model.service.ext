<?php

namespace Bx\Model\Service\Ext;

use Bx\Model\Interfaces\ModelCollectionInterface;
use Bx\Model\Interfaces\Models\QueryableModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\QueryModel;
class ExtendedQueryModel extends QueryModel
{
    private QueryableModelServiceInterface $modelService;
    private ?UserContextInterface $userContext;

    public function __construct(QueryableModelServiceInterface $modelService, UserContextInterface $userContext = null)
    {
        $this->modelService = $modelService;
        $this->userContext = $userContext;
        parent::__construct($modelService, $userContext);
    }

    public function getModelCollection(string $class): ModelCollectionInterface
    {
        if ($this->modelService instanceof ExtReadableModelServiceInterface) {
            return $this->modelService->getModelCollectionByUserContext(
                $class,
                $this->filter ?: null,
                $this->sort ?: null,
                $this->limit ?: null,
                $this->getOffset() ?: null,
                $this->userContext,
                $this->getRuntimeFields()
            );
        }

        return $this->modelService->getModelCollection(
            $class,
            $this->filter ?: null,
            $this->sort ?: null,
            $this->limit ?: null,
            $this->getRuntimeFields()
        );
    }
}
