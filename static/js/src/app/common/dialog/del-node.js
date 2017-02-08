/**
 * @description 新增节点弹出框
 * @author <huixiang0922@gmail.com>
 * @date 14-7-2.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var ConfirmBox = require('../../../lib/cmp/dialog/confirm-box');
    var Action = require('../../../lib/util/dom/action');
    var Controller = require('../controller');
    var GetStaff = require('../../get-staff');
    var CommonFun = require('../action/action');

    //判断被选中的节点下面是否还有子节点，给出不同的提示内容
    function getOptions(obj, total){
        //配置参数
        var options = [{
            'buttons': [{
                text: '确定',
                action: 'send'
            }, {
                text: '取消',
                action: 'cancel'
            }],
            'tipText': '是否确定删除该节点？'
        },{
            'buttons': [{
                text: '取消',
                action: 'cancel'
            }],
            'tipText': '该节点下还有数据，无法进行删除操作！'
        }];

        //如果没有子节点
        if (CommonFun.getCurrentNode(obj).find('span:first').hasClass('ico_docu') && (total === 0)){
            return options[0];
        }else{
            return options[1];
        }

    }

    Action.listen({

        //删除节点
        'del': function(e, node){

            var self = $(this);
            var curSelectedNode = GetStaff.getSelectedNode(CommonFun.getCurrentNode(self), Global.treeObj);
            var curId = curSelectedNode.id;

            Controller.orgManager.getStaffList(curId, function(data){
                var total = data.total;
                var options = getOptions(self, total);

                var deleteClass = ConfirmBox.show({
                    title: '删除节点',
                    repositionOnResize: true,
                    content: '<p class="del_text">' + options.tipText + '</p>',
                    width: 470,
                    height: 300,
                    buttons: options.buttons,
                    fixed: true,
                    hasMask: {
                        hideOnClick: true
                    }
                }).on('send', function() {

                    var data = {
                        'dept_id': +curId
                    };

                    Controller.orgManager.del(data,function(data){
                        deleteClass.hide();
                        Global.treeObj.removeNode(curSelectedNode);
                    })
                }).on('cancel', function() {
                    this.hide();
                });
            })
        }

    });

});
