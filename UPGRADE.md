# Upgrade guide

## From 2.2.* to 2.3.0

### Dropped support for PHP 8.3

Upgrade to at least PHP 8.4.

## From 2.1.* to 2.2.0

Nothing to do.

## From 2.0.* to 2.1.0

Nothing to do.

## From 1.4.* to 2.0.0

### Switched away from custom normalizers

Switched away from custom normalizers to the normalizers from `digital-craftsman/self-aware-normalizers`.

The new normalizers are automatically registered and will handle the normalization of ids and id lists. When you've configured the `IdNormalizer` or `IdListNormalizer` manually somewhere, you need to replace them with the `StringNormalizableNormalizer` and `ArrayNormalizableNormalizer` respectively.

The `Id` and `IdList` classes now contain a `normalize` and `denormalize` methods. So if you've implemented those methods in your classes, you need to rename yours to something else.

### Removed custom doctrine type for IdList

There is no need for the custom doctrine type for `IdList` anymore. Instead, extend your doctrine types from `ArrayNormalizableType`. As the id list knows which id class to construct, the doctrine type doesn't need the `getIdClass` method anymore.

```php
use DigitalCraftsman\Ids\Doctrine\IdListType;

final class UserIdListType extends IdListType
{
    public static function getTypeName(): string
    {
        return 'user_id_list';
    }

    public static function getClass(): string
    {
        return UserIdList::class;
    }
    
    public static function getIdClass(): string
    {
        return UserId::class;
    }
}
```

After:

```php
use DigitalCraftsman\SelfAwareNormalizers\Doctrine\ArrayNormalizableType;

final class UserIdListType extends ArrayNormalizableType
{
    public static function getTypeName(): string
    {
        return 'user_id_list';
    }

    public static function getClass(): string
    {
        return UserIdList::class;
    }
}
```

### Upgrade to at least PHP 8.3

Support for PHP 8.2 was dropped, so you have to upgrade to at least PHP 8.3.

## From 1.3.* to 1.4.0

Nothing to do.

## From 1.2.* to 1.3.0

Nothing to do.

## From 1.2.0 to 1.2.1

- Run Psalm to see if the new annotations find any issues know visible (make sure that all your `Id` and `IdList` classes are `final`).

## From 1.1.0 to 1.2.0

Nothing to do.

## From 1.0.0 to 1.1.0

Nothing to do.

## From 0.15.* to 1.0.0

Nothing to do.

## From 0.14.* to 0.15.0

Nothing to do.

## From 0.13.* to 0.14.0

### Converted database column type from `JSON` to `JSONB` for `IdListType`

The database column type for `IdListType` was converted from `JSON` to `JSONB` to improve comparison performance and enable index creation. The migration for existing lists need to be created manually as Doctrine doesn't realize the changes (as `JSONB` is just an option for `JSON` in doctrine).

Search your project for usages of the id lists and create a migration to set the column type to `JSONB` for the id lists. A migration query could look like this:

```sql
ALTER TABLE project ALTER COLUMN ids_of_users_with_access SET DATA TYPE JSONB USING ids_of_users_with_access::jsonb;
```

## From 0.12.* to 0.13.0

Nothing to do.

## From 0.11.* to 0.12.0

Nothing to do.

## From 0.10.* to 0.11.0

### Changed abstract methods of types

They are now `public` and `static` to enable automatic registration.
And the function `getIdListClass` from `IdListType` was renamed to `getClass`.

Before:

```php
final class UserIdType extends IdType
{
    protected function getTypeName(): string
    {
        return 'user_id';
    }

    protected function getClass(): string
    {
        return UserId::class;
    }
}
```

After:

```php
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

Before:

```php
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

After:

```php
final class UserIdListType extends IdListType
{
    public static function getTypeName(): string
    {
        return 'user_id_list';
    }

    public static function getClass(): string
    {
        return UserIdList::class;
    }

    public static function getIdClass(): string
    {
        return UserId::class;
    }
}
```

### Removed `OrderedIdList`

