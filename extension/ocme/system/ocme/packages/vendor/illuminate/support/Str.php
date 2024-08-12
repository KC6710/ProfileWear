<?php namespace Illuminate\Support;

use RuntimeException;
use Stringy\StaticStringy;
use Illuminate\Support\Traits\Macroable;

class Str {

	use Macroable;

	/**
	 * The cache of snake-cased words.
	 *
	 * @var array
	 */
	protected static $snakeCache = [];

	/**
	 * The cache of camel-cased words.
	 *
	 * @var array
	 */
	protected static $camelCache = [];

	/**
	 * The cache of studly-cased words.
	 *
	 * @var array
	 */
	protected static $studlyCache = [];

	/**
	 * Transliterate a UTF-8 value to ASCII.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function ascii($value)
	{
		return StaticStringy::toAscii($value);
	}

	/**
	 * Convert a value to camel case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function camel($value)
	{
		if (isset(static::$camelCache[$value]))
		{
			return static::$camelCache[$value];
		}

		return static::$camelCache[$value] = lcfirst(static::studly($value));
	}

	/**
	 * Determine if a given string contains a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	public static function contains($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ($needle != '' && strpos($haystack, $needle) !== false) return true;
		}

		return false;
	}

	/**
	 * Determine if a given string ends with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	public static function endsWith($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ((string) $needle === substr($haystack, -strlen($needle))) return true;
		}

		return false;
	}

	/**
	 * Cap a string with a single instance of a given value.
	 *
	 * @param  string  $value
	 * @param  string  $cap
	 * @return string
	 */
	public static function finish($value, $cap)
	{
		$quoted = preg_quote($cap, '/');

		return preg_replace('/(?:'.$quoted.')+$/', '', $value).$cap;
	}

	/**
	 * Determine if a given string matches a given pattern.
	 *
	 * @param  string  $pattern
	 * @param  string  $value
	 * @return bool
	 */
	public static function is($pattern, $value)
	{
		if ($pattern == $value) return true;

		$pattern = preg_quote($pattern, '#');

		// Asterisks are translated into zero-or-more regular expression wildcards
		// to make it convenient to check if the strings starts with the given
		// pattern such as "library/*", making any string check convenient.
		$pattern = str_replace('\*', '.*', $pattern).'\z';

		return (bool) preg_match('#^'.$pattern.'#', $value);
	}

	/**
	 * Return the length of the given string.
	 *
	 * @param  string  $value
	 * @return int
	 */
	public static function length($value)
	{
		return mb_strlen($value);
	}

	/**
	 * Limit the number of characters in a string.
	 *
	 * @param  string  $value
	 * @param  int     $limit
	 * @param  string  $end
	 * @return string
	 */
	public static function limit($value, $limit = 100, $end = '...')
	{
		if (mb_strlen($value) <= $limit) return $value;

		return rtrim(mb_substr($value, 0, $limit, 'UTF-8')).$end;
	}

	/**
	 * Convert the given string to lower-case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function lower($value)
	{
		return mb_strtolower($value);
	}

	/**
	 * Limit the number of words in a string.
	 *
	 * @param  string  $value
	 * @param  int     $words
	 * @param  string  $end
	 * @return string
	 */
	public static function words($value, $words = 100, $end = '...')
	{
		preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

		if ( ! isset($matches[0]) || strlen($value) === strlen($matches[0])) return $value;

		return rtrim($matches[0]).$end;
	}

	/**
	 * Parse a Class@method style callback into class and method.
	 *
	 * @param  string  $callback
	 * @param  string  $default
	 * @return array
	 */
	public static function parseCallback($callback, $default)
	{
		return static::contains($callback, '@') ? explode('@', $callback, 2) : array($callback, $default);
	}

	/**
	 * Get the plural form of an English word.
	 *
	 * @param  string  $value
	 * @param  int     $count
	 * @return string
	 */
	public static function plural($value, $count = 2)
	{
		return Pluralizer::plural($value, $count);
	}

