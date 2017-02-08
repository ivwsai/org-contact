/**
 * @description 对节点树进行操作
 * @author <huixiang0922@gmail.com>
 * @date 14-7-7 15:17
 */

define(function(require, exports, module) {
    var $ = require('$');
    var GetStaff = require('./get-staff');
    var WinOperateTree = require('./tpl/operate-tree');
    var CommonFun = require('./common/action/action');

    var operateTree = {
        init: function(){
            operateTree.clickCompany();
            operateTree.clickNode();
            operateTree.hoverNode();
            operateTree.isButtonShow();
        },
        //单击公司名
        clickCompany: function() {
            $('#companyName').on('click',function(){
                $('#classTree a').removeClass('curSelectedNode');
                $(this).addClass("curSelected");
                GetStaff.staffList($(this), -1);
            });
        },
        //单击节点获取数据
        clickNode: function(){
            $('#classTree').on('click','a',function(){
                $('#companyName').removeClass("curSelected");
                $('#classTree a').removeClass('curSelectedNode');
                $(this).addClass('curSelectedNode');

                //获取选中节点的id值
                Global.curA = $('#classTree a.curSelectedNode');
                var curId = GetStaff.getSelectedNode(Global.curA, Global.treeObj).id;
                Global.curid = curId;

                //通过curId获取数据
                GetStaff.staffList(Global.curA, curId);

                operateTree.isButtonShow();
            });
        },
        //移到节点显示操作节点模块
        hoverNode: function(){
            $('#classTree').on('mouseenter','a',function(){
                //判断是不是第八级
                var isEightLevel = $(this).hasClass('level6');
                $(this).addClass('current').append(WinOperateTree.render({
                    isEightLevel: isEightLevel
                }));

                //如果是第八级，调整弹出层的宽度
                if (isEightLevel){
                    $('#winOptTree').css({'width': '197px'});
                }

                //文本溢出及弹出操作节点层级的定位
                CommonFun.treeStyle($(this));

            }).on('mouseleave','a',function(){
                $('#winOptTree').remove();
                $('#btnTree').remove();
                $(this).closest('#classTree').find('a').removeClass('current');
            });
        },
        //通过判断当前选中的节点是否有下一级，来进行显隐增加帐号按钮
        isButtonShow: function(){
            return;
            if ($('#classTree a.curSelectedNode').find('span:first').hasClass('ico_docu')){
                $('.btn_area .add').show();
            }else{
                $('.btn_area .add').hide();
            }
        }
    }

    module.exports = operateTree;

})
