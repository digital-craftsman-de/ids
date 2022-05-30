# Doctrine edge cases

The unit of work of Doctrine doesn't register changes within an object. Therefore, using the `MutableIdList` with it is dangerous. You would need to create a copy after writing to it, so that Doctrine registers the new object and pushes the changes to the database.

I would suggest only using the `IdList` with Doctrine.
