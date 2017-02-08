<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * 验证规则类
 *
 * @package Netap
 * @category Helpers

 *
 */
class Helper_Valid
{

    /**
     * 验证是否非空
     *
     * @param string $value
     * @return boolean
     */
    public static function not_empty($value)
    {
        if (is_object($value) and $value instanceof ArrayObject) {
            // Get the array from the ArrayObject
            $value = $value->getArrayCopy();
        }

        // Value cannot be NULL, FALSE, '', or an empty array
        return !in_array($value, array(NULL, FALSE, '', array()), TRUE);
    }

    /**
     * 正则匹配
     *
     * @param string $value
     * @param string $expression
     * @return boolean
     */
    public static function regex($value, $expression)
    {
        return (bool)preg_match($expression, (string)$value);
    }

    /**
     *
     * 最小长度验证
     *
     * @uses Netap_UTF8::strlen
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function min_length($value, $length)
    {
        return Netap_UTF8::strlen($value) >= $length;
    }

    /**
     *
     * 最大长度验证
     *
     * @uses Netap_UTF8::strlen
     * @param string $value
     * @param int $length
     * @return boolean
     */
    public static function max_length($value, $length)
    {
        return Netap_UTF8::strlen($value) <= $length;
    }

    /**
     * 长度验证
     *
     * @uses Netap_UTF8::strlen
     * @param string $value
     * @param int|array $length
     * @return boolean
     */
    public static function exact_length($value, $length)
    {
        if (is_array($length)) {
            foreach ($length as $strlen) {
                if (Netap_UTF8::strlen($value) === $strlen)
                    return TRUE;
            }
            return FALSE;
        }

        return Netap_UTF8::strlen($value) === $length;
    }

    /**
     * 字符串长度范围验证
     *
     * @uses Netap_UTF8::strlen
     * @param string $value
     * @param int $min
     * @param int $max
     */
    public static function range_length($value, $min, $max)
    {
        $length = Netap_UTF8::strlen($value);

        if ($length < $min || $length > $max) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 精确比较两个字符是否相同
     *
     * @param string $value
     * @param string $required
     * @return boolean
     */
    public static function equals($value, $required)
    {
        return ($value === $required);
    }

    /**
     * 检查邮箱地址是否正确
     *
     * @uses Netap_UTF8::strlen
     * @param string $email
     * @param boolean $strict 严格按照RFC校验
     * @return boolean
     */
    public static function email($email, $strict = FALSE)
    {
        if (Netap_UTF8::strlen($email) > 254) {
            return FALSE;
        }

        if ($strict === TRUE) {
            $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
            $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
            $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
            $pair = '\\x5c[\\x00-\\x7f]';

            $domain_literal = "\\x5b($dtext|$pair)*\\x5d";
            $quoted_string = "\\x22($qtext|$pair)*\\x22";
            $sub_domain = "($atom|$domain_literal)";
            $word = "($atom|$quoted_string)";
            $domain = "$sub_domain(\\x2e$sub_domain)*";
            $local_part = "$word(\\x2e$word)*";

            $expression = "/^$local_part\\x40$domain$/D";
        } else {
            $expression = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})$/iD';
        }

