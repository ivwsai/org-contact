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
    var addTpl = require('./tpl/add');
    //var updateAddTpl = require('./tpl/update-add-node');
    var CommonFun = require('../action/action');
    var Counselor = require('./counselor');

    $(function() {
        Action.listen({

            //添加节点
            'add': function(e, node) {

                var self = $(this),
                    parentText = '',
                    curA,
                    curId,
                    parentId = 0;


                Global.curA = curA = CommonFun.getCurrentNode(self);
                var isLastNode = false;//curA.find('span:first').hasClass('ico_docu');

                //获取父节点，分别有“新增学院”和“新建下级”两种情况
                if ($(node[0]).hasClass('btn_add')){
                    parentText = Global.unitname;
                }else{
                    parentText = CommonFun.getParentText(curA);
                    parentId = GetStaff.getSelectedNode(curA, Global.treeObj).id;
                    curId = GetStaff.getSelectedNode(Global.curA, Global.treeObj).id;
                }

                var addClass = ConfirmBox.show({
                    title: '新增节点',
                    repositionOnResize: true,
                    content: addTpl.render({
                        parentText: parentText,
                        isLastNode: isLastNode
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
                        'parent_id': +parentId,
                        'name': nodename,
                        'shortname': nodename,
                        'chief_uid': +managerId,
                        'seq':seq ? seq : 0
                    };

                    Controller.orgManager.add(data,function(data){

                        if (data.errMsg){
                            CommonFun.tipText(data.errMsg);
                            return;
                        }

                        addClass.hide();

                        Global.treeObj.addNodes(GetStaff.getSelectedNode(Global.curA, Global.treeObj), {id:data.dept_id, pId:data.parent_id, name:data.name, seq:data.seq});

                    })

                }).on('cancel', function() {
                    this.hide();
                });

                Counselor.getSelor();
            }
        });
    });

});
