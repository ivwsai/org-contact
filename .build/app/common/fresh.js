/**
 * @description 一进入首页时信息的展示
 * @author <huixiang0922@gmail.com>
 * @date 14-7-4 10:25
 */
define("dist/app/common/fresh", [ "$", "./controller", "./ajax", "./_ajax", "../../lib/util/event", "../../lib/util/json", "./dialog/singleton", "../../lib/cmp/dialog/confirm-box", "../../lib/cmp/dialog/dialog", "../../lib/cmp/overlay", "../../lib/util/dom/position", "../../lib/util/bom/browser", "../../lib/util/dom/iframe-shim", "../../lib/cmp/widget", "../../lib/util/base", "../../lib/util/class", "../../lib/util/aspect", "../../lib/util/attribute", "../../lib/cmp/daparser", "../../lib/cmp/auto-render", "../../lib/cmp/mask", "../../lib/util/dom/sticky", "../../lib/util/dom/scroll", "../../lib/util/dom/wheel", "../../lib/cmp/dialog/tpl/dialog", "../../lib/cmp/dialog/tpl/button", "../../lib/cmp/tree/ztree/ztree", "../get-staff", "../page", "./pagenation", "../../lib/cmp/pagination/pagination", "../../lib/util/ajax", "../tpl/staff-list", "../ctrl-table", "./action/action", "../../lib/util/dom/action" ], function(require, exports, module) {
    var $ = require("$");
    var Controller = require("./controller");
    require("../../lib/cmp/tree/ztree/ztree");
    var GetStaff = require("../get-staff");
    var CommonFun = require("./action/action");
    var pageCommon = require("../page");
    var freshTree = {
        staffList: function() {
            $("#pagination div").remove();
            if ($("#companyName").hasClass("curSelected")) {
                pageCommon.request(1, true, -1);
            } else {
                //获取选中节点的id值
                Global.curA = $("#classTree a.curSelectedNode");
                var curId = GetStaff.getSelectedNode(Global.curA, Global.treeObj).id;
                pageCommon.request(1, true, curId);
            }
        }
    };
    module.exports = freshTree;
});