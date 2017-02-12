/**
 * @description 导出
 * @author <huixiang0922@gmail.com>
 * @date 14-7-25.
 */
define("dist/app/common/dialog/xls-outport", [ "$", "../../../lib/cmp/dialog/dialog", "../../../lib/cmp/overlay", "../../../lib/util/dom/position", "../../../lib/util/bom/browser", "../../../lib/util/dom/iframe-shim", "../../../lib/cmp/widget", "../../../lib/util/base", "../../../lib/util/class", "../../../lib/util/event", "../../../lib/util/aspect", "../../../lib/util/attribute", "../../../lib/cmp/daparser", "../../../lib/cmp/auto-render", "../../../lib/cmp/mask", "../../../lib/util/dom/sticky", "../../../lib/util/dom/scroll", "../../../lib/util/dom/wheel", "../../../lib/cmp/dialog/tpl/dialog", "../../../lib/util/dom/action", "../../get-staff", "../../page", "../pagenation", "../../../lib/cmp/pagination/pagination", "../../../lib/util/ajax", "../ajax", "../_ajax", "../../../lib/util/json", "./singleton", "../../../lib/cmp/dialog/confirm-box", "../../../lib/cmp/dialog/tpl/button", "../../tpl/staff-list", "../../ctrl-table", "./tpl/xls-outport" ], function(require, exports, module) {
    var $ = require("$");
    var Dialog = require("../../../lib/cmp/dialog/dialog");
    var Action = require("../../../lib/util/dom/action");
    var GetStaff = require("../../get-staff");
    var XlsOutport = require("./tpl/xls-outport");
    var optStaff, curId;
    Action.listen({
        opt: function(e, node) {
            if (!optStaff) {
                optStaff = new Dialog({
                    title: "导出",
                    repositionOnResize: true,
                    content: XlsOutport.render(),
                    width: 400,
                    height: 450,
                    fixed: false,
                    hasMask: {
                        hideOnClick: true
                    }
                });
            }
            optStaff.show();
            var setting = {
                data: {
                    simpleData: {
                        enable: true
                    }
                }
            };
            var treeObj = $.fn.zTree.init($("#outputTree"), setting, Global.treeObj.getNodes());
            $("#outputTree a:first").addClass("curSelectedNode");
            $("#outputTree").on("click", 'a span[id*="span"]', function() {
                $("#outputTree a").removeClass("curSelectedNode");
                $(this).parent().addClass("curSelectedNode");
            });
            optStaff.element.find('a[data-action="startOutport"]').click(function() {
                //获取选中节点的id值
                var curA = $("#outputTree a.curSelectedNode");
                curId = GetStaff.getSelectedNode(curA, treeObj).id;
                $(this).attr("href", Global.req_url + "/staff/export?dept_id=" + curId);
                optStaff.hide();
            });
            optStaff.element.find('input[data-action="cancel"]').click(function() {
                optStaff.hide();
            });
        }
    });
});