	/**
	 * Generate a more truly "random" alpha-numeric string.
	 *
	 * @param  int  $length
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public static function random($length = 16)
	{
		$string = '';

		while (($len = strlen($string)) < $length)
		{
			$size = $length - $len;
			$bytes = static::randomBytes($size);
			$string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
		}

		return $string;
	}

	/**
	 * Generate a more truly "random" bytes.
	 *
	 * @param  int  $length
	 * @return string
	 *
	 * @throws \RuntimeException
	 */
	public static function randomBytes($length = 16)
	{
		if (function_exists('random_bytes'))
		{
			$bytes = random_bytes($length);
		}
		elseif (function_exists('openssl_random_pseudo_bytes'))
		{
			$bytes = openssl_random_pseudo_bytes($length, $strong);
			if ($bytes === false || $strong === false)
			{
				throw new RuntimeException('Unable to generate random string.');
			}
		}
		else
		{
			throw new RuntimeException('OpenSSL extension is required for PHP 5 users.');
		}

		return $bytes;
	}

	/**
	 * Generate a "random" alpha-numeric string.
	 *
	 * Should not be considered sufficient for cryptography, etc.
	 *
	 * @param  int  $length
	 * @return string
	 */
	public static function quickRandom($length = 16)
	{
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
	}

	/**
	 * Convert the given string to upper-case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function upper($value)
	{
		return mb_strtoupper($value);
	}

	/**
	 * Convert the given string to title case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function title($value)
	{
		return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
	}

	/**
	 * Get the singular form of an English word.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function singular($value)
	{
		return Pluralizer::singular($value);
	}

	/**
	 * Generate a URL friendly "slug" from a given string.
	 *
	 * @param  string  $title
	 * @param  string  $separator
	 * @return string
	 */
	public static function slug($title, $separator = '-')
	{
		$title = static::ascii($title);

		// Convert all dashes/underscores into separator
		$flip = $separator == '-' ? '_' : '-';

		$title = preg_replace('!['.preg_quote($flip).']+!u', $separator, $title);

		// Remove all characters that are not the separator, letters, numbers, or whitespace.
		$title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', mb_strtolower($title));

		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

		return trim($title, $separator);
	}

	/**
	 * Convert a string to snake case.
	 *
	 * @param  string  $value
	 * @param  string  $delimiter
	 * @return string
	 */
	public static function snake($value, $delimiter = '_')
	{
		$key = $value.$delimiter;

		if (isset(static::$snakeCache[$key]))
		{
			return static::$snakeCache[$key];
		}

		if ( ! ctype_lower($value))
		{
			$value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
		}

		return static::$snakeCache[$key] = $value;
	}

	/**
	 * Determine if a given string starts with a given substring.
	 *
	 * @param  string  $haystack
	 * @param  string|array  $needles
	 * @return bool
	 */
	public static function startsWith($haystack, $needles)
	{
		foreach ((array) $needles as $needle)
		{
			if ($needle != '' && strpos($haystack, $needle) === 0) return true;
		}

		return false;
	}

