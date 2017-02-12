/**
 * @description 获取职员列表信息
 * @author <huixiang0922@gmail.com>
 * @date 14-7-7 15:27
 */
define("dist/app/get-staff", [ "$", "./page", "./common/pagenation", "../lib/cmp/pagination/pagination", "../lib/cmp/widget", "../lib/util/base", "../lib/util/class", "../lib/util/event", "../lib/util/aspect", "../lib/util/attribute", "../lib/cmp/daparser", "../lib/cmp/auto-render", "../lib/util/ajax", "./common/ajax", "./common/_ajax", "../lib/util/json", "./common/dialog/singleton", "../lib/cmp/dialog/confirm-box", "../lib/cmp/dialog/dialog", "../lib/cmp/overlay", "../lib/util/dom/position", "../lib/util/bom/browser", "../lib/util/dom/iframe-shim", "../lib/cmp/mask", "../lib/util/dom/sticky", "../lib/util/dom/scroll", "../lib/util/dom/wheel", "../lib/cmp/dialog/tpl/dialog", "../lib/cmp/dialog/tpl/button", "./tpl/staff-list", "./ctrl-table" ], function(require, exports, module) {
    var $ = require("$");
    var pageCommon = require("./page");
    var getStaff = {
        staffList: function(selectedObj, id) {
            var selectedNodeName = selectedObj.attr("title");
            $("#selectTitle").attr("title", id).html(selectedNodeName);
            $("#pagination div").remove();
            pageCommon.request(1, true, id);
        },
        //获取节点树选中节点json
        getSelectedNode: function(obj, treeObj) {
            var currentTreeId = obj.closest("li").attr("id");
            return treeObj.getNodeByTId(currentTreeId);
        },
        getParentText: function(curA) {
            var text = "";
            var curClassNum = curA.parent().attr("class").replace(/level/, "");
            text += Global.unitname;
            for (var i = 0; i <= curClassNum; i++) {
                if (i > curClassNum) {
                    break;
                }
                if (i !== curClassNum) {
                    text += "/";
                }
                text += curA.parents("li.level" + i).find("a").attr("title");
            }
            return text;
        }
    };
    module.exports = getStaff;
});