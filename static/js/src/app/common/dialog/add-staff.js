/**
 * @description 新增职员
 * @author <huixiang0922@gmail.com>
 * @date 14-7-10.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Dialog = require('../../../lib/cmp/dialog/dialog');
    var DateUtil = require('../../../lib/util/date');
    var Calendar = require('../../../lib/cmp/calendar/calendar');
    var Action = require('../../../lib/util/dom/action');
    var Controller = require('../controller');
    var addStaffTpl = require('./tpl/add-staff');
    var Checker = require('./checker');
    var IDCard = require('./idcard-done');
    var Fresh = require('../fresh');

    Action.listen({
        'addStaff': function(e, node){
            var date = new Date();
            var addStaff = new Dialog({
                title: '新增职员',
                repositionOnResize: true,
                content: addStaffTpl.render({
                    joindate: DateUtil.format(date),
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

            addStaff.show();
            addStaff.after('hide', function() {
                this.destroy();
            });

            new Calendar({
                zIndex:addStaff.attrs.zIndex.value+1,
                fixed: true,
                trigger: '#joindate'
            });

            var year = date.getFullYear() - 18;
            new Calendar({
                zIndex:addStaff.attrs.zIndex.value+1,
                fixed: true,
                date: (year-5)+'-12-31',
                trigger: '#birthday',
                disabled: {
                    date: function (date) {
                        return date.getFullYear() > year
                    }
                }
            });

            //给部门文本框设置初始值
            var parentText = $('#selectTitle').text();
            var id = $('#selectTitle').attr('title');
            addStaff.element.find('#classW').attr('title', id).val(parentText).attr('disabled', true);

            //表单验证
            var checker = Checker.init(addStaff.element.find('form'));

            //操作证件号下拉菜单
            var cardSelect = $('#cardSelectBox');
            IDCard.idSelect(cardSelect);

            //证件号输入框失去焦点事件
            var cardInput = cardSelect.parent().find('input[id*=idcard]');
            IDCard.inputBlur(cardInput);

            addStaff.element.find('input[data-action="nextAdd"]').click(function(){
                var username = $('#username').val();
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

                //验证是否通过
                var vtResult = idcardno === '' ? IDCard.validator(cardInput, xcardno) : IDCard.validator(cardInput, idcardno);

                var data = {
                    'name': username,
                    'password': '',
                    'gender': gender,
                    'dept_id': deptId,
                    'title': title,
                    'mobile': mobile,
                    'email': email,
                    'idcardno': idcardno,
                    'xcardno': xcardno,
                    'birthday': birthday,
                    'joindate': joindate,
                    'seat': seat
                };

                if (Checker.execute(checker)){
                    Controller.staff.add(data, function(data){
                        addStaff.hide();
                        Fresh.staffList();
                    });
                }

            });
            addStaff.element.find('input[data-action="cancel"]').click(function() {
                addStaff.hide();
            });
        }

    })
});
