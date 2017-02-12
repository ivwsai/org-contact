/**
 * @description 编辑职员
 * @author <huixiang0922@gmail.com>
 * @date 14-7-10.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Dialog = require('../../../lib/cmp/dialog/dialog');
    var Calendar = require('../../../lib/cmp/calendar/calendar');
    var Action = require('../../../lib/util/dom/action');
    var Controller = require('../controller');
    var editStaffTpl = require('./tpl/edit-staff');
    var selectDeptTpl = require('./tpl/select-dept');
    var resetPwdTpl = require('./tpl/reset-pwd');
    var Checker = require('./checker');
    var IDCard = require('./idcard-done');
    var GetStaff = require('../../get-staff');
    var CommonFun = require('../action/action');
    var Fresh = require('../fresh');
    var selectDept, uid;

    Action.listen({

        'editStaff': function(e, node){
            uid = $(this).closest('tr').attr('name');
            var deptname = $(this).closest('tr').find('td').eq(3).text();

            Controller.orgManager.getSingleInfo(uid,function(data){
                //data.joindate = CommonFun.stampToTime(data.joindate);

                var editStaff = new Dialog({
                    title: '编辑职员信息',
                    repositionOnResize: true,
                    content: editStaffTpl.render({
                        info: data,
                        deptname: deptname
                    }),
                    /*width: 1000,
                    height: '95%',
                    fixed: false,*/
                    width: 850,
                    height: 650,
                    fixed: true,
                    hasMask: {
                        hideOnClick: false
                    }
                });

                editStaff.show();
                editStaff.after('hide', function() {
                    this.destroy();
                });

                new Calendar({
                    zIndex:editStaff.attrs.zIndex.value+1,
                    fixed: true,
                    trigger: '#joindate'
                });

                var year = new Date().getFullYear() - 18;
                new Calendar({
                    zIndex:editStaff.attrs.zIndex.value+1,
                    fixed: true,
                    date: (year-5)+'-12-31',
                    trigger: '#birthday',
                    disabled: {
                        date: function (date) {
                            return date.getFullYear() > year
                        }
                    }
                });

                //性别
                $('#sex option[value=' + data.gender + ']').attr('selected',true);
                $('#status option[value=' + data.status + ']').attr('selected',true);

                data.idcardno === '' ? $('#cardSelectBox option:last').attr('selected',true) : $('#cardSelectBox option:first').attr('selected',true);

                var checker = Checker.init(editStaff.element.find('form'));

                //操作证件号下拉菜单
                var cardSelect = $('#cardSelectBox');
                IDCard.idSelect(cardSelect);

                //证件号输入框失去焦点事件
                var cardInput = cardSelect.parent().find('input[id*=idcard]');
                IDCard.inputBlur(cardInput);

                editStaff.element.find('input[data-action="nextEdit"]').click(function(){
                    cardInput.trigger('blur');

                    var name = $('#username').val();
                    var gender = $('#sex').val();
                    var mobile = $('#mobile').val();
                    var email = $('#mail').val();
                    var deptId = $('#classW').attr('title');
                    var title = $('#title').val();
                    var idcardno = IDCard.getCardNumber().idcardno;
                    var xcardno = IDCard.getCardNumber().xcardno;
                    var joindate = $('#joindate').val();
                    var birthday = $('#birthday').val();
                    var seat = $('#seat').val();
                    var status = $('#status').val();

                    //验证是否通过
                    var vtResult = idcardno === '' ? IDCard.validator(cardInput, xcardno) : IDCard.validator(cardInput, idcardno);

                    var data = {
                        'user_id': uid,
                        'name': name,
                        'gender': gender,
                        'dept_id': deptId,
                        'title': title,
                        'mobile': mobile,
                        'email': email,
                        'idcardno': idcardno,
                        'xcardno': xcardno,
                        'birthday': birthday,
                        'joindate': joindate,
                        'seat': seat,
                        'status': status
                    };

                    if (Checker.execute(checker) && vtResult === true){
                        Controller.staff.edit(data).done(function(){
                            editStaff.hide();
                            Fresh.staffList();
                        })
                    }
                });
                editStaff.element.find('input[data-action="cancel"]').click(function() {
                    editStaff.hide();
                });

            })

        },
        'selectDept': function(e, node){
            if (!selectDept){
                selectDept = new Dialog({
                    title: '选择部门',
                    repositionOnResize: true,
                    content: selectDeptTpl.render(),
                    width: 320,
                    height: 400,
                    fixed: true,
                    hasMask: {
                        hideOnClick: true
                    }
                });
            }

            selectDept.show();

            var setting = {
                data: {
                    simpleData: {
                        enable: true
                    }
                }
            };

            var treeObj = $.fn.zTree.init($('#selectDept'), setting, Global.treeObj.getNodes());

            $('#selectDept a:first').addClass('curSelectedNode');
            $('#selectDept').on('click','a span[id*="span"]',function(){
                $('#selectDept a').removeClass('curSelectedNode');
                $(this).parent().addClass('curSelectedNode');

                //获取选中节点的id值
                var curA = $('#selectDept a.curSelectedNode');
                Global.plaintext = GetStaff.getParentText(curA);
                Global.selId = GetStaff.getSelectedNode(curA, treeObj).id;
            })

            selectDept.element.find('input[data-action="cancel"]').click(function() {
                selectDept.hide();
            });

        },
        'nextSC': function(e, node){
            //if (!$('#selectDept a.curSelectedNode').find('span:first').hasClass('ico_docu')){
            //    CommonFun.tipText('你选择的不是部门，请重新选择！');
            //    return;
            //}

            $('#addForm #classW').attr('title', Global.selId);
            $('#addForm #classW').val(Global.plaintext);

            selectDept.hide();
        },
        'resetPwd': function(e, node){
            var uids = [], initialPwd = '';

            if($(node).attr('class') === 'rpwd'){
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

                $.ajax({
                    url: Global.req_url + '/org/getInitialpwd',
                    async: false,
                    dataType: 'json'
                }).done(function(json){
                    initialPwd = json.password;
                })

            }else{
                uids.push(uid);
            }

            var resetPwd = new Dialog({
                title: '密码重置',
                repositionOnResize: true,
                content: resetPwdTpl.render({
                    initialPwd: initialPwd
                }),
                width: 470,
                height: 270,
                fixed: true,
                hasMask: {
                    hideOnClick: true
                }
            })

            resetPwd.show();
            resetPwd.after('hide', function() {
                this.destroy();
            });

            resetPwd.element.find('input[data-action="reset"]').click(function(){
                var password = $('#pwd').val();

                if (/^\w{6,12}$/.test(password)){
                    $('.tip-error').hide();

                    var data = {
                        'uids': uids,
                        'password': password
                    };

                    Controller.staff.resetPwd(data).done(function(){
                        for (var i = 0, len = $('#dataList tr').length; i < len; i++){
                            $('#dataList tr').eq(i).removeClass('current');
                            $('#dataList tr').eq(i).find('input').prop('checked', false);
                        }
                        resetPwd.hide();
                    })
                }else{
                    $('.tip-error').show();
                    return;
                }

            })

            resetPwd.element.find('input[data-action="cancel"]').click(function() {
                resetPwd.hide();
            });

            $('#showPwd').on('click', function(){
                if ($(this).prop('checked')){
                    $('#pwd').attr('type', 'text');
                }else{
                    $('#pwd').attr('type', 'password');
                }

            })
        }

    })
});
