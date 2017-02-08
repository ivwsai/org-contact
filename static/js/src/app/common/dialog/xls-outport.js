/**
 * @description 导出
 * @author <huixiang0922@gmail.com>
 * @date 14-7-25.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Dialog = require('../../../lib/cmp/dialog/dialog');
    var Action = require('../../../lib/util/dom/action');
    var GetStaff = require('../../get-staff');
    var XlsOutport = require('./tpl/xls-outport');
    var optStaff, curId;

    Action.listen({

        'opt': function(e, node){

            if (!optStaff){
                optStaff = new Dialog({
                    title: '导出',
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

            var treeObj = $.fn.zTree.init($('#outputTree'), setting, Global.treeObj.getNodes());

            $('#outputTree a:first').addClass('curSelectedNode');

            $('#outputTree').on('click','a span[id*="span"]',function(){
                $('#outputTree a').removeClass('curSelectedNode');
                $(this).parent().addClass('curSelectedNode');
            })

            optStaff.element.find('a[data-action="startOutport"]').click(function() {

                //获取选中节点的id值
                var curA = $('#outputTree a.curSelectedNode');
                curId = GetStaff.getSelectedNode(curA, treeObj).id;

                $(this).attr('href', Global.req_url + '/staff/export?dept_id=' + curId);

                optStaff.hide();
            });

            optStaff.element.find('input[data-action="cancel"]').click(function() {
                optStaff.hide();
            });

        }
    })
});
