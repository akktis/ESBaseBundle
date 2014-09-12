
## Mailer:

### Configuration

Verify your `swiftmailer` configuration:

```yaml
swiftmailer:
    transport: "%mailer_transport%"
    host:      "%mailer_host%"
    port:      "%mailer_port%"
    username:  "%mailer_user%"
    password:  "%mailer_password%"
    spool:     { type: memory }
```

Enable the mail and set the required `sender_address` parameter.

```yaml
# /app/config/config.yml
es_base:
    # ...
    mailer:
        sender_address: no-reply@acmedemo.com
```

### Usage

#### Mail template

All parts of the mail are in one template.
In your mail template, the following blocks must be present:
- `subject` Define the subject of the mail (plain text)
- `body_text` The text/plain part of the mail
- `body_html` The text/html part of the mail

This bundle provides a mail layout (`ESBaseBundle:Mail:base.html.twig`) implementing these blocks for you.
You just have to fill the custom and the signature will be appended.

You can use this template and write your mail this way:

```django
{# @AcmeDemoBundle/Resources/views/Mail/my_custom_mail.html.twig #}
{% extends 'ESBaseBundle:Mail:base.html.twig' %}

{% block subject %}
	Welcome!
{% endblock %}

{% block content_text %}
	Hello,

	How are you?
	 \o/
	  |
	 /\

	See you!
{% endblock %}

{% block content_html %}
	<p>Hello,</p>

	<p>How are you?</p>
	<img src="http://path/to/img">
	<hr>
	<p>See you!</p>
{% endblock %}
```

#### Send emails

On the backend side you can use the `es_base.mailer` service:

```php
$mailer = $this->container->get('es_base.mailer');
$mailer->send('AcmeDemoBundle:Mail:my_custom_mail.html.twig',
	'recipient@website.com',
	array('message' => $message)
);
```

You you intend to send a mail to the authenticated user, you should use:

```php
$mailer->sendToUser(
	'AcmeDemoBundle:Mail:my_custom_mail.html.twig',
	array('message' => $message),
	$user
);
```
