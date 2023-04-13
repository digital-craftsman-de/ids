# Upgrade guide

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
