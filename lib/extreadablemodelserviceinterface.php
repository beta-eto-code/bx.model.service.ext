<?php

namespace Bx\Model\Service\Ext;

use Bx\Model\Interfaces\ModelCollectionInterface;
use Bx\Model\Interfaces\UserContextInterface;

interface ExtReadableModelServiceInterface
{
    public function getModelCollectionByUserContext(
        string $class,
        array $filter = null,
        array $sort = null,
        int $limit = null,
        int $offset = null,
        ?UserContextInterface $userContext = null,
        ?array $runtime = null
    ): ModelCollectionInterface;
}
