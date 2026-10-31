# Ids – Typed id value objects for Symfony

Stop passing ids around as plain strings. This bundle lets you create a dedicated, validated value object for every kind of id (`UserId`, `OrderId`, …) and lists of them (`UserIdList`, …), which work out of the box with the Symfony serializer and Doctrine.

As it's a central part of an application, it's tested thoroughly (including mutation testing).

[![Latest Stable Version](https://img.shields.io/badge/stable-2.5.0-blue)](https://packagist.org/packages/digital-craftsman/ids)
[![PHP Version Require](https://img.shields.io/badge/php-8.5|8.6-5b5d95)](https://packagist.org/packages/digital-craftsman/ids)
[![codecov](https://codecov.io/gh/digital-craftsman-de/ids/branch/main/graph/badge.svg?token=BL0JKZYLBG)](https://codecov.io/gh/digital-craftsman-de/ids)
![Packagist Downloads](https://img.shields.io/packagist/dt/digital-craftsman/ids)
![Packagist License](https://img.shields.io/packagist/l/digital-craftsman/ids)

## Why?

When ids are plain strings, nothing stops you from mixing them up:

```php
// Which string is which? Neither PHP nor your static analysis can tell.
public function grantAccess(string $userId, string $projectId): void

$service->grantAccess($projectId, $userId); // Compiles, runs, corrupts data.
```

With dedicated id classes, the same mistake is caught right away:

```php
public function grantAccess(UserId $userId, ProjectId $projectId): void

$service->grantAccess($projectId, $userId); // TypeError
```

On top of that, you get:

- **Validation:** An id can only be constructed from a valid UUID. Invalid input never makes it into your domain.
- **Expressive code:** Comparisons and guards like `$userId->mustNotBeEqualTo($targetUserId)` instead of string checks spread through the code.
- **Id lists:** Immutable, typed collections of ids with set operations (`containsId`, `diff`, `intersect`, …) and guards. A `UserIdList` can only contain `UserId`s and never contains duplicates.
- **Serializer support:** Ids are normalized to strings and id lists to arrays of strings – without writing a single normalizer.
- **Doctrine support:** Ids and id lists can be used directly as column types – without writing a single Doctrine type.

The serializer and Doctrine integration is provided by [`digital-craftsman/self-aware-normalizers`](https://github.com/digital-craftsman-de/self-aware-normalizers), which is installed as a dependency.

### Why not `ramsey/uuid` or `symfony/uid`?

Both are great libraries to work with UUIDs, but they aren't built to be the ids of your domain:

- **Performance:** `ramsey/uuid` resolves factories, generators and codecs and builds a whole object graph for every single UUID. For plain v4 UUIDs, which is what most applications need, that's a lot of overhead. This package only creates and validates a string through the `uuid` extension (or the polyfill) and wraps it in one small `readonly` object. That difference adds up quickly when thousands of ids are created or denormalized in a single request.
- **Custom ids:** Both libraries model *a UUID*, not *the id of a user*. To get a `UserId`, you'd have to wrap or extend their classes, and their static factories, normalizers and Doctrine types still only know about the generic UUID classes. Here, a typed id is a one-liner and everything else works with it automatically.
- **Id lists:** Neither library has a concept of lists of ids. You end up with `array<UuidInterface>`, which allows duplicates, mixes different kinds of ids and has no set operations or guards.

You can still use `symfony/uid` or `ramsey/uuid` just to *generate* other UUID versions, for example v7. See [Use other UUID versions](./docs/use-other-uuid-versions.md).

## Installation

Requires PHP 8.5 or 8.6, Symfony 7.4 or 8 and Doctrine DBAL 4.

```shell
composer require digital-craftsman/ids
```

It's recommended to install the [`uuid` PHP extension](https://pecl.php.net/package/uuid) for better performance of id creation and validation. `symfony/polyfill-uuid` is used as a fallback. When the extension is installed, you can [prevent installing the polyfill](./docs/prevent-polyfill-usage.md).

### Configuration

The serializer works without any configuration. For the Doctrine types, add the directories containing your ids and id lists to `config/packages/self-aware-normalizers.php`:

```php
<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'self_aware_normalizers' => [
        'implementation_directories' => [
            '%kernel.project_dir%/src/ValueObject',
        ],
    ],
]);
```

All ids and id lists in these directories are registered as Doctrine types automatically.

## Ids

### Defining an id

Extend from `Id`. That's all:

```php
<?php

declare(strict_types=1);

namespace App\ValueObject;

use DigitalCraftsman\Ids\ValueObject\Id;

final readonly class UserId extends Id
{
}
```

### Using an id

```php
// Create
$userId = UserId::generateRandom();
$userId = UserId::fromString('15d6208b-7cf2-49e5-a193-301d594d98a7'); // Throws InvalidId for invalid UUIDs

// Read
$userId->value;      // '15d6208b-7cf2-49e5-a193-301d594d98a7'
$userId->toString(); // Same, also available through (string) $userId

// Compare
if ($userId->isEqualTo($command->userId)) {
    // ...
}
```

### Guards

Guards throw an exception when the condition isn't met:

```php
$requestingUser->userId->mustNotBeEqualTo($command->targetUserId);
```

Every guard accepts an optional callable to throw a custom exception instead:

```php
$requestingUser->userId->mustNotBeEqualTo(
    $command->targetUserId,
    static fn () => new Exception\UserCanNotTargetItself(),
);
```

### Serializer

Ids are normalized to their string value, no matter how deeply they are nested:

```php
final readonly class UserPayload
{
    public function __construct(
        public UserId $userId,
        public string $firstName,
        public string $lastName,
    ) {
    }
}

$serializer->serialize($userPayload, JsonEncoder::FORMAT);
```

```json
{
  "userId": "15d6208b-7cf2-49e5-a193-301d594d98a7",
  "firstName": "Tomas",
  "lastName": "Bauer"
}
```

Denormalization works the same way in reverse. This also makes ids usable in commands and queries of the [CQS routing bundle](https://github.com/digital-craftsman-de/cqs-routing).

### Doctrine

Use the class name as column type. Ids are stored as `guid` (`uuid` in PostgreSQL):

```php
#[ORM\Entity]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: UserId::class)]
    public UserId $userId;
}
```

## Id lists

### Defining an id list

Extend from `IdList` and define which id class it contains:

```php
<?php

declare(strict_types=1);

namespace App\ValueObject;

use DigitalCraftsman\Ids\ValueObject\IdList;

/** @extends IdList<UserId> */
final readonly class UserIdList extends IdList
{
    #[\Override]
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}
```

An id list only accepts ids of this class and never contains the same id twice.

### Creating an id list

```php
$userIds = new UserIdList([$userId1, $userId2]);
$userIds = UserIdList::emptyList();
$userIds = UserIdList::fromIdStrings(['15d6208b-…', 'a4f3c1e2-…']);
$userIds = UserIdList::fromMap($users, static fn (User $user) => $user->userId);
$userIds = UserIdList::fromIdLists([$admins, $editors]); // Merged, without duplicates
```

### Changing an id list

Id lists are immutable. Every change returns a new list:

```php
$userIds = $userIds->addId($userId);          // Throws when already in list
$userIds = $userIds->addIdWhenNotInList($userId);
$userIds = $userIds->removeId($userId);       // Throws when not in list
$userIds = $userIds->removeIdWhenInList($userId);

$userIds = $userIds->addIds($otherUserIds);   // Same variants exist for lists
$userIds = $userIds->diff($otherUserIds);
$userIds = $userIds->intersect($otherUserIds);
$userIds = $userIds->filter(static fn (UserId $userId) => ...);
```

These methods are marked with `#[\NoDiscard]`, so PHP warns you when you forget to use the returned list.

### Reading an id list

```php
$idsOfEnabledUsers->containsId($command->userId);
$idsOfEnabledUsers->containsEveryId($userIds);
$idsOfEnabledUsers->containsSomeIds($userIds);
$idsOfEnabledUsers->containsNoneIds($userIds);
$idsOfEnabledUsers->isEqualTo($userIds); // Same ids, independent of order
$idsOfEnabledUsers->isEmpty();
count($idsOfEnabledUsers);
$idsOfEnabledUsers->idsAsStringList();   // ['15d6208b-…', …]

foreach ($idsOfEnabledUsers as $userId) {
    // ...
}

$names = $idsOfEnabledUsers->map(static fn (UserId $userId) => $userRepository->getName($userId));
```

Additionally, there are `notContainsId`, `isNotEqualTo`, `isNotEmpty`, `mapWithIdKeys`, `every`, `some` and `reduce`.

### Guards

As with ids, every check is also available as guard, optionally with a custom exception:

```php
$idsOfEnabledUsers->mustContainId($command->targetUserId);

$idsOfEnabledUsers->mustContainId(
    $command->targetUserId,
    static fn () => new Exception\UserIsNotEnabled(),
);
```

Available guards: `mustContainId`, `mustNotContainId`, `mustContainEveryId`, `mustNotContainEveryId`, `mustContainSomeIds`, `mustContainNoneIds`, `mustBeEmpty`, `mustNotBeEmpty`, `mustBeEqualTo` and `mustNotBeEqualTo`.

### Serializer

Id lists are normalized to a list of id strings:

```json
{
  "idsOfUsersWithAccess": [
    "15d6208b-7cf2-49e5-a193-301d594d98a7",
    "a4f3c1e2-4b8d-4f6a-9c2e-7d1b3e5f8a90"
  ]
}
```

### Doctrine

Use the class name as column type. Id lists are stored as `jsonb` in PostgreSQL and `json` on other platforms:

```php
#[ORM\Entity]
#[ORM\Table(name: 'project')]
class Project
{
    #[ORM\Column(name: 'ids_of_users_with_access', type: UserIdList::class)]
    public UserIdList $idsOfUsersWithAccess;
}
```

## Additional documentation

- [Prevent polyfill usage](./docs/prevent-polyfill-usage.md)
- [Use other UUID versions](./docs/use-other-uuid-versions.md)
- [Changelog](./CHANGELOG.md)
- [Upgrade guide](./UPGRADE.md)
