define(function (require, exports, module) {

    var reg;

    //数字
    var check_integer = function (value) {
        reg = /^\d+$/;
        return reg.test(value);
    };
    //字母
    var check_english = function (value) {
//        reg = /^[a-zA-Z{1,}\ \,\.\?\:\\\/\*\#\@\$\&\^\(\)\[\]\{\}\<\>\;\'\"\!\-\=\_\+\|\t]+$/;
        reg = /^[a-zA-Z\ \,\.\?\:\\\/\*\#\@\$\&\^\(\)\[\]\{\}\<\>\;\'\"\!\-\=\_\+\|\t]+$/;
        return reg.test(value);
    };
    //中文
    var check_chinese = function (value) {
        var trim_value = value.replace(/s+/g, "");
        //除汉字外，标点符号也能过
        reg = /^[\u4E00-\u9FA5\u3002\uff1b\uff0c\uff1a\u201c\u201d\uff08\uff09\u3001\uff1f\u300a\u300b,\.;\-_\?]*$/;
        return reg.test(trim_value);
    };
    //手机号码
    var check_mobile = function (value) {
        reg = /^(((13)|(15)|(18))+\d{9})$/;
        return reg.test(value);
    };
    //email
    var check_email = function (value) {
        reg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return reg.test(value);
    };
    //不限
    var check_none = function (str) {
        return true;
    }

    //验证是否含有引用的格式
    var check_quote = function (value) {
        var reg = /\[Q[1-9]+\]/g;
        return reg.test(value);
    }
    //返回匹配引用格式的数组
    var match_quotes = function (value) {
        var reg = /\[Q[1-9]+\]/g;
        var matches=value.match(reg);
        return   matches;
    }


    var checkregex = {
        getmethod: function (value, type) {
            var enum_method = {
                "none": check_none(value),
                "number": check_integer(value),
                "alphabet": check_english(value),
                "chinese": check_chinese(value),
                "email": check_email(value),
                "mobile": check_mobile(value),
                'checkQuote': check_quote(value),
                'matchQuotes':match_quotes(value)
            };
            return enum_method[type];
        },
        check_val: function (value, type) {
            switch (type) {
                //不限
                case "1":
                    return  this.getmethod(value, "none");
                    break;
                //数字
                case "2":
                    return  this.getmethod(value, "number");
                    break;
                //字母
                case "3":
                    return this.getmethod(value, "alphabet");
                    break;
                //中文
                case "4":
                    return  this.getmethod(value, "chinese");
                    break;
                //邮箱
                case "5":
                    return this.getmethod(value, "email");
                    break;
                //手机号码
                case "6":
                    return  this.getmethod(value, "mobile");
                    break;
                //检测引用
                case '7':
                    return this.getmethod(value, 'checkQuote');
                    break;
                //返回引用匹配数组
                case '8':
                    return this.getmethod(value,'matchQuotes');
                    break;
                default:
                    return  this.getmethod(value, "none");
                    break;
            }
        }


    };
    module.exports = checkregex;


});