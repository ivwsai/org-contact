/**
 * @description 获取职员列表信息
 * @author <huixiang0922@gmail.com>
 * @date 14-7-7 15:27
 */

define(function(require, exports, module) {
    var $ = require('$');
    var pageCommon = require('./page');

    var getStaff = {
        staffList: function(selectedObj, id){
            var selectedNodeName = selectedObj.attr('title');
            $('#selectTitle').attr('title', id).html(selectedNodeName);
            $('#pagination div').remove();

            pageCommon.request(1, true, id);

        },
        //获取节点树选中节点json
        getSelectedNode: function(obj,treeObj){
            var currentTreeId = obj.closest('li').attr('id');
            return treeObj.getNodeByTId(currentTreeId);
        },
        getParentText: function(curA){
            var text = '';
            var curClassNum = curA.parent().attr('class').replace(/level/,'');
            text += Global.unitname;

            for (var i = 0; i <= curClassNum; i++){

                if (i > curClassNum){
                    break;
                }

                if (i !== curClassNum){
                    text += '/';
                }

                text += curA.parents('li.level' + i).find('a').attr('title');

            }

            return text;
        }
    }

    module.exports = getStaff;
})
