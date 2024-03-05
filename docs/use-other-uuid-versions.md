# Use other UUID versions

By default, this package uses the `create_uuid` function of the `uuid` extension which in returns creates UUIDs with version 4.

When you want other versions like for example 7 (that aren't even supported by the `uuid` extension), you can overwrite the `generateRandom` function of the `Id` class and create them through the `ramsey/uuid` or `symfony/uid` package.

```php
use Symfony\Component\Uid\UuidV7;

final readonly class UserId extends Id
{
    #[\Override]
    public static function generateRandom(): UserId
    {
        $uuid7 = new UuidV7();
        
        return new static($uuid7->toRfc4122());
    }
}
```
