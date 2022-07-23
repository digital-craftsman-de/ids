# Remove polyfill

When you've installed the `uuid` extension for PHP, there is no need for installing and loading the `symfony/polyfill-uuid` package. To remove it from the installed and loaded packages, simply add it to your `replace` list in your `composer.json` file like the following:

```json
{
  "replace": {
    "symfony/polyfill-uuid": "*"
  }
}
```
