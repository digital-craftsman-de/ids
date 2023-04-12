# Changelog

## 0.8.0

- **[Breaking change](./UPGRADE.md#updated-behaviour-of-idlistremoveid)**: Updated method `removeId` from `IdList` to throw an exception when the id is not in the list. This is the same behaviour as the `addId` method has.
- Added new method `removeIdWhenInList` to `IdList`.
- Added new `fromIdStrings` named constructor to `IdList`.

## 0.7.0

- **[Breaking change](./UPGRADE.md#updated-behaviour-of-idlistdiff)**: Changed the way `IdList`'s `diff` method behaves to match `array_diff`'s behavior.
  - Previously it returned an `IdList` containing all elements that were present in the `IdList` itself but not in the given `IdList` (method parameter) as well as all elements that were in the given `IdList` (method parameter) but not in the `IdList` itself.
  - Now it returns an `IdList` containing only the elements that are present in the `IdList` itself but not in the given `IdList` (method parameter).

## 0.6.0

- Added `containsEveryId(self $idList): bool` to `IdList`.
- Added `containsSomeIds(self $idList): bool` to `IdList`.
- Added `mustContainEveryId(self $idList): void` to `IdList`.
- Added `mustContainSomeIds(self $idList): void` to `IdList`.

## 0.5.1

- Added PHPStan on level 9 and fixed PHPStan issues.

## 0.5.0

- Reduced visibility of internal methods `idAtPosition`, `mustNotContainDuplicateIds` and `mustOnlyContainIdsOfHandledClass` of `IdList` from `public` to `private`.

## 0.4.0

- Drop support for PHP 8.0.
- Add support for PHP 8.2.

## 0.3.3

- Improve template annotations

## 0.3.2

- Return same instance of list when `addIdWhenNotInList` doesn't mutate the list.

## 0.3.1

- The `IdList` now implements the `\IteratorAggregate` instead the `\Iterator` interface to fix comparing lists in tests with equal checks.  

## 0.3.0

- **[Breaking change](./UPGRADE.md#removed-methods-isexistinginlist-and-isnotexistinginlist)**: The methods `isExistingInList` and `isNotExistingInList` have been removed. The `containsId` method of the list class must be used instead.

## 0.2.0

- **[Breaking change](./UPGRADE.md#id-list-parameter-for-isexistinginlist-and-isnotexistinginlist)**: The methods `isExistingInList` and `isNotExistingInList` now expect an id list as parameter instead of an array of ids.
- Id list now supports ids of id subclass.

## 0.1.0

- Initial release