The `OrderedIdList` was removed. If you need an ordered list, you can copy it from the previous version and manage it yourself.

## From 0.9.* to 0.10.0

### Dropped support for Symfony below 6.3

Support for Symfony below 6.3 was dropped, so you have to upgrade to at least Symfony 6.3. This is the only way to prevent deprecations from being thrown for the cachable support.

## From 0.8.* to 0.9.0

### Upgrade to at least PHP 8.2

Support for PHP 8.1 was dropped, so you have to upgrade to at least PHP 8.2.

### Switch your `Id` and `IdList` classes to `readonly`

The `Id` and `IdList` classes are now `readonly`. This means you need to add the `readonly` keyword to your classes that extend from them. You can remove `@psalm-immutable` annotations from your classes.

Before:

```php
/** @psalm-immutable **/
final class UserId extends Id
{
}

/**
 * @psalm-immutable 
 * @extends IdList<UserId> 
 */
final class UserIdList extends IdList
{
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}
```

After:

```php
final readonly class UserId extends Id
{
}

/** @extends IdList<UserId> */
final readonly class UserIdList extends IdList
{
    public static function handlesIdClass(): string
    {
        return UserId::class;
    }
}
```

## From 0.7.* to 0.8.0

### Updated behaviour of `IdList::removeId`

The `removeId` method of `IdList` now behaves like the `addId` method in that it throws an exception (`IdListDoesNotContainId`) when the id that has to be removed, doesn't exist in the list. Use the new `removeIdWhenInList` method if you want to remove an id without caring whether it's in the list or not.

### Internal ids of `IdList` now use the string representation of an id as key instead of the index

This greatly improves performance of large lists. If you still need them with an index, or ordered, you can use the new `OrderedIdList` for your id list classes. Those lists won't benefit from the performance improvement though.

The method `isInSameOrder` was removed from `IdList` but is still available on the `OrderedIdList`.

## From 0.6.* to 0.7.0

### Updated behaviour of `IdList::diff`

The `diff` method of `IdList` now behaves like `array_diff`.

Consider implementing the previous behavior in your codebase if you have used `diff`.

## From 0.4.* to 0.5.0

### Reduced visibility

Reduced visibility of internal methods `idAtPosition`, `mustNotContainDuplicateIds` and `mustOnlyContainIdsOfHandledClass` of `IdList` from `public` to `private`.

Remove your usages of those methods if you're using them somewhere.

## From 0.3.* to 0.4.0

### Upgrade to at least PHP 8.1

Support for PHP 8.0 was dropped, so you have to upgrade to at least PHP 8.1.

## From 0.2.* to 0.3.0

### Removed methods `isExistingInList` and `isNotExistingInList`

The owning side for this method is the list, so it doesn't make sense to have it on the id. There already is a `containsId` method on the list which must be used instead.

Before:

```php
$idsOfUsersWithAccess = new UserIdList([
    $project->idOfUserWithAccess,
    $company->idOfUserWithAccess,
]);

if ($command->userId->isExistingInList($idsOfUsersWithAccess)) {
    ...
```

After:

```php
$idsOfUsersWithAccess = new UserIdList([
    $project->idOfUserWithAccess,
    $company->idOfUserWithAccess,
]);

if ($idsOfUsersWithAccess->containsId($command->userId)) {
    ...
```

## From 0.1.* to 0.2.0

### Id list parameter for `isExistingInList` and `isNotExistingInList`

Previously the parameter was `array<int, Id> $list`. Now it's `IdList $list`.

Before:

```php
$idsOfUsersWithAccess = [
    $project->idOfUserWithAccess,
    $company->idOfUserWithAccess,
];

if ($command->userId->isExistingInList($idsOfUsersWithAccess)) {
    ...
```

After:

```php
$idsOfUsersWithAccess = new UserIdList([
    $project->idOfUserWithAccess,
    $company->idOfUserWithAccess,
]);

if ($command->userId->isExistingInList($idsOfUsersWithAccess)) {
    ...
```