        return (bool)preg_match($expression, (string)$email);
    }

    /**
     * 检查邮箱地址的MX记录是否存在
     *
     * @param string $email
     * @return boolean
     */
    public static function email_domain($email)
    {
        if (!Helper_Valid::not_empty($email))
            return FALSE; // Empty fields cause issues with checkdnsrr()


        // Check if the email domain has a valid MX record
        return (bool)checkdnsrr(preg_replace('/^[^@]++@/', '', $email), 'MX');
    }

    /**
     * URL地址验证
     *
     * @param string $url
     * @return boolean
     */
    public static function url($url)
    {
        // Based on http://www.apps.ietf.org/rfc/rfc1738.html#sec-5
        if (!preg_match('~^

			# scheme
			[-a-z0-9+.]++://

			# username:password (optional)
			(?:
			[-a-z0-9$_.+!*\'(),;?&=%]++   # username
			(?::[-a-z0-9$_.+!*\'(),;?&=%]++)? # password (optional)
			@
		)?

		(?:
		# ip address
		\d{1,3}+(?:\.\d{1,3}+){3}+

		| # or

		# hostname (captured)
		(
			(?!-)[-a-z0-9]{1,63}+(?<!-)
			(?:\.(?!-)[-a-z0-9]{1,63}+(?<!-)){0,126}+
		)
	)

	# port (optional)
	(?::\d{1,5}+)?

	# path (optional)
	(?:/.*)?

	$~iDx', $url, $matches)
        )
            return FALSE;

        // We matched an IP address
        if (!isset($matches[1]))
            return TRUE;

        // Check maximum length of the whole hostname
        // http://en.wikipedia.org/wiki/Domain_name#cite_note-0
        if (strlen($matches[1]) > 253)
            return FALSE;

        // An extra check for the top level domain
        // It must start with a letter
        $tld = ltrim(substr($matches[1], (int)strrpos($matches[1], '.')), '.');
        return ctype_alpha($tld[0]);
    }

    /**
     * 检查IP地址是否正确
     *
     * @param string $ip
     * @param boolean $allow_private 是否允许私有地址
     * @return boolean
     */
    public static function ip($ip, $allow_private = TRUE)
    {
        // Do not allow reserved addresses
        $flags = FILTER_FLAG_NO_RES_RANGE;

        if ($allow_private === FALSE) {
            // Do not allow private or reserved addresses
            $flags = $flags | FILTER_FLAG_NO_PRIV_RANGE;
        }

        return (bool)filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    /**
     * 验证信用卡号是否合理
     *
     * @param integer $number credit card number
     * @param string|array $type card type, or an array of card types
     * @return boolean
     * @uses Helper_Valid::luhn
     */
    public static function credit_card($number, $type = NULL)
    {
        // Remove all non-digit characters from the number
        if (($number = preg_replace('/\D+/', '', $number)) === '')
            return FALSE;

        if ($type == NULL) {
            // Use the default type
            $type = 'default';
        } elseif (is_array($type)) {
            foreach ($type as $t) {
                // Test each type for validity
                if (Helper_Valid::credit_card($number, $t))
                    return TRUE;
            }

            return FALSE;
        }

        $cards = array(
            'default' => array(
                'length' => '13,14,15,16,17,18,19',
                'prefix' => '',
                'luhn' => TRUE
            ),

            'american express' => array(
                'length' => '15',
                'prefix' => '3[47]',
                'luhn' => TRUE
            ),

            'diners club' => array(
                'length' => '14,16',
                'prefix' => '36|55|30[0-5]',
                'luhn' => TRUE
            ),

            'discover' => array(
                'length' => '16',
                'prefix' => '6(?:5|011)',
                'luhn' => TRUE
            ),

            'jcb' => array(
                'length' => '15,16',
                'prefix' => '3|1800|2131',
                'luhn' => TRUE
            ),

            'maestro' => array(
                'length' => '16,18',
                'prefix' => '50(?:20|38)|6(?:304|759)',
                'luhn' => TRUE
            ),

            'mastercard' => array(
                'length' => '16',
                'prefix' => '5[1-5]',
                'luhn' => TRUE
            ),

            'visa' => array(
                'length' => '13,16',
                'prefix' => '4',
                'luhn' => TRUE
            )
        );
        // Check card type
        $type = strtolower($type);

        if (!isset($cards[$type]))
            return FALSE;

        // Check card number length
        $length = strlen($number);

        // Validate the card length by the card type
        if (!in_array($length, preg_split('/\D+/', $cards[$type]['length'])))
            return FALSE;

        // Check card number prefix
        if (!preg_match('/^' . $cards[$type]['prefix'] . '/', $number))
            return FALSE;

        // No Luhn check required
        if ($cards[$type]['luhn'] == FALSE)
            return TRUE;

        return Helper_Valid::luhn($number);
    }

    /**
     * LUHN算法，主要用来计算信用卡等证件号码的合法性
     * [Luhn](http://en.wikipedia.org/wiki/Luhn_algorithm)
     * (mod10) formula.
     *
     * @param string $number number to check
     * @return boolean
     */
    public static function luhn($number)
    {
        // Force the value to be a string as this method uses string functions.
        // Converting to an integer may pass PHP_INT_MAX and result in an error!
        $number = (string)$number;

        if (!ctype_digit($number)) {
            // Luhn can only be used on numbers!
            return FALSE;
        }

        // Check number length
        $length = strlen($number);

        // Checksum of the card number
        $checksum = 0;

        for ($i = $length - 1; $i >= 0; $i -= 2) {
            // Add up every 2nd digit, starting from the right
            $checksum += substr($number, $i, 1);
        }

        for ($i = $length - 2; $i >= 0; $i -= 2) {
            // Add up every 2nd digit doubled, starting from the right
            $double = substr($number, $i, 1) * 2;

            // Subtract 9 from the double where value is greater than 10
            $checksum += ($double >= 10) ? ($double - 9) : $double;
        }

        // If the checksum is a multiple of 10, the number is valid
        return ($checksum % 10 === 0);
    }

    /**
     * 较验电话号码
     *
     * @param string $number phone number to check
     * @param array $lengths
     * @return boolean
     */
    public static function phone($number, $lengths = NULL)
    {
        if (!is_array($lengths)) {
            $lengths = array(7, 10, 11);
        }

        // Remove all non-digit characters from the number
        $number = preg_replace('/\D+/', '', $number);

        // Check if the number is within range
        return in_array(strlen($number), $lengths);
    }

    /**
     * 验证国内移动电话
     *
     * @param string $number phone number to check
     * @param array $lengths
     * @return boolean
     */
    public static function mobilephone($mobilephone)
    {
        if (preg_match('/^1[3456789]\d{9}$/i', $mobilephone)) {
            return true;
        }
        return false;
    }

    /**
     * 验证时间是否合法
     *
     * @param string $str date to check
     * @return boolean
     */
    public static function date($str)
    {
        return (strtotime($str) !== FALSE);
    }

    /**
     * 验证字符是否只由字母组成
     *
     * @param string $str input string
     * @param boolean $utf8 trigger UTF-8 compatibility
     * @return boolean
     */
    public static function alpha($str, $utf8 = FALSE)
    {
        $str = (string)$str;

        if ($utf8 === TRUE) {
            return (bool)preg_match('/^\pL++$/uD', $str);
        } else {
            return ctype_alpha($str);
        }
    }

    /**
     * 验证字符是否只由字母、数字组成
     *
     * @param string $str input string
     * @param boolean $utf8 trigger UTF-8 compatibility
     * @return boolean
     */
    public static function alpha_numeric($str, $utf8 = FALSE)
    {
        if ($utf8 === TRUE) {
            return (bool)preg_match('/^[\pL\pN]++$/uD', $str);
        } else {
            return ctype_alnum($str);
        }
    }

    /**
     * 验证字符是否只由字母、数字、破折号、下划线组成
     *
     * @param string $str input string
     * @param boolean $utf8 trigger UTF-8 compatibility
     * @return boolean
     */
    public static function alpha_dash($str, $utf8 = FALSE)
    {
        if ($utf8 === TRUE) {
            $regex = '/^[-\pL\pN_]++$/uD';
        } else {
            $regex = '/^[-a-z0-9_]++$/iD';
        }

        return (bool)preg_match($regex, $str);
    }

    /**
     * 判断是否大于0的数字
     *
     * @param string $str input string
     * @param boolean $utf8 trigger UTF-8 compatibility
     * @return boolean
     */
    public static function digit($str, $utf8 = FALSE)
    {
        if ($utf8 === TRUE) {
            return (bool)preg_match('/^\pN++$/uD', $str);
        } else {
            return (is_int($str) and $str >= 0) or ctype_digit($str);
        }
    }

    /**
     * 判断一个值是否有效的数值
     *
     * @param string $str input string
     * @return boolean
     */
    public static function numeric($str)
    {
        // Get the decimal point for the current locale
        list ($decimal) = array_values(localeconv());

        // A lookahead is used to make sure the string contains at least one
        // digit (before or after the decimal point)
        return (bool)preg_match('/^-?+(?=.*[0-9])[0-9]*+' . preg_quote($decimal) . '?+[0-9]*+$/D', (string)$str);
    }

    /**
     * 验证一个值是否在指定范围内
     *
     * @param string $number number to check
     * @param integer $min minimum value
     * @param integer $max maximum value
     * @param integer $step increment size
     * @return boolean
     */
    public static function range($number, $min, $max, $step = NULL)
    {
        if ($number <= $min or $number >= $max) {
            // Number is outside of range
            return FALSE;
        }

        if (!$step) {
            // Default to steps of 1
            $step = 1;
        }

        // Check step requirements
        return (($number - $min) % $step === 0);
    }

    /**
     * 验证是否是合法的十进制格式
     *
     * @param string $str number to check
     * @param integer $places number of decimal places
     * @param integer $digits number of digits
     * @return boolean
     */
    public static function decimal($str, $places = 2, $digits = NULL)
    {
        if ($digits > 0) {
            // Specific number of digits
            $digits = '{' . ((int)$digits) . '}';
        } else {
            // Any number of digits
            $digits = '+';
        }

        // Get the decimal point for the current locale
        list ($decimal) = array_values(localeconv());

        return (bool)preg_match('/^[+-]?[0-9]' . $digits . preg_quote($decimal) . '[0-9]{' . ((int)$places) . '}$/D', $str);
    }

    /**
     *验证颜色值是否符合HTML #00000 格式
     *
     * @param string $str input string
     * @return boolean
     */
    public static function color($str)
    {
        return (bool)preg_match('/^#?+[0-9a-f]{3}(?:[0-9a-f]{3})?$/iD', $str);
    }

    /**
     * 验证字符是否只由字母、数字、中文、破折号、下划线、小括号组成
     *
     * @param string $str input string
     * @param boolean $utf8 trigger UTF-8 compatibility
     * @return boolean
     */
    public static function alpha_numeric_cn($str, $utf8 = FALSE)
    {
        if ($utf8 === TRUE) {
            $regex = '/^[\x3447-\xfa29\pL\pN\-_\(\)]++$/uD';
        } else {
            $regex = '/^[一-龥|\w\-_\(\)]++$/D';
        }

        return (bool)preg_match($regex, $str);
    }


    /**
     * 是否存在大写字母
     *
     * @param string $str
     * @return boolean
     */
    public static function uppercase($str)
    {
        return (bool)preg_match('/[A-Z]/', $str);
    }

    /**
     * 18位身份证验证
     * @param string $idcardno
     * @return boolean
     */
    public static function idcard18($idcardno)
    {
        if (empty($idcardno) || !is_string($idcardno) || strlen($idcardno) != 18) {
            return false;
        }
        $allow_chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'X');

        $idcardno = strtoupper($idcardno);
        $idcards = str_split($idcardno);
        foreach ($idcards as $val) {
            if (!in_array($val, $allow_chars)) {
                return false;
            }
        }

        $year = intval(substr($idcardno, 6, 4));
        $month = intval(substr($idcardno, 10, 2));
        $day = intval(substr($idcardno, 12, 2));

        if (!checkdate($month, $day, $year)) {
            return false;
        }
        // wi为加权因子列表
        $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // cc为验证码列表
        $cc = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $sum = 0;

        foreach ($idcards as $i => $val) {
            if ($i >= 17) {
                break;
            }
            $sum += intval($val) * $wi[$i];
        }
        return $idcards[17] == $cc[$sum % 11];
    }
}
