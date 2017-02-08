/**
 * @description 新增节点弹出框
 * @author <huixiang0922@gmail.com>
 * @date 14-7-2.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Controller = require('../controller');
    var searchTpl = require('./tpl/search');

    module.exports = {

        getSelor: function(){
            //搜索部门长
            $('#fdy').keyup(function(e){

                var fdy = $(this).val();

                if (fdy === ''){
                    $('#searchList').hide();
                    return;
                }

                if (/^[\u4e00-\u9fa5a-zA-Z0-9]+$/.test(fdy) && (e.keyCode < 37 || e.keyCode > 40)){
                    $('#searchList li').remove();
                    Controller.staff.search(encodeURI(fdy), function(data){
                        if (data.total !== 0){
                            $('#searchList').show().append(searchTpl.render({sinfo: data.data}));
                            $('#searchList li').eq(0).addClass('current');
                        }else{
                            $('#searchList').hide();
                        }
                    })
                }
            })

            //部门长输入框获取焦点
            $('#fdy').focus(function(e){

                if ($('#searchList li').length >= 1){
                    $('#searchList').show();
                }
                e.stopPropagation();

            }).click(function(e){
                e.stopPropagation();
            })

            //单击结果框不失去焦点
            $('#searchList').on('click', 'li', function(){
                $('#fdy').val($(this).text());
                $('#fdy').attr('title', $(this).attr('name'));
                $('#searchList').hide();
            });

            $(document).click(function(){
                $('#searchList').hide();
            })

            //键盘上下键
            var index = 0;
            $(document).keydown(function(e){

                if (e.keyCode === 38){
                    index === 0 && (index = $('#searchList li').length);
                    index--;
                }

                if (e.keyCode === 40){
                    index++;
                    index === $('#searchList li').length && (index = 0);
                }

                $('#searchList li').eq(index).addClass('current').siblings().removeClass('current');

                $('#fdy').val($.trim($('#searchList li').eq(index).text()));

                $('#searchList').scrollTop($('#searchList li').outerHeight() * index);

            })
        }

    };

});
