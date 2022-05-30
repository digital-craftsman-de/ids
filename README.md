# Id handling with value objects in Symfony

A Symfony bundle to work with id and id list value objects in Symfony. It includes Symfony normalizers for automatic normalization and denormalization and Doctrine types to store the ids and id lists directly in the database.  

As it's a central part of an application, it's tested thoroughly.

[![codecov](https://codecov.io/gh/digital-craftsman-de/ids/branch/main/graph/badge.svg?token=BL0JKZYLBG)](https://codecov.io/gh/digital-craftsman-de/ids)

## Working with ids

### Creating a new id

The bulk of the logic is in the `BaseId` class. Creating a new id is as simple as creating a new class and extending from it like the following:

```php
<?php

declare(strict_types=1);

namespace App\ValueObject;

use DigitalCraftsman\Ids\ValueObject\BaseId;

/** @psalm-immutable */
final class UserId extends BaseId
{
}
```

Now you're already able to use it in your code like this:

```php
$userId = UserId::generateRandom();

if ($userId->isEqualTo($command->userId)) {
    ...
}
```

### Symfony serializer

If you're injecting the `SerializerInterface` directly, there is nothing to do. The normalizer for the id is automatically registered.

```php
public function __construct(
    private SerializerInterface $serializer,
) {
}

public function handle(): void
{
    $this->serializer->serialize(...)
}
```

### Doctrine types

To use an id in your entities, you just need to register a new type for the id. Create a new class for the new id like the following:

```php
<?php

declare(strict_types=1);

namespace App\Doctrine;

use App\ValueObject\UserId;
use DigitalCraftsman\Ids\Doctrine\BaseIdType;

final class UserIdType extends BaseIdType
{
    protected function getTypeName(): string
    {
        return 'user_id';
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
      user_id: App\Doctrine\UserIdType
```

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
