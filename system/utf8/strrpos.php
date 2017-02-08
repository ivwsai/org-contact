<?php defined('SYSPATH') or die('No direct script access.');
/**
 * UTF8::strrpos
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrpos($str, $search, $offset = 0)
{
    $offset = (int)$offset;

    if (Netap_UTF8::is_ascii($str) AND Netap_UTF8::is_ascii($search))
        return strrpos($str, $search, $offset);

    if ($offset == 0) {
        $array = explode($search, $str, -1);
        return isset($array[0]) ? Netap_UTF8::strlen(implode($search, $array)) : FALSE;
    }

    $str = Netap_UTF8::substr($str, $offset);
    $pos = Netap_UTF8::strrpos($str, $search);
    return ($pos === FALSE) ? FALSE : ($pos + $offset);
}