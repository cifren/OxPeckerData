Installation
============

Add the bunde to your `composer.json` file:
```json
require: {
    // ...
    "earls/oxpecker-data-bundle": "dev-master",
    "knplabs/etl": "0.1.*@dev"
    // ...
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/earls/OxPeckerDataBundle.git"
    }
]
```

Then run a `composer update`:
```shell
composer.phar update
# OR
composer.phar update earls/oxpecker-data-bundle # to only update the bundle
```

Register the bundle with your `kernel`:
```php
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Earls\OxPeckerDataBundle\EarlsOxPeckerDataBundle(),
    // ...
);
```