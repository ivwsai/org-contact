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
    var editTpl = require('./tpl/edit');
    var CommonFun = require('../action/action');
    var Counselor = require('./counselor');

    $(function() {
        Action.listen({

            //编辑节点
            'edit': function(e, node){

                var self = $(this);
                var isLastNode = true;//CommonFun.getCurrentNode(self).find('span:first').hasClass('ico_docu');
                var curNode = GetStaff.getSelectedNode(CommonFun.getCurrentNode(self), Global.treeObj);
                var currentA = CommonFun.getCurrentNode(self);

                //获取父元素文本
                var parentText = CommonFun.editParentText(CommonFun.getCurrentNode(self));
                var parentId = 0;

                if (!CommonFun.getCurrentNode(self).closest('ul').siblings('a').hasClass('btn_add')){
                    parentId = GetStaff.getSelectedNode(CommonFun.getCurrentNode(self).closest('ul').siblings('a'), Global.treeObj).id;
                }

                var curTitle = self.closest('a').attr('title');

                Controller.orgManager.deptInfo(curNode.id, function(data){

                    var managerName = data.manager_name;
                    var chiefUid = data.chief_uid;

                    var editClass = ConfirmBox.show({
                        title: '编辑节点',
                        repositionOnResize: true,
                        content: editTpl.render({
                            isLastNode: isLastNode,
                            parentText: parentText,
                            curTitle: curTitle,
                            curSeq:curNode.seq,
                            managerName: managerName
                        }),
                        width: 470,
                        height: 365,
                        buttons: [{
                            text: '确定',
                            action: 'send'
                        }, {
                            text: '取消',
                            action: 'cancel'
                        }],
                        fixed: true,
                        hasMask: {
                            hideOnClick: true
                        }
                    }).on('send', function() {

                        var nodeObj = $('#nodename');
                        var vdNode = CommonFun.nodeValidator(nodeObj);
                        if (vdNode === false){
                            return;
                        }

                        var nodename = nodeObj.val();
                        var managerId = $('#fdy').attr('title');
                        var seq = $('#deptseq').val();

                        var data = {
                            'dept_id': +curNode.id,
                            'parent_id': $('#parentnode').attr('title') || +parentId,
                            'name': nodename,
                            'shortname': nodename,
                            'chief_uid': +managerId || chiefUid,
                            'seq':seq
                        };

                        Controller.orgManager.edit(data,function(data){
                            curNode.seq = seq;
                            //这里应该更新缓存，而不是直接操作dom
                            editClass.hide();
                            currentA.find('span:last').text(nodename);
                            currentA.attr('title', nodename);
                        });
                    }).on('cancel', function() {
                        this.hide();
                    });

                    Counselor.getSelor();
                })
            }
        });
    });

});
