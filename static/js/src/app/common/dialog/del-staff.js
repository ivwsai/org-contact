/**
 * @description 删除职员
 * @author <huixiang0922@gmail.com>
 * @date 14-7-10.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var ConfirmBox = require('../../../lib/cmp/dialog/confirm-box');
    var Action = require('../../../lib/util/dom/action');
    var Controller = require('../controller');
    var Fresh = require('../fresh');
    var CommonFun = require('../action/action');

    Action.listen({

        'delStaff': function(e, node){

            var uids = [];

            if (node[0].className === 'del'){
                var n = 0;
                for (var i = 0, len = $('#dataList tr').length; i < len; i++){
                    if ($('#dataList tr').eq(i).hasClass('current')){
                        n++;
                        uids.push($('#dataList tr').eq(i).attr('name'));
                    }
                }
                if (n === 0){
                    CommonFun.tipText('没有选中的职员');
                    return false;
                }
            }else{
                uids.push($(this).closest('tr').attr('name'));
            }

            var delStaff = ConfirmBox.show({
                title: '删除职员',
                repositionOnResize: true,
                content: '<p class="del_text">确定删除该职员信息吗？</p>',
                width: 470,
                height: 300,
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
                //
                var data = {
                    'uids': uids
                };

                Controller.staff.del(data,function(data){
                    delStaff.hide();
                    Fresh.staffList();
                })

            }).on('cancel', function() {
                this.hide();
            });
        }

    })
});
