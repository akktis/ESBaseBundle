<?php

namespace ES\Bundle\BaseBundle\Form\Serializer;

class JsonSerializer
{
	private $map;

	function __construct(array $map)
	{
		$this->map = $map;
	}
}