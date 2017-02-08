/**
 * @description /page
 * @author <huixiang0922@gmail.com>
 * @date 14-7-22
 */

define(function(require, exports, module) {
    var $ = require('$');
    var pagenation = require('./common/pagenation');
    var ajax = require('./common/ajax');
    var StaffList = require('./tpl/staff-list');
    var CtrlTable = require('./ctrl-table');
    var pageSize = 10;

    var pageCommon = {
        page: function (data, flag, id) {
            new pagenation({
                parentNode: '#pagination',
                showPN: false,
                total: +data.total,
                size: pageSize,
                success: function (currentpage) {
                    if (!flag) {
                        //第一次不再请求了，已经请求过了
                        pageCommon.request(currentpage, false, id);
                    }

                }
            }).render();

            flag = false;
        },
        request: function (page, flag, id) {
            var url = '/staff/list?getsub=1&dept_id=' + id + '&page=' + page + '&size=' + pageSize;
            ajax({url: url, method: 'GET'}).done(function(data) {
                if (flag) {
                    pageCommon.page(data, flag, id);
                }

                $('#stu_list_tip div:first b').html(data.total);

                $('#dataList table, #dataList p').remove();

                //for (var i = 0, len = data.data.length; i < len; i++){
                //    var curDate = new Date(parseInt(data.data[i].joindate) * 1000);
                //    data.data[i].joindate = curDate.getFullYear();
                //}

                $('#dataList').append(StaffList.render({
                    slist: data
                }));

                if (data.total === 0){
                    $('#pagination').hide();
                }else{
                    $('#pagination').show();
                }

                //操作表格
                CtrlTable.init();
            })
        },
        pageSearch: function (data, flag, name) {
            new pagenation({
                parentNode: '#pagination',
                showPN: false,
                total: +data.total,
                size: pageSize,
                success: function (currentpage) {
                    if (!flag) {
                        //第一次不再请求了，已经请求过了
                        pageCommon.requestSearch(currentpage, false, name);
                    }

                }
            }).render();

            flag = false;
        },
        requestSearch: function (page, flag, name) {
            var url = '/staff/list?getsub=1&search=' + name + '&page=' + page + '&size=' + pageSize;

            ajax({url: url, method: 'GET'}).done(function(data) {

                if (flag) {
                    pageCommon.pageSearch(data, flag, name);
                }

                $('#stu_list_tip div:first b').html(data.total);

                $('#dataList table, #dataList p').remove();

                //for (var i = 0, len = data.data.length; i < len; i++){
                //    var curDate = new Date(parseInt(data.data[i].joindate) * 1000);
                //    data.data[i].joindate = curDate.getFullYear();
                //}

                $('#dataList').append(StaffList.render({
                    slist: data
                }));

                if (data.total === 0){
                    $('#pagination').hide();
                }else{
                    $('#pagination').show();
                }

                //操作表格
                CtrlTable.init();
            })
        }
    };

    module.exports = pageCommon;
});
