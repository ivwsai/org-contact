/**
 * @description 新增和编辑职员信息时身份证模块的相关操作
 * @author <huixiang0922@gmail.com>
 * @date 14-8-1.
 */
define("dist/app/common/dialog/idcard-done", [ "$", "../action/action", "../../../lib/cmp/dialog/confirm-box", "../../../lib/cmp/dialog/dialog", "../../../lib/cmp/overlay", "../../../lib/util/dom/position", "../../../lib/util/bom/browser", "../../../lib/util/dom/iframe-shim", "../../../lib/cmp/widget", "../../../lib/util/base", "../../../lib/util/class", "../../../lib/util/event", "../../../lib/util/aspect", "../../../lib/util/attribute", "../../../lib/cmp/daparser", "../../../lib/cmp/auto-render", "../../../lib/cmp/mask", "../../../lib/util/dom/sticky", "../../../lib/util/dom/scroll", "../../../lib/util/dom/wheel", "../../../lib/cmp/dialog/tpl/dialog", "../../../lib/cmp/dialog/tpl/button", "../../../lib/util/dom/action" ], function(require, exports, module) {
    var $ = require("$");
    var CommonFun = require("../action/action");
    var regExp = {
        idcard: /^[1-9][0-9]{5}19[0-9]{2}((01|03|05|07|08|10|12)(0[1-9]|[1-2][0-9]|3[0-1])|(04|06|09|11)(0[1-9]|[1-2][0-9]|30)|02(0[1-9]|[1-2][0-9]))[0-9]{3}[0-9Xx]$/,
        othercard: /^[a-zA-Z0-9]{1,}/
    };
    var errMsg = {
        format: "证件号的格式不正确",
        len: "证件号的长度"
    };
    var IDCard = {
        attr: {
            idcardno: "",
            xcardno: ""
        },
        //下拉菜单选择
        idSelect: function(obj) {
            var self = this;
            obj.change(function() {
                self.showRelObj(obj);
            });
        },
        //改变关联对象
        showRelObj: function(obj) {
            var val = obj.val();
            obj.parent().find("input").val("").attr("id", "idcard" + val);
            $("#birthday").val("");
            //改变下拉选项后，对应的输入框失去焦点事件
            var curInput = obj.parent().find("input");
            this.inputBlur(curInput);
        },
        //身份证输入框失去焦点事件
        inputBlur: function(obj) {
            var self = this;
            obj.blur(function() {
                var inputVal = obj.val();
                var birthday = [];
                //开始验证
                self.validator(obj, inputVal);
                //获取表单验证错误信息
                var idNum = obj.attr("id").replace("idcard", "");
                var msg = self.getErrorMsg(+idNum, inputVal);
                switch (+idNum) {
                  case 1:
                    if (msg === "") {
                        if (inputVal !== "") {
                            birthday.push(inputVal.substr(6, 4), inputVal.substr(10, 2), inputVal.substr(12, 2));
                            $("#birthday").val(birthday.join("-")).attr("disabled", true);
                        }
                        self.attr.idcardno = inputVal;
                        self.attr.xcardno = "";
                    } else {
                        self.attr.idcardno = inputVal;
                        self.attr.xcardno = "";
                    }
                    break;

                  case 2:
                    if (msg === "") {
                        $("#birthday").attr("disabled", false);
                        self.attr.idcardno = "";
                        self.attr.xcardno = inputVal;
                    } else {
                        self.attr.idcardno = "";
                        self.attr.xcardno = inputVal;
                    }
                    break;

                  default:
                    break;
                }
            });
            obj.focus(function() {
                obj.closest("li").removeClass("ui-form-item-error");
                obj.siblings("div").remove();
            });
        },
        //获取证件号号码
        getCardNumber: function() {
            return this.attr;
        },
        //证件号表单验证错误消息
        getErrorMsg: function(id, str) {
            switch (id) {
              case 1:
                if (str.length === 0) {
                    return "";
                } else if (str.length !== 18) {
                    return errMsg.len + "应该是18位";
                } else if (!CommonFun.isCnNewID(str)) {
                    return errMsg.format;
                } else {
                    return "";
                }
                break;

              case 2:
                if (str.length === 0) {
                    return "";
                } else if (!regExp.othercard.test(str)) {
                    return errMsg.format;
                } else {
                    return "";
                }
                break;

              default:
                break;
            }
        },
        //验证方法
        validator: function(obj, cardno) {
            obj.closest("li").removeClass("ui-form-item-error");
            obj.closest("li").find("div").remove();
            //获取表单验证错误信息
            var idNum = obj.attr("id").replace("idcard", "");
            var msg = this.getErrorMsg(+idNum, cardno);
            //提示错误信息
            if (msg !== "") {
                obj.closest("li").addClass("ui-form-item-error");
                obj.closest("li").append('<div class="ui-form-explain">' + msg + " </div>");
                return false;
            }
            return true;
        }
    };
    module.exports = IDCard;
});