# Changelog

## 0.3.1

- The `IdList` now implements the `\IteratorAggregate` instead the `\Iterator` interface to fix comparing lists in tests with equal checks.  

## 0.3.0

- **[Breaking change](./UPGRADE.md#removed-methods-isexistinginlist-and-isnotexistinginlist)**: The methods `isExistingInList` and `isNotExistingInList` have been removed. The `containsId` method of the list class must be used instead.

## 0.2.0

- **[Breaking change](./UPGRADE.md#id-list-parameter-for-isexistinginlist-and-isnotexistinginlist)**: The methods `isExistingInList` and `isNotExistingInList` now expect an id list as parameter instead of an array of ids.
- Id list now supports ids of id subclass.

## 0.1.0

- Initial release
