
## Load more

### Usage

#### In a controller:

```php
// src/Acme/DemoBundle/Controller/ListController.php

public function listAction()
{
	$em    = $this->getDoctrine()->getManager();
	$queryBuilder = $em->createQueryBuilder();
	$queryBuilder->select('i')
		->from('AcmeDemoBundle:Item', 'i');

	$query = $queryBuilder->getQuery();

	$adapter = new DoctrineORMAdapter($query);
	$loadMore = new LoadMore($adapter, $request, 10);

	$params = [
		'items' => $loadMore,
	];

	if ($request->isXmlHttpRequest()) {
		return $this->render('AcmeDemoBundle:List:list_content.html.twig', $params);
	}

	return $params;
}
```

#### In the view:

You should separate your view.
The main one which contains the layout of the list.

```django
{# src/Acme/DemoBundle/Resources/views/List/list.html.twig #}
<h1>My list</h1>

<div id="loadmore-area">
	{% include 'AcmeDemoBundle:MyList:loadmore_content.html.twig' %}
</div>
<script>
	$(function (){
		$('#loadmore-area').cameleonLoadmore({
			loadingText: "Loading ..."
		});
	});
</script>
```

And the variable content which can be loaded via AJAX:

```django
{# src/Acme/DemoBundle/Resources/views/List/list_content.html.twig #}
<div class="items">
	{% for item in items %}
		<div class="item">
			{{ item.name }}
		</div>
	{% endfor %}
</div>
{% if items.hasMore %}
	<div class="loadmore-action">
		<a href="{{ items.moreUri }}" class="loadmore-btn btn btn-default">
			Load more
		</a>
	</div>
{% endif %}
```

### Custom fields

In you want to sort on another id field or date field you can use the following methods:

```php
$adapter = new DoctrineORMAdapter($query);
$loadMore = new LoadMore($adapter, $request, 10);

$loadMore->setDateField('my_date'); // Default is "createdAt"
$loadMore->setIdField('id'); // Default value
```

### Reverse side (show previous)

You may need to reverse the items' order, for example, to show previous (older) comments

In that case you can use the `reverse` options (both on JS and PHP) side and place your loadmore button on the top of the list.

In your controller:

```php
$loadMore = new LoadMore($adapter, $request, 10);
$loadMore->setReverse(true);
```

In your layout view:

```django
{# src/Acme/DemoBundle/Resources/views/List/list.html.twig #}
<h1>My list</h1>

<div id="loadmore-area">
	{% include 'AcmeDemoBundle:MyList:loadmore_content.html.twig' %}
</div>
<script>
	$(function (){
		$('#loadmore-area').cameleonLoadmore({
			loadingText: "Loading previous comments...",
			reverse: true
		});
	});
</script>
```

In your partial view:

```django
{% if items.hasMore %}
	<div class="loadmore-action">
		<a href="{{ items.moreUri }}" class="loadmore-btn btn btn-default">
			Show previous
		</a>
	</div>
{% endif %}
{# src/Acme/DemoBundle/Resources/views/List/list_content.html.twig #}
<div class="items">
	{% for item in items %}
		<div class="item">
			{{ item.name }}
		</div>
	{% endfor %}
</div>
```
