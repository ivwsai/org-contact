/**
 * @description 获取职员列表信息
 * @author <huixiang0922@gmail.com>
 * @date 14-7-7 15:27
 */

define(function(require, exports, module) {
    var $ = require('$');

    var table = document.getElementById("dataList");
    var oInput = table.getElementsByTagName("input");

    function isCheckAll(){
        var n = 0;

        for (var i = 1, n = 0; i < oInput.length; i++){

            $(oInput[i]).closest('tr').removeClass('current');

            if (oInput[i].checked){
                n++;
                $(oInput[i]).closest('tr').addClass('current');
            }

        }
        oInput[0].checked = n == oInput.length - 1;
    }

    var ctrlTable = {
        init: function(){
            if (oInput.length < 1){
                return;
            }
            oInput[0].onclick = function (){

                for (var i = 1; i < oInput.length; i++){
                    oInput[i].checked = this.checked;
                }
                isCheckAll();
            };

            //根据复选个数更新全选框状态
            for (var i = 1; i < oInput.length; i++){
                oInput[i].onclick = function (){
                    isCheckAll();
                }
            }
        }
    }

    Global.Helper = ctrlTable;
    module.exports = ctrlTable;
})
