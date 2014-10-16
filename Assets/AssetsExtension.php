<?php


namespace ES\Bundle\BaseBundle\Assets;

class AssetsExtension implements AssetsExtensionInterface
{
	function getJavascriptIncludes()
	{
		return [];
	}

	function getCSSIncludes()
	{
		return [];
	}

	function getJavascriptCode()
	{
		return [];
	}
}