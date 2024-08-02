# Id handling with value objects in Symfony

A Symfony bundle to work with id and id list value objects in Symfony. It includes Symfony normalizers for automatic normalization and denormalization and Doctrine types to store the ids and id lists directly in the database.  

As it's a central part of an application, it's tested thoroughly (including mutation testing).

[![Latest Stable Version](http://poser.pugx.org/digital-craftsman/ids/v)](https://packagist.org/packages/digital-craftsman/ids)
[![PHP Version Require](http://poser.pugx.org/digital-craftsman/ids/require/php)](https://packagist.org/packages/digital-craftsman/ids)
[![codecov](https://codecov.io/gh/digital-craftsman-de/ids/branch/main/graph/badge.svg?token=BL0JKZYLBG)](https://codecov.io/gh/digital-craftsman-de/ids)
[![Total Downloads](http://poser.pugx.org/digital-craftsman/ids/downloads)](https://packagist.org/packages/digital-craftsman/ids)
[![License](http://poser.pugx.org/digital-craftsman/ids/license)](https://packagist.org/packages/digital-craftsman/ids)

## Installation and configuration

Install package through composer:

```shell
composer require digital-craftsman/ids
```

It's recommended that you install the [`uuid` PHP extension](https://pecl.php.net/package/uuid) for better performance of id creation and validation.  `symfony/polyfill-uuid` is used as a fallback. You can [prevent installing the polyfill](./docs/prevent-polyfill-usage.md) when you've installed the PHP extension.

## Working with ids

### Creating a new id

The bulk of the logic is in the `Id` class. Creating a new id is as simple as creating a new `final readonly class` and extending from it like the following:

```php
<?php

declare(strict_types=1);

namespace App\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Id;

final readonly class UserId extends Id
{
}
```

Now you're already able to use it in your code like this:

```php
$userId = UserId::generateRandom();
```

```php
if ($userId->isEqualTo($command->userId)) {
    ...
}
```

Guard against invalid usages:
```php
$requestingUser->userId->mustNotBeEqualTo($command->targetUserId);
```

Or with a custom exception:
```php
$requestingUser->userId->mustNotBeEqualTo(
    $command->targetUserId,
    static fn () => new Exception\UserCanNotTargetItself(),
);
```

### Symfony serializer

If you're injecting the `SerializerInterface` directly, there is nothing to do. The normalizer for the id is automatically registered.

```php
namespace App\DTO;

final readonly class UserPayload
{
    public function __construct(
        public UserId $userId,
        public string $firstName,
        public string $lastName,
    ) {
    }
}
```

```php
public function __construct(
    private SerializerInterface $serializer,
) {
}

public function handle(UserPayload $userPayload): string
{
    return $this->serializer->serialize($userPayload, JsonEncoder::FORMAT);
}
```

```json
{
  "userId": "15d6208b-7cf2-49e5-a193-301d594d98a7",
  "firstName": "Tomas",
  "lastName": "Bauer"
}
```

This can be combined with the [CQRS bundle](https://github.com/digital-craftsman-de/cqrs) to have serialized ids there.

### Doctrine types

To use an id in your entities, you just need to register a new type for the id. Create a new class for the new id like the following:

```php
<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\ValueObject\UserId;
use DigitalCraftsman\Ids\Doctrine\IdType;

final class UserIdType extends IdType
{
    public static function getTypeName(): string
    {
        return 'user_id';
    }

    public static function getClass(): string
    {
        return UserId::class;
    }
}
```

Then register the new type in your `config/packages/doctrine.yaml` file:

```yaml
doctrine:
  dbal:
    types:
      user_id: App\Doctrine\UserIdType
```

Alternatively you can also add a compiler pass to [register the types automatically](https://blog.digital-craftsman.de/automatically-register-doctrine-types-in-symfony-with-compiler-pass/).

Then you're already able to add it into your entity like this:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'user_id')]
    public UserId $id;
    
    ...
}
```

## Working with id lists

Id lists are wrapper for an array of ids. They contain a few utility functions and improved type safety.

The `IdList` is immutable. Therefore, the mutation methods (like `add`, `remove`, ...) always return a new instance of the list.

### Creating a new id list

The bulk of the logic is in the `IdList` class. Creating a new id list is as simple as creating a new `final readonly class` and extending from it like the following:

```php
<?php

declare(strict_types=1);

namespace App\ValueObject;

use DigitalCraftsman\Ids\ValueObject\IdList;

/** @extends IdList<UserId> */
final readonly class UserIdList extends IdLIst
{
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}
```

Now you're already able to use it in your code like this:

```php
$userIdList = new UserIdList($userIds);
```

```php
if ($idsOfEnabledUsers->contains($command->userId)) {
    ...
}
```

Guard against invalid usages:
```php
$idsOfEnabledUsers->mustContainId($command->targetUserId);
```

Or with custom exception:
```php
$idsOfEnabledUsers->mustContainId(
    $command->targetUserId,
    static fn () => new Exception\UserIsNotEnabled(),
);
```

### Symfony serializer

If you're injecting the `SerializerInterface` directly, there is nothing to do. The normalizer for the id list is automatically registered.

### Doctrine types

To use an id list in your entities, you just need to register a new type for the id list. Create a new class for the new id list like the following:

```php
<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\ValueObject\UserId;
use App\ValueObject\UserIdList;
use DigitalCraftsman\Ids\Doctrine\IdListType;

final class UserIdListType extends IdListType
{
    protected function getTypeName(): string
    {
        return 'user_id_list';
    }

    protected function getIdListClass(): string
    {
        return UserIdList::class;
    }
    
    protected function getIdClass(): string
    {
        return UserId::class;
    }
}
```

Then register the new type in your `config/packages/doctrine.yaml` file:

```yaml
doctrine:
  dbal:
    types:
      user_id_list: App\Doctrine\UserIdListType
```

Then you're already able to add it into your entity like this:

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObject\UserIdList;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvestorRepository::class)]
#[ORM\Table(name: 'investor')]
class Investor
{
    #[ORM\Column(name: 'ids_of_users_with_access', type: 'user_id_list')]
    public UserIdList $idsOfUsersWithAccess;
    
    ...
}
```

## Additional documentation

- [Prevent polyfill usage](./docs/prevent-polyfill-usage.md)
- [Use other UUID versions](./docs/use-other-uuid-versions.md)
- [Changelog](./CHANGELOG.md)
- [Upgrade guide](./UPGRADE.md)
