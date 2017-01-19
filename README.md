# Custom Redis driver for Stash

Due to issues with Predis Stash driver, this bundle provides an adjusted implementation for the Predis driver.

## Installation and usage

### Step 1

Add repository of the bundle to `composer.json`:

```
{

    ...
  
    "repositories": [
        { "type": "vcs", "url": "https://github.com/inviqa/stash-redis-driver-bundle" }
    ],
      
    ...
}
```

### Step 2

Install the package:

```
$ composer require inviqa/stash-redis-driver-bundle
```

### Step 3

Add package to `AppKernel.php`:

```
...

    public function registerBundles()
        {
            $bundles = array(
                ...
                new Inviqa\StashRedisDriverBundle\StashRedisDriverBundle(),
                ...
        );

...
```

This will replace default Redis driver with the custom implementation.

### Authors

Developed and maintained by [Inviqa](https://inviqa.com). Original implementation by [Samuel Roze](https://github.com/sroze),
adjustments for latest Predis and Stash version by [David Lukac](https://github.com/davidlukac).
