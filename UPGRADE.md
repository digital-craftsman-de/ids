# Upgrade guide

## From 0.4.* to 0.5.0

### Reduced visibility

Reduced visibility of internal methods `idAtPosition`, `mustNotContainDuplicateIds` and `mustOnlyContainIdsOfHandledClass` of `IdList` from `public` to `private`.

You need to remove your usages of those methods if you're using them somewhere. Let me know if they are important to you with your use case.

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
