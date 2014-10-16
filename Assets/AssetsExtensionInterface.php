<?php


namespace ES\Bundle\BaseBundle\Assets;

interface AssetsExtensionInterface
{
	function getJavascriptIncludes();

	function getCSSIncludes();

	function getJavascriptCode();
} 