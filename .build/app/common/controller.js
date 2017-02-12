/**
 * @description 请求接口
 * @author <huixiang0922@gmail.com>
 * @date 14-7-2
 */
define("dist/app/common/controller", [ "./ajax", "./_ajax", "../../lib/util/event", "$", "../../lib/util/json", "./dialog/singleton", "../../lib/cmp/dialog/confirm-box", "../../lib/cmp/dialog/dialog", "../../lib/cmp/overlay", "../../lib/util/dom/position", "../../lib/util/bom/browser", "../../lib/util/dom/iframe-shim", "../../lib/cmp/widget", "../../lib/util/base", "../../lib/util/class", "../../lib/util/aspect", "../../lib/util/attribute", "../../lib/cmp/daparser", "../../lib/cmp/auto-render", "../../lib/cmp/mask", "../../lib/util/dom/sticky", "../../lib/util/dom/scroll", "../../lib/util/dom/wheel", "../../lib/cmp/dialog/tpl/dialog", "../../lib/cmp/dialog/tpl/button" ], function(require, exports, module) {
    var Ajax = require("./ajax");
    var Controller = {
        orgManager: {
            getDepts: function(callback) {
                var url = "/dept/all";
                return req(url, "GET", callback);
            },
            //获取部门职员列表
            getStaffList: function(id, callback) {
                var url = "/staff/list?getsub=1&dept_id=" + id;
                return req(url, "GET", callback);
            },
            //新增节点
            add: function(data, callback) {
                var url = "/dept/add";
                return req(url, "POST", callback, data);
            },
            //删除节点
            del: function(data, callback) {
                var url = "/dept/delete";
                return req(url, "POST", callback, data);
            },
            //编辑节点
            edit: function(data, callback) {
                var url = "/dept/edit";
                return req(url, "POST", callback, data);
            },
            //获取单个职员信息
            getSingleInfo: function(uid, callback) {
                var url = "/staff/info?user_id=" + uid;
                return req(url, "GET", callback);
            },
            deptInfo: function(id, callback) {
                var url = "/dept/info?dept_id=" + id;
                return req(url, "GET", callback);
            }
        },
        staff: {
            add: function(data, callback) {
                var url = "/staff/add";
                return req(url, "POST", callback, data);
            },
            del: function(data, callback) {
                var url = "/staff/delete";
                return req(url, "POST", callback, data);
            },
            edit: function(data, callback) {
                var url = "/staff/edit";
                return req(url, "POST", callback, data);
            },
            search: function(name, callback) {
                var url = "/staff/list?search=" + name;
                return req(url, "GET", callback);
            },
            resetPwd: function(data, callback) {
                var url = "/staff/resetpwd";
                return req(url, "POST", callback, data);
            },
            setPwd: function(data, callback) {
                var url = "/staff/setpwd";
                return req(url, "POST", callback, data);
            },
            setStaffPwd: function(data, callback) {
                var url = "/staff/setInitialpwd";
                return req(url, "POST", callback, data);
            }
        }
    };
    module.exports = Controller;
    /**
     * 请求资源
     *
     * @param method
     * @param url
     * @param callback
     * @param data
     */
    function req(url, method, callback, data) {
        return Ajax({
            url: url,
            method: method,
            data: data
        }).done(function(data) {
            callback && callback(data);
        }).error(function(callback) {
            debugger;
            this.on("error", callback);
            return this;
        });
    }
});