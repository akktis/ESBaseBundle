
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

## Configure mailer:


```yaml
# /app/config/config.yml

es_base:
    # ...
    mailer:
        sender_address: no-reply@acmedemo.com
```

## Configure contact:

```yaml
# /app/config/config.yml

es_base:
    # ...
    contact: ~
```

All messages are persisted to the database but if you wish to receive contact messages by email,
you can add `deliver_to` option:

```yaml
    contact:
        deliver_to: contact@acmedemo.com
```

## Configure mailer:

```yaml
# /app/config/config.yml

es_base:
    # ...
    mailer:
        sender_address: no-reply@acmedemo.com
```

## TODO

Handle Google Analytics tags shot by other bundles

ex: ESUserBundle registration controller shoot a "register" tag for GA