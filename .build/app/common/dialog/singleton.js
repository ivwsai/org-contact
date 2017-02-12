/**
 * @description 单例弹窗，防止多次触发出现多个弹窗
 * @author <caolvchong@gmail.com>
 * @date 14-3-3.
 */
define("dist/app/common/dialog/singleton", [ "$", "../../../lib/cmp/dialog/confirm-box", "../../../lib/cmp/dialog/dialog", "../../../lib/cmp/overlay", "../../../lib/util/dom/position", "../../../lib/util/bom/browser", "../../../lib/util/dom/iframe-shim", "../../../lib/cmp/widget", "../../../lib/util/base", "../../../lib/util/class", "../../../lib/util/event", "../../../lib/util/aspect", "../../../lib/util/attribute", "../../../lib/cmp/daparser", "../../../lib/cmp/auto-render", "../../../lib/cmp/mask", "../../../lib/util/dom/sticky", "../../../lib/util/dom/scroll", "../../../lib/util/dom/wheel", "../../../lib/cmp/dialog/tpl/dialog", "../../../lib/cmp/dialog/tpl/button" ], function(require, exports, module) {
    var $ = require("$");
    var ConfirmBox = require("../../../lib/cmp/dialog/confirm-box");
    var CB = ConfirmBox.extend({
        show: function(config) {
            if (this.get("visible")) {
                this.hide();
            }
            CB.superclass.show.call(this, config);
            return this;
        }
    });
    var ins;
    CB.show = function(config) {
        if (!ins) {
            ins = new CB(config);
        }
        return ins.show(config);
    };
    module.exports = CB;
});