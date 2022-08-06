# Upgrade guide

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
