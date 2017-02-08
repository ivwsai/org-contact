/**
 * @description 设置密码
 * @author <huixiang0922@gmail.com>
 * @date 14-12-22.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Dialog = require('../../../lib/cmp/dialog/dialog');
    var Action = require('../../../lib/util/dom/action');
    var SetPwdTpl = require('./tpl/set-pwd');
    var Controller = require('../controller');
    var Common = require('../action/action');

    $(function() {
        Action.listen({
            'setPwd': function(e, node){
                var initialPwd = '';
                $.ajax({
                    url: Global.req_url + '/org/getInitialpwd?type=0',
                    async: false,
                    dataType: 'json'
                }).done(function(json){
                    initialPwd = json.password;
                })

                var setPwd = new Dialog({
                    title: '默认密码配置',
                    repositionOnResize: true,
                    content: SetPwdTpl.render(),
                    width: 650,
                    height: 350,
                    fixed: true,
                    hasMask: {
                        hideOnClick: true
                    }
                });

                setPwd.show();
                setPwd.after('hide', function() {
                    this.destroy();
                });

                var radios = $('input[type="radio"]');

                if (+initialPwd == '' || +initialPwd == 1){
                    radios.eq(0).prop('checked', true);
                }else if (+initialPwd == -1){
                    radios.eq(1).prop('checked', true);
                }else if (+initialPwd == -2){
                    radios.eq(2).prop('checked', true);
                }else{
                    radios.eq(3).prop('checked', true);
                    $('#pwd').val(initialPwd);
                }

                setPwd.element.find('button[data-action="save"]').click(function(){
                    var data = {
                        'type': $('input[name="setpwd"]:checked').val(),
                        'password': $('input[name="pwd"]').val()
                    };

                    if (data.type == 0 && $.trim(data.password) == '') {
                        Common.tipText('密码必须为6~12字符');
                        return;
                    }
                    
                    Controller.staff.setStaffPwd(data).done(function(data){
                        setPwd.hide();
                    })
                });

                setPwd.element.find('input[data-action="cancel"]').click(function() {
                    setPwd.hide();
                });

                $('#showPwd').on('click', function(){
                    if ($(this).prop('checked')){
                        $('#pwd').attr('type', 'text');
                    }else{
                        $('#pwd').attr('type', 'password');
                    }
                })
            }
        });
    });


});