	/**
	 * Convert a value to studly caps case.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function studly($value)
	{
		$key = $value;

		if (isset(static::$studlyCache[$key]))
		{
			return static::$studlyCache[$key];
		}

		$value = ucwords(str_replace(array('-', '_'), ' ', $value));

		return static::$studlyCache[$key] = str_replace(' ', '', $value);
	}

    /**
     * Returns the portion of string specified by the start and length parameters.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @return string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * Make a string's first character uppercase.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst($string)
    {
        return static::upper(static::substr($string, 0, 1)).static::substr($string, 1);
    }
    /**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @see https://github.com/danielstjules/Stringy/blob/2.3.1/LICENSE.txt
     *
     * @return array
     */
    protected static function charsArray()
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0'    => ['Â°', 'â‚€', 'Ű°'],
            '1'    => ['Âą', 'â‚', 'Ű±'],
            '2'    => ['Â˛', 'â‚‚', 'Ű˛'],
            '3'    => ['Âł', 'â‚', 'Űł'],
            '4'    => ['â´', 'â‚„', 'Ű´', 'Ů¤'],
            '5'    => ['âµ', 'â‚…', 'Űµ', 'ŮĄ'],
            '6'    => ['â¶', 'â‚†', 'Ű¶', 'Ů¦'],
            '7'    => ['â·', 'â‚‡', 'Ű·'],
            '8'    => ['â¸', 'â‚', 'Ű¸'],
            '9'    => ['âą', 'â‚‰', 'Űą'],
            'a'    => ['Ă ', 'Ăˇ', 'áşŁ', 'ĂŁ', 'áşˇ', 'Ä', 'áşŻ', 'áş±', 'áşł', 'áşµ', 'áş·', 'Ă˘', 'áşĄ', 'áş§', 'áş©', 'áş«', 'áş­', 'Ä', 'Ä…', 'ĂĄ', 'Î±', 'Î¬', 'áĽ€', 'áĽ', 'áĽ‚', 'áĽ', 'áĽ„', 'áĽ…', 'áĽ†', 'áĽ‡', 'áľ€', 'áľ', 'áľ‚', 'áľ', 'áľ„', 'áľ…', 'áľ†', 'áľ‡', 'á˝°', 'Î¬', 'áľ°', 'áľ±', 'áľ˛', 'áľł', 'áľ´', 'áľ¶', 'áľ·', 'Đ°', 'ŘŁ', 'á€ˇ', 'á€¬', 'á€«', 'Ç»', 'ÇŽ', 'ÂŞ', 'á', 'ŕ¤…', 'Ř§'],
            'b'    => ['Đ±', 'Î˛', 'ĐŞ', 'Đ¬', 'Ř¨', 'á€—', 'á‘'],
            'c'    => ['Ă§', 'Ä‡', 'ÄŤ', 'Ä‰', 'Ä‹'],
            'd'    => ['ÄŹ', 'Ă°', 'Ä‘', 'ĆŚ', 'Čˇ', 'É–', 'É—', 'áµ­', 'á¶', 'á¶‘', 'Đ´', 'Î´', 'ŘŻ', 'Ř¶', 'á€Ť', 'á€’', 'á“'],
            'e'    => ['Ă©', 'Ă¨', 'áş»', 'áş˝', 'áşą', 'ĂŞ', 'áşż', 'á»', 'á»', 'á»…', 'á»‡', 'Ă«', 'Ä“', 'Ä™', 'Ä›', 'Ä•', 'Ä—', 'Îµ', 'Î­', 'áĽ', 'áĽ‘', 'áĽ’', 'áĽ“', 'áĽ”', 'áĽ•', 'á˝˛', 'Î­', 'Đµ', 'Ń‘', 'ŃŤ', 'Ń”', 'É™', 'á€§', 'á€±', 'á€˛', 'á”', 'ŕ¤Ź', 'ŘĄ', 'Ř¦'],
            'f'    => ['Ń„', 'Ď†', 'Ů', 'Ć’', 'á¤'],
            'g'    => ['Äť', 'Äź', 'Äˇ', 'ÄŁ', 'Đł', 'Ň‘', 'Îł', 'á€‚', 'á’', 'ÚŻ'],
            'h'    => ['ÄĄ', 'Ä§', 'Î·', 'Î®', 'Ř­', 'Ů‡', 'á€ź', 'á€ľ', 'á°'],
            'i'    => ['Ă­', 'Ă¬', 'á»‰', 'Ä©', 'á»‹', 'Ă®', 'ĂŻ', 'Ä«', 'Ä­', 'ÄŻ', 'Ä±', 'Îą', 'ÎŻ', 'ĎŠ', 'Î', 'áĽ°', 'áĽ±', 'áĽ˛', 'áĽł', 'áĽ´', 'áĽµ', 'áĽ¶', 'áĽ·', 'á˝¶', 'ÎŻ', 'áż', 'áż‘', 'áż’', 'Î', 'áż–', 'áż—', 'Ń–', 'Ń—', 'Đ¸', 'á€Ł', 'á€­', 'á€®', 'á€Šá€ş', 'Ç', 'á', 'ŕ¤‡'],
            'j'    => ['Äµ', 'Ń', 'Đ', 'áŻ', 'Ř¬'],
            'k'    => ['Ä·', 'Ä¸', 'Đş', 'Îş', 'Ä¶', 'Ů‚', 'Ů', 'á€€', 'á™', 'áĄ', 'Ú©'],
            'l'    => ['Ĺ‚', 'Äľ', 'Äş', 'ÄĽ', 'Ĺ€', 'Đ»', 'Î»', 'Ů„', 'á€ś', 'áš'],
            'm'    => ['ĐĽ', 'ÎĽ', 'Ů…', 'á€™', 'á›'],
            'n'    => ['Ă±', 'Ĺ„', 'Ĺ', 'Ĺ†', 'Ĺ‰', 'Ĺ‹', 'Î˝', 'Đ˝', 'Ů†', 'á€”', 'áś'],
            'o'    => ['Ăł', 'Ă˛', 'á»Ź', 'Ăµ', 'á»Ť', 'Ă´', 'á»‘', 'á»“', 'á»•', 'á»—', 'á»™', 'Ćˇ', 'á»›', 'á»ť', 'á»ź', 'á»ˇ', 'á»Ł', 'Ă¸', 'ĹŤ', 'Ĺ‘', 'ĹŹ', 'Îż', 'á˝€', 'á˝', 'á˝‚', 'á˝', 'á˝„', 'á˝…', 'á˝¸', 'ĎŚ', 'Đľ', 'Ů', 'Î¸', 'á€­á€Ż', 'Ç’', 'Çż', 'Âş', 'áť', 'ŕ¤“'],
            'p'    => ['Đż', 'Ď€', 'á€•', 'áž', 'Ůľ'],
            'q'    => ['á§'],
            'r'    => ['Ĺ•', 'Ĺ™', 'Ĺ—', 'Ń€', 'Ď', 'Ř±', 'á '],
            's'    => ['Ĺ›', 'Ĺˇ', 'Ĺź', 'Ń', 'Ď', 'Č™', 'Ď‚', 'Řł', 'Řµ', 'á€…', 'Ĺż', 'áˇ'],
            't'    => ['ĹĄ', 'ĹŁ', 'Ń‚', 'Ď„', 'Č›', 'ŘŞ', 'Ř·', 'á€‹', 'á€', 'Ĺ§', 'á—', 'á˘'],
            'u'    => ['Ăş', 'Ăą', 'á»§', 'Ĺ©', 'á»Ą', 'Ć°', 'á»©', 'á»«', 'á»­', 'á»Ż', 'á»±', 'Ă»', 'Ĺ«', 'ĹŻ', 'Ĺ±', 'Ĺ­', 'Ĺł', 'Âµ', 'Ń', 'á€‰', 'á€Ż', 'á€°', 'Ç”', 'Ç–', 'Ç', 'Çš', 'Çś', 'áŁ', 'ŕ¤‰'],
            'v'    => ['Đ˛', 'á•', 'Ď'],
            'w'    => ['Ĺµ', 'Ď‰', 'ĎŽ', 'á€ť', 'á€˝'],
            'x'    => ['Ď‡', 'Îľ'],
            'y'    => ['Ă˝', 'á»ł', 'á»·', 'á»ą', 'á»µ', 'Ăż', 'Ĺ·', 'Đą', 'Ń‹', 'Ď…', 'Ď‹', 'ĎŤ', 'Î°', 'ŮŠ', 'á€š'],
            'z'    => ['Ĺş', 'Ĺľ', 'ĹĽ', 'Đ·', 'Î¶', 'Ř˛', 'á€‡', 'á–'],
            'aa'   => ['Řą', 'ŕ¤†', 'Ř˘'],
            'ae'   => ['Ă¤', 'Ă¦', 'Ç˝'],
            'ai'   => ['ŕ¤'],
            'at'   => ['@'],
            'ch'   => ['Ń‡', 'á©', 'á­', 'Ú†'],
            'dj'   => ['Ń’', 'Ä‘'],
            'dz'   => ['Ńź', 'á«'],
            'ei'   => ['ŕ¤Ť'],
            'gh'   => ['Řş', 'á¦'],
            'ii'   => ['ŕ¤'],
            'ij'   => ['Äł'],
            'kh'   => ['Ń…', 'Ř®', 'á®'],
            'lj'   => ['Ń™'],
            'nj'   => ['Ńš'],
            'oe'   => ['Ă¶', 'Ĺ“', 'Ř¤'],
            'oi'   => ['ŕ¤‘'],
            'oii'  => ['ŕ¤’'],
            'ps'   => ['Ď'],
            'sh'   => ['Ń', 'á¨', 'Ř´'],
            'shch' => ['Ń‰'],
            'ss'   => ['Ăź'],
            'sx'   => ['Ĺť'],
            'th'   => ['Ăľ', 'Ď‘', 'Ř«', 'Ř°', 'Ř¸'],
            'ts'   => ['Ń†', 'áŞ', 'á¬'],
            'ue'   => ['ĂĽ'],
            'uu'   => ['ŕ¤Š'],
            'ya'   => ['ŃŹ'],
            'yu'   => ['ŃŽ'],
            'zh'   => ['Đ¶', 'áź', 'Ú'],
            '(c)'  => ['Â©'],
            'A'    => ['Ă', 'Ă€', 'áş˘', 'Ă', 'áş ', 'Ä‚', 'áş®', 'áş°', 'áş˛', 'áş´', 'áş¶', 'Ă‚', 'áş¤', 'áş¦', 'áş¨', 'áşŞ', 'áş¬', 'Ă…', 'Ä€', 'Ä„', 'Î‘', 'Î†', 'áĽ', 'áĽ‰', 'áĽŠ', 'áĽ‹', 'áĽŚ', 'áĽŤ', 'áĽŽ', 'áĽŹ', 'áľ', 'áľ‰', 'áľŠ', 'áľ‹', 'áľŚ', 'áľŤ', 'áľŽ', 'áľŹ', 'áľ¸', 'áľą', 'áľş', 'Î†', 'áľĽ', 'Đ', 'Çş', 'ÇŤ'],
            'B'    => ['Đ‘', 'Î’', 'ŕ¤¬'],
            'C'    => ['Ă‡', 'Ä†', 'ÄŚ', 'Ä', 'ÄŠ'],
            'D'    => ['ÄŽ', 'Ă', 'Ä', 'Ć‰', 'ĆŠ', 'Ć‹', 'á´…', 'á´†', 'Đ”', 'Î”'],
            'E'    => ['Ă‰', 'Ă', 'áşş', 'áşĽ', 'áş¸', 'ĂŠ', 'áşľ', 'á»€', 'á»‚', 'á»„', 'á»†', 'Ă‹', 'Ä’', 'Ä', 'Äš', 'Ä”', 'Ä–', 'Î•', 'Î', 'áĽ', 'áĽ™', 'áĽš', 'áĽ›', 'áĽś', 'áĽť', 'Î', 'áż', 'Đ•', 'Đ', 'Đ­', 'Đ„', 'ĆŹ'],
            'F'    => ['Đ¤', 'Î¦'],
            'G'    => ['Äž', 'Ä ', 'Ä˘', 'Đ“', 'Ň', 'Î“'],
            'H'    => ['Î—', 'Î‰', 'Ä¦'],
            'I'    => ['ĂŤ', 'ĂŚ', 'á»', 'Ä¨', 'á»Š', 'ĂŽ', 'ĂŹ', 'ÄŞ', 'Ä¬', 'Ä®', 'Ä°', 'Î™', 'ÎŠ', 'ÎŞ', 'áĽ¸', 'áĽą', 'áĽ»', 'áĽĽ', 'áĽ˝', 'áĽľ', 'áĽż', 'áż', 'áż™', 'áżš', 'ÎŠ', 'Đ', 'Đ†', 'Đ‡', 'ÇŹ', 'Ď’'],
            'K'    => ['Đš', 'Îš'],
            'L'    => ['Äą', 'Ĺ', 'Đ›', 'Î›', 'Ä»', 'Ä˝', 'Äż', 'ŕ¤˛'],
            'M'    => ['Đś', 'Îś'],
            'N'    => ['Ĺ', 'Ă‘', 'Ĺ‡', 'Ĺ…', 'ĹŠ', 'Đť', 'Îť'],
            'O'    => ['Ă“', 'Ă’', 'á»Ž', 'Ă•', 'á»Ś', 'Ă”', 'á»', 'á»’', 'á»”', 'á»–', 'á»', 'Ć ', 'á»š', 'á»ś', 'á»ž', 'á» ', 'á»˘', 'Ă', 'ĹŚ', 'Ĺ', 'ĹŽ', 'Îź', 'ÎŚ', 'á˝', 'á˝‰', 'á˝Š', 'á˝‹', 'á˝Ś', 'á˝Ť', 'áż¸', 'ÎŚ', 'Đž', 'Î', 'Ó¨', 'Ç‘', 'Çľ'],
            'P'    => ['Đź', 'Î '],
            'R'    => ['Ĺ', 'Ĺ”', 'Đ ', 'Îˇ', 'Ĺ–'],
            'S'    => ['Ĺž', 'Ĺś', 'Č', 'Ĺ ', 'Ĺš', 'Đˇ', 'ÎŁ'],
            'T'    => ['Ĺ¤', 'Ĺ˘', 'Ĺ¦', 'Čš', 'Đ˘', 'Î¤'],
            'U'    => ['Ăš', 'Ă™', 'á»¦', 'Ĺ¨', 'á»¤', 'ĆŻ', 'á»¨', 'á»Ş', 'á»¬', 'á»®', 'á»°', 'Ă›', 'ĹŞ', 'Ĺ®', 'Ĺ°', 'Ĺ¬', 'Ĺ˛', 'ĐŁ', 'Ç“', 'Ç•', 'Ç—', 'Ç™', 'Ç›'],
            'V'    => ['Đ’'],
            'W'    => ['Î©', 'ÎŹ', 'Ĺ´'],
            'X'    => ['Î§', 'Îž'],
            'Y'    => ['Ăť', 'á»˛', 'á»¶', 'á»¸', 'á»´', 'Ĺ¸', 'áż¨', 'áż©', 'áżŞ', 'ÎŽ', 'Đ«', 'Đ™', 'ÎĄ', 'Î«', 'Ĺ¶'],
            'Z'    => ['Ĺą', 'Ĺ˝', 'Ĺ»', 'Đ—', 'Î–'],
            'AE'   => ['Ă„', 'Ă†', 'ÇĽ'],
            'CH'   => ['Đ§'],
            'DJ'   => ['Đ‚'],
            'DZ'   => ['ĐŹ'],
            'GX'   => ['Äś'],
            'HX'   => ['Ä¤'],
            'IJ'   => ['Ä˛'],
            'JX'   => ['Ä´'],
            'KH'   => ['ĐĄ'],
            'LJ'   => ['Đ‰'],
            'NJ'   => ['ĐŠ'],
            'OE'   => ['Ă–', 'Ĺ’'],
            'PS'   => ['Î¨'],
            'SH'   => ['Đ¨'],
            'SHCH' => ['Đ©'],
            'SS'   => ['áşž'],
            'TH'   => ['Ăž'],
            'TS'   => ['Đ¦'],
            'UE'   => ['Ăś'],
            'YA'   => ['ĐŻ'],
            'YU'   => ['Đ®'],
            'ZH'   => ['Đ–'],
            ' '    => ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80"],
        ];
    }

}
