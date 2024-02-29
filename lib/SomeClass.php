<?php

namespace Bx\Model\Service\Ext;

use Bx\Model\FetcherModel;
use Bx\Model\Interfaces\FileServiceInterface;
use Bx\Model\Models\User;
use Bx\Model\Service\Ext\Traits\LinkedServiceHelper;
use Bx\Model\Service\Ext\Traits\SaveModelHelperWithReload;
use Data\Provider\Interfaces\DataProviderInterface;

class SomeClass extends BaseModelService
{
    use LinkedServiceHelper;
    use SaveModelHelperWithReload;

    private FileServiceInterface $fileService;

    public function __construct(DataProviderInterface $dataProvider, FileServiceInterface $fileService)
    {
        $this->fileService = $fileService;
        parent::__construct($dataProvider);
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    protected function getDefaultSelect(): array
    {
        return [
            'ID',
            'ACTIVE',
            'EMAIL',
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
            'PERSONAL_PHOTO',
        ];
    }

    protected static function getFilterFields(): array
    {
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }

    protected function getDataMapForSave(): array
    {
        return [
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
            'PERSONAL_PHOTO'
        ];
    }

    protected function getCompareClassMapForSave(): array
    {
        return [];
    }

    static protected function getSortFields(): array
    {
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }

    protected function getLinkedFields(): array
    {
        return [
            'photo' => FetcherModel::initAsSingleValue(
                $this->fileService,
                'photo',
                'PERSONAL_PHOTO',
                'ID'
            )
        ];
    }
}
