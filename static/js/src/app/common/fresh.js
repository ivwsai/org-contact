/**
 * @description 一进入首页时信息的展示
 * @author <huixiang0922@gmail.com>
 * @date 14-7-4 10:25
 */

define(function(require, exports, module) {
    var $ = require('$');
    var Controller = require('./controller');
    require('../../lib/cmp/tree/ztree/ztree');
    var GetStaff = require('../get-staff');
    var CommonFun = require('./action/action');
    var pageCommon = require('../page');

    var freshTree = {
        staffList: function(){

            $('#pagination div').remove();

            if ($('#companyName').hasClass("curSelected")) {
                pageCommon.request(1, true, -1);
            } else {
                //获取选中节点的id值
                Global.curA = $('#classTree a.curSelectedNode');
                var curId = GetStaff.getSelectedNode(Global.curA, Global.treeObj).id;

                pageCommon.request(1, true, curId);
            }
        }
    };

    module.exports = freshTree;
})
