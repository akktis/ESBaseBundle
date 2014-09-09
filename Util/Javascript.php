<?php


namespace ES\Bundle\BaseBundle\Util;

class Javascript
{
	/**
	 * @param mixed $js
	 */
	static public function encodeJS($js)
	{
		$json = json_encode($js);
		$json = preg_replace_callback('#\"function\s*\(((\\\")*|(.*?[^\\\](\\\")*))\"#ius', function ($regs) {
			return substr(str_replace(array("\\n", "\\t"), '', str_replace('\\"', '"', $regs[0])), 1, -1);
		}, $json);

		return $json;
	}
} 