/**
 * @description 表单校验
 * @author <huixiang0922@gmail.com>
 * @date 2014-7-14
 */
define("dist/app/common/dialog/checker", [ "$", "../../../lib/cmp/validator/validator", "../../../lib/cmp/validator/core", "../../../lib/cmp/validator/async", "../../../lib/cmp/widget", "../../../lib/util/base", "../../../lib/util/class", "../../../lib/util/event", "../../../lib/util/aspect", "../../../lib/util/attribute", "../../../lib/cmp/daparser", "../../../lib/cmp/auto-render", "../../../lib/cmp/validator/utils", "../../../lib/cmp/validator/rule", "../../../lib/cmp/validator/item" ], function(require, exports, module) {
    var $ = require("$");
    var Validator = require("../../../lib/cmp/validator/validator");
    var Checker = {
        init: function(element) {
            var validator = new Validator({
                element: element,
                failSilently: true
            });
            Validator.addRule("username", /^[\u4e00-\u9fa5a-zA-Z]{1}[\u4e00-\u9fa50-9a-zA-Z_]{1,}$/, "{{display}}的格式不正确,请以字母或汉字开头");
            Validator.addRule("date", /^\d{4}-\d{2}-\d{2}$/, "{{display}}的格式不正确");
            /* 添加表单校验 */
            validator.addItem({
                element: "#username",
                required: true,
                rule: 'maxlength{"max": 12} minlength{"min": 2} username'
            }).addItem({
                element: "#mobile",
                required: true,
                rule: "mobile"
            }).addItem({
                element: "#mail",
                rule: "email"
            }).addItem({
                element: "#sex",
                required: true
            }).addItem({
                element: "#joindate",
                required: true,
                rule: "date"
            });
            return validator;
        },
        execute: function(validator) {
            var result = false;
            validator.execute(function(hasError) {
                result = !hasError;
            });
            return result;
        }
    };
    module.exports = Checker;
});