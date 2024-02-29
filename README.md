# Вспомогательная библиотека для описания сервисов bx.model

## Установка
```
composer require beta/bx.model.service.ext
```

*Пример описания сервиса для работы в режиме чтения (без внешних связей)*

```php
use Bx\Model\Models\User;
use Bx\Model\Service\Ext\Traits\SimpleServiceHelper;

class SomeROSimpleClass extends BaseROModelService
{
    use SimpleServiceHelper;

    protected function getModelClass(): string
    {
        // Используемый класс модели
        return User::class;
    }

    protected function getDefaultSelect(): array
    {
        // Поля выбираемые по-умолчанию
        return [
            'ID',
            'ACTIVE',
            'EMAIL',
            'NAME',
            'LAST_NAME',
            'SECOND_NAME'
        ];
    }

    protected static function getFilterFields(): array
    {
        // Разрешенные поля для фильтрации (через query builder)
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }

    static protected function getSortFields(): array
    {
        // Разрешенные поля для сортировки (через query builder)
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }
}
```

*Пример инициализации сервиса*

```php
use Bitrix\Main\UserTable;
use BX\Data\Provider\DataManagerDataProvider;

$userDataProvider = new DataManagerDataProvider(UserTable::class);
$userRepository = new SomeROSimpleClass($userDataProvider);
$userCollection = $userRepository->getList(['filter' => ['ACTIVE' => 'Y'], 'limit' => 10]);
```

*Пример описания сервиса для работы в режиме чтения (с внешними связями)*

```php
use Bx\Model\FetcherModel;
use Bx\Model\Interfaces\FileServiceInterface;
use Bx\Model\Models\User;
use Bx\Model\Service\Ext\Traits\LinkedServiceHelper;
use Data\Provider\Interfaces\DataProviderInterface;

class SomeROLinkedClass extends BaseROModelService
{
    use LinkedServiceHelper;    // трейт для выборки связанных моделей

    private FileServiceInterface $fileService;

    public function __construct(DataProviderInterface $dataProvider, FileServiceInterface $fileService)
    {
        $this->fileService = $fileService;
        parent::__construct($dataProvider);
    }

    protected function getModelClass(): string
    {
        // Используемый класс модели
        return User::class;
    }

    protected function getDefaultSelect(): array
    {
        // Поля выбираемые по-умолчанию
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
        // Разрешенные поля для фильтрации (через query builder)
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }

    static protected function getSortFields(): array
    {
        // Разрешенные поля для сортировки (через query builder)
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
```

*Пример инициализации сервиса*

```php
use Bitrix\Main\UserTable;
use BX\Data\Provider\DataManagerDataProvider;
use Bx\Model\Services\FileService;
use Bx\Model\Models\File;

$userDataProvider = new DataManagerDataProvider(UserTable::class);
$userRepository = new SomeROLinkedClass($userDataProvider, new FileService());
$userCollection = $userRepository->getList(['filter' => ['ACTIVE' => 'Y'], 'limit' => 10]);
$firstUser = $userCollection->first();
/**
* @var File $firstUserPhoto
 */
$firstUserPhoto = $firstUser['photo'];
echo $firstUserPhoto->getSrc(); // /upload/*.jpg
```

*Пример описания сервиса для работы в режиме чтения и записи (с внешними связями)*

```php
use Bitrix\Main\Type\Date;
use Bx\Model\FetcherModel;
use Bx\Model\Interfaces\FileServiceInterface;
use Bx\Model\Models\User;
use Bx\Model\Service\Ext\Traits\LinkedServiceHelper;
use Bx\Model\Service\Ext\Traits\SaveModelHelperWithReload;
use Data\Provider\Interfaces\DataProviderInterface;

class SomeLinkedClass extends BaseModelService
{
    use LinkedServiceHelper;
    use SaveModelHelperWithReload; // после сохранения обновляет исходную модель данными из БД

    private FileServiceInterface $fileService;

    public function __construct(DataProviderInterface $dataProvider, FileServiceInterface $fileService)
    {
        $this->fileService = $fileService;
        parent::__construct($dataProvider);
    }

    protected function getModelClass(): string
    {
        // Используемый класс модели
        return User::class;
    }

    protected function getDefaultSelect(): array
    {
        // Поля выбираемые по-умолчанию
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
        // Разрешенные поля для фильтрации (через query builder)
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }

    static protected function getSortFields(): array
    {
        // Разрешенные поля для сортировки (через query builder)
        return [
            'id' => 'ID',
            'name' => 'NAME',
            'email' => 'EMAIL',
        ];
    }

    protected function getDataMapForSave(): array
    {
        // Поля для сохранения
        return [
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
            'PERSONAL_PHOTO',
            'birthday' => 'PERSONAL_BIRTHDAY', // поле модели birthday для сохранения в поле PERSONAL_BIRTHDAY провайдера данных
        ];
    }

    protected function getCompareClassMapForSave(): array
    {
        // тут описываются сопоставления полей содержащие в качестве значений объекты с классами
        return [
            'PERSONAL_BIRTHDAY' => Date::class
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
```