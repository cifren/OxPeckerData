Add the bunde to your `composer.json` file:
```json
require: {
    // ...
    "cifren/oxpecker-data-bundle": "dev-master",
    "knplabs/etl": "0.1.*@dev"
    // ...
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/cifren/OxPeckerData.git"
    }
]
```

Then run a `composer update`:
```shell
composer.phar update
# OR
composer.phar update cifren/oxpecker-data # to only update the bundle
```

Register the bundle with your `kernel`:
```php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Cifren\OxPeckerDataBundle\CifrenOxPeckerDataBundle(),
    // ...
);
```
