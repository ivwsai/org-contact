<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * 验证规则类
 * @package Netap
 * @category System
 * @uses Helper_Valid

 *
 */
class Netap_Validation
{

    /**
     * 不为空验证规则
     * @var string
     */
    const NOT_EMPTY = 'Helper_Valid::not_empty';

    /**
     * 正则匹配规则，需要附加参数
     * @var string
     */
    const REGEX = 'Helper_Valid::regex';

    /**
     * 最小长度限制，需要附加参数
     * @var string
     */
    const MIN_LENGTH = 'Helper_Valid::min_length';

    /**
     * 最大长度限制，需要附加参数
     * @var string
     */
    const MAX_LENGTH = 'Helper_Valid::max_length';

    /**
     * 固定长度限制，需要附加参数
     * @var string
     */
    const EXACT_LENGTH = 'Helper_Valid::exact_length';

    /**
     * 长度范围限制，需要附加参数
     * @var string
     */
    const RANGE_LENGTH = 'Helper_Valid::range_length';

    /**
     * 等于，需要附加参数
     * @var string
     */
    const EQUALS = 'Helper_Valid::equals';

    /**
     * 邮箱验证
     * @var string
     */
    const EMAIL = 'Helper_Valid::email';

    /**
     * 检查邮箱地址的MX记录是否存在
     * @var string
     */
    const EMAIL_DOMAIN = 'Helper_Valid::email_domain';

    /**
     * URL验证
     * @var string
     */
    const URL = 'Helper_Valid::url';

    /**
     * IP验证，需要附加参数是否允许本地地址
     * @var string
     */
    const IP = 'Helper_Valid::ip';

    /**
     * 信用卡验证，需要附加参数type
     * @var string
     */
    const CREDIT_CARD = 'Helper_Valid::credit_card';

    /**
     * LUHN算法，主要用来计算信用卡等证件号码的合法性
     * @var string
     */
    const LUHN = 'Helper_Valid::luhn';

    /**
     * 校验电话号码
     * @var string
     */
    const PHONE = 'Helper_Valid::phone';

    /**
     * 校验日期
     * @var string
     */
    const DATE = 'Helper_Valid::date';

    /**
     * 验证字符是否只由字母组成，需要附加参数是否utf8，默认否
     * @var string
     */
    const ALPHA = 'Helper_Valid::alpha';

    /**
     * 验证字符是否只由字母、数字组成，需要附加参数是否utf8，默认否
     * @var string
     */
    const ALPHA_NUMERIC = 'Helper_Valid::alpha_numeric';

    /**
     * 验证字符是否只由字母、数字、下划线组成，需要附加参数是否utf8，默认否
     * @var string
     */
    const ALPHA_DASH = 'Helper_Valid::alpha_dash';

    /**
     * 判断是否大于0的数字，需要附加参数是否utf8，默认否
     * @var string
     */
    const DIGIT = 'Helper_Valid::digit';

    /**
     * 判断一个值是否有效的数值
     * @var string
     */
    const NUMERIC = 'Helper_Valid::numeric';

    /**
     * 验证一个值是否在指定范围内，需要附加参数$min, $max, $step = NULL
     * @var string
     */
    const RANGE = 'Helper_Valid::range';

    /**
     * 验证是否是合法的十进制格式，需要附加参数$places = 2, $digits = NULL
     * @var string
     */
    const DECIMAL = 'Helper_Valid::decimal';

    /**
     * 验证颜色值是否符合HTML #00000 格式
     * @var string
     */
    const COLOR = 'Helper_Valid::color';

    /**
     * 验证枚举值
     * @var string
     */
    const IN_ARRAY = 'Helper_Valid::in_array';

    /**
     * 规则数组
     * @var array
     */
    protected $rules = array();

    /**
     * 验证错误消息数组
     * @var array
     */
    protected $errors = array();

    public function __construct()
    {
    }

    /**
     *
     * 增加或更新验证规则
     * @param string $field 字段名称
     * @param string $rule 规则名称
     * @param string $error 当发生错误后返回的错误信息
     * @param array $rule_param 规则参数数组，如array(1,2)
     * @return Netap_Validation
     * @throws Netap_Exception
     */
    public function addrule($field, $rule, $error, $rule_param = array())
    {
        if (!empty($field) && !empty($rule) && !empty($error) && is_array($rule_param)) {

            /* 判断规则名是否可合法调用 */
            if (!is_callable($rule)) {
                throw new Netap_Exception('添加规则[' . $field . ']异常,第二个参数必须是可回调的函数,请检查!');
            }

            $this->rules[$field][] = array('rule' => $rule, 'error' => $error, 'rule_param' => $rule_param);
        } else {
            throw new Netap_Exception('添加规则[' . $field . ']异常,参数不正确,请检查!');
        }
        return $this;
    }

    public function cleanrule()
    {
        $this->rules = array();
        $this->errors = array();
    }

    /**
     * 开始验证数组
     * @param array $post
     * @return true|array 如果验证成功，返回TRUE，否则返回错误数组
     */
    public function check($post)
    {
        $this->errors = array();
        if (is_array($post)) {
            foreach ($this->rules as $k => $rules) {
                foreach ($rules as $rule) {
                    if ($rule['rule'] == 'Helper_Valid::not_empty') {
                        if ((isset($post[$k]) && !Helper_Valid::not_empty($post[$k])) || (!isset($post[$k]) && empty($rule['rule_param']['allow_null']))) {
                            $this->errors[$k] = $rule['error'];
                        }
                    } elseif (isset($post[$k]) && Helper_Valid::not_empty($post[$k])) {
                        /* 同一字段只要有遇上不符合规则的，则不往下执行 */
                        if (isset($this->errors[$k])) {
                            break;
                        }

                        $call_param = array_merge(array($post[$k]), $rule['rule_param']);
                        if (!call_user_func_array($rule['rule'], $call_param)) {
                            if (!isset($this->errors[$k])) {
                                $this->errors[$k] = $rule['error'];
                            }
                        }
                    }
                }
            }
        }
        return empty($this->errors);
    }

    /**
     * 获取错误数组
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * 添加错误，回调验证时使用
     * @param strint $k
     * @param mixed $msg
     */
    public function adderror($k, $msg)
    {
        $this->errors[$k] = $msg;
    }
}
