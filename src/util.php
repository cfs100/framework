<?php

namespace framework;

abstract class util
{
	public static function urlFriendly($string)
	{
		$string = trim(strtolower(
			preg_replace(
				'<([^\w\d]|[- ])+>i', '-',
				iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', preg_replace('<\'|">', '', $string))
			)
		), '-');
		if (strlen($string) == 0) {
			$string = '-';
		}
		return $string;
	}

	public static function urlUnfriendly($string)
	{
		return trim(preg_replace('<-+>', ' ', $string));
	}
}
