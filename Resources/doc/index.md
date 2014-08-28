
# Base Bundle

- [Assetic with sprites](assetic.md)
- [Staging configuration](staging.md)
- [Google Analytics](google_analytics.md)

## Configure bootstrap bundle

Enable BraincraftedBootstrapBundle:

```php
# app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),
        );
    }
}
```

## Translations

see [JMSTranslationBundle documentation](http://jmsyst.com/bundles/JMSTranslationBundle/master/installation)