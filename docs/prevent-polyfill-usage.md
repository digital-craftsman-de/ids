# Remove polyfill

When you've installed the `uuid` PHP extension, there is no reason to install and load the `symfony/polyfill-uuid` package. To remove it from the installed and loaded packages, simply add it to your `replace` list in your `composer.json` file like the following:

```json
{
  "replace": {
    "symfony/polyfill-uuid": "*"
  }
}
```

Just make sure to add `ext-uuid` to your `composer.json` requirements like the following:

```json
{
  "require": {
    "ext-uuid": "*"
  }
}
```
