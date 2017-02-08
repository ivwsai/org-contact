/**
 * @description 公共模块
 * @author <huixiang0922@gmail.com>
 * @date 14-07-22.
 */
define(function (require, exports, module) {
    var $ = require('$');
    var ConfirmBox = require('../../../lib/cmp/dialog/confirm-box');
    var dataArr = [];

    var CommonFun = {
        //树的返回数据处理函数
        mapData: function(data){

            for (var i = 0; i < data.length; i++){

                var singleData = {};
                singleData.id = data[i].dept_id;
                singleData.pId = data[i].parent_id;
                singleData.name = data[i].name;

                dataArr.push(singleData);

                //if (data[i].sub){
                //    arguments.callee(data[i].sub);
                //}
            }

            return dataArr;
        },
        //错误信息弹出提示
        tipText: function(data){
            var tip = ConfirmBox.alert({
                title: '提示',
                repositionOnResize: true,
                content: '<p class="del_text">' + data + '</p>',
                width: 470,
                height: 300,
                fixed: true,
                hasMask: {
                    hideOnClick: true
                }
            })
        },
        //获取新增职员时的父节点
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
        },
        //获取编辑节点时的父节点
        editParentText: function(curA){
            var text = '';
            var curClassNum = curA.parent().attr('class').replace(/level/,'');
            text += Global.unitname;

            for (var i = 0; i <= curClassNum - 1; i++){

                if (i > curClassNum){
                    break;
                }

                if (i !== curClassNum){
                    text += '/';
                }

                text += curA.parents('li.level' + i).find('a').attr('title');

            }

            return text;
        },
        //统一处理入司时间的格式
        stampToTime: function(s){
            var oDate = new Date(s * 1000);
            return oDate.getFullYear();
        },
        getCurrentNode: function(obj){
            $('#classTree a').removeClass('curSelectedNode');
            obj.closest('a').addClass('curSelectedNode');
            return $('#classTree a.curSelectedNode');
        },
        nodeValidator: function(obj){
            if (/^\s*$/.test(obj.val())){
                obj.val('不能为空').css({'border-color':'#f50','background':'#E9B7BF'});
                obj.focus(function(){
                    obj.val('').css({'border-color':'#ddd','background':'#fff'});
                })
            }
            if (obj.val() === '不能为空'){
                return false;
            }
            return true;
        },
        treeStyle: function(obj){
            var curWidth = parseInt(obj.closest('li').css('width'));
            obj.find('span:last').css('width', (curWidth - 18 + 'px'));
            if (obj.find('span:first').hasClass('ico_docu')){
                obj.css('width', (curWidth + 'px'));
                $('#winOptTree').css('left',(curWidth + 12 + 'px'));
            }else{
                obj.css('width', (curWidth + 'px'));
                $('#winOptTree').css('left',(curWidth - 6 + 'px'));
            }
        },
        //身份证校验
        isCnNewID: function(cid){
            var arrExp = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];//加权因子
            var arrValid = [1, 0, "X", 9, 8, 7, 6, 5, 4, 3, 2];//校验码
            if(/^\d{17}\d|x$/i.test(cid)){
                var sum = 0, idx;
                for(var i = 0; i < cid.length - 1; i++){
                    // 对前17位数字与权值乘积求和
                    sum += parseInt(cid.substr(i, 1), 10) * arrExp[i];
                }
                // 计算模（固定算法）
                idx = sum % 11;
                // 检验第18位是否与校验码相等
                return arrValid[idx] == cid.substr(17, 1).toUpperCase();
            }else{
                return false;
            }
        }
    }

    module.exports = CommonFun;
});
