<?php defined('SYSPATH') or die ('No direct script access.');

class Module_Util
{

    /**
     * 获取客户端浏览器类型
     * @return string
     */
    public static function getbrowser()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $browser = '';
        $browser_ver = '';

        do {
            if (preg_match('/OmniWeb\/(v*)([^s|;]+)/i', $agent, $regs)) {
                $browser = 'OmniWeb';
                $browser_ver = $regs[2];
                break;
            }

            if (preg_match('/Netscape([d]*)\/([^s]+)/i', $agent, $regs)) {
                $browser = 'Netscape';
                $browser_ver = $regs[2];
                break;
            }

            if (preg_match('/safari\/([^s]+)/i', $agent, $regs)) {
                $browser = 'Safari';
                $browser_ver = $regs[1];
                break;
            }

            if (preg_match('/MSIE(s)?([^s|;]+)/i', $agent, $regs)) {
                $browser = 'Internet Explorer';
                $browser_ver = $regs[1];
                break;
            }

            if (preg_match('/Opera[s|\/]([^s]+)/i', $agent, $regs)) {
                $browser = 'Opera';
                $browser_ver = $regs[1];
                break;
            }

            if (preg_match('/NetCaptors([^s|;]+)/i', $agent, $regs)) {
                $browser = '(Internet Explorer ' . $browser_ver . ') NetCaptor';
                $browser_ver = $regs[1];
                break;
            }

            if (preg_match('/Maxthon/i', $agent, $regs)) {
                $browser = '(Internet Explorer ' . $browser_ver . ') Maxthon';
                $browser_ver = '';
                break;
            }

            if (preg_match('/FireFox\/([^s]+)/i', $agent, $regs)) {
                $browser = 'FireFox';
                $browser_ver = $regs[1];
                break;
            }

            if (preg_match('/Lynx\/([^s]+)/i', $agent, $regs)) {
                $browser = 'Lynx';
                $browser_ver = $regs[1];
                break;
            }
        } while (0);

        if ($browser != '') {
            return $browser . ' ' . $browser_ver;
        } else {
            return 'Unknow browser';
        }
    }

    /**
     * 获取客户端操作系统版本
     * @return string
     */
    public static function os()
    {
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $os = 'Unknown';

        do {
            if ($agent == '') {
                break;
            }

            if (stripos($agent, 'win') !== FALSE) {
                if (strpos($agent, '95') !== FALSE) {
                    $os = 'Windows 95';
                    break;
                }

                if (strpos($agent, '98') !== FALSE) {
                    $os = 'Windows 98';
                    break;
                }

                if (stripos($agent, 'nt 5.0') !== FALSE) {
                    $os = 'Windows 2000';
                    break;
                }

                if (stripos($agent, 'nt 5.1') !== FALSE) {
                    $os = 'Windows XP';
                    break;
                }

                if (stripos($agent, 'nt 5.2') !== FALSE) {
                    $os = 'Windows Server 2003';
                    break;
                }

                if (stripos($agent, 'nt 6.0') !== FALSE) {
                    $os = 'Windows Vista';
                    break;
                }

                if (stripos($agent, 'nt 6.1') !== FALSE) {
                    $os = 'Windows 7';
                    break;
                }

                if (stripos($agent, 'nt 6.2') !== FALSE) {
                    $os = 'Windows UI';
                    break;
                }

                if (stripos($agent, 'nt') !== FALSE) {
                    $os = 'Windows NT';
                    break;
                }

                if (stripos('32') !== FALSE) {
                    $os = 'Windows 32';
                    break;
                }
            }

            if (stripos($agent, 'win 9x') !== FALSE && strpos($agent, '4.90') !== FALSE) {
                $os = 'Windows ME';
                break;
            }

            if (stripos($agent, 'linux') !== FALSE) {
                $os = 'Linux';
                break;
            }

            if (stripos($agent, 'unix') !== FALSE) {
                $os = 'Unix';
                break;
            }

            if (stripos($agent, 'sun') !== FALSE && stripos($agent, 'os') !== FALSE) {
                $os = 'SunOS';
                break;
            }

            if (stripos($agent, 'ibm') !== FALSE && stripos($agent, 'os') !== FALSE) {
                $os = 'IBM OS/2';
                break;
                break;
            }

            if (stripos($agent, 'Mac') !== FALSE && stripos($agent, 'PC') !== FALSE) {
                $os = 'Macintosh';
                break;
            }

            if (stripos($agent, 'PowerPC') !== FALSE) {
                $os = 'PowerPC';
                break;
            }

            if (stripos($agent, 'AIX') !== FALSE) {
                $os = 'AIX';
                break;
            }

            if (stripos($agent, 'HPUX') !== FALSE) {
                $os = 'HPUX';
                break;
            }

            if (stripos($agent, 'NetBSD') !== FALSE) {
                $os = 'NetBSD';
                break;
            }

            if (stripos($agent, 'BSD') !== FALSE) {
                $os = 'BSD';
                break;
            }

            if (stripos($agent, 'OSF1') !== FALSE) {
                $os = 'OSF1';
                break;
            }

            if (stripos($agent, 'IRIX') !== FALSE) {
                $os = 'IRIX';
                break;
            }

            if (stripos($agent, 'FreeBSD') !== FALSE) {
                $os = 'FreeBSD';
                break;
            }

            if (stripos($agent, 'teleport') !== FALSE) {
                $os = 'teleport';
                break;
            }

            if (stripos($agent, 'flashget') !== FALSE) {
                $os = 'flashget';
                break;
            }

            if (stripos($agent, 'webzip') !== FALSE) {
                $os = 'webzip';
                break;
            }

            if (stripos($agent, 'offline') !== FALSE) {
                $os = 'offline';
                break;
            }
        } while (0);

        return $os;
    }
}
