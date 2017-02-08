/**
 * @description 项目的ajax配置
 * @author <chengbapi@gmail.com>
 * @date 14-3-5.
 */
define(function (require, exports, module) {
    var Ajax = require('./_ajax');
    var JSON = require('../../lib/util/json');
    var SingletonDialog = require('./dialog/singleton');
    var $ = require('$');

    // 项目相关的权限认证等
    var _options = {
        success: function (data, res, ajax) {
            this.trigger('done', data, res, ajax);
            return this;
        },
        error: function(data, res, ajax){
            if (data.status < 500) {
                this.trigger('params-error', data, res, ajax);
            } else {
                this.trigger('fail', data, res, ajax);
            }
            return this;
        },
        dataType: 'json',
        // 默认事件
        defaults: {
            'not-allowed': function (data) {
                SingletonDialog.show({
                    title: '请求非法',
                    content: (data && data.data && data.data.msg) || '请求非法',
                    buttons: [{
                        text: '确定',
                        action: 'redirect',
                        className:'btn'
                    }],
                    className:'commonDialog',
                    hasMask: {
                        hideOnClick: true
                    }
                }).on('redirect', function() {
                        // @todo: 如果持续无法登陆，这里会造成location越来越长
                        window.location = '/login?ret=' + window.location;
                    });
            },
            'timeout': function () {
                var self = this;
                setTimeout(function () {
                    self.resend();
                }, 5000);
            },
            'no-found': function () {
                SingletonDialog.show({
                    title: '404',
                    content: '请求资源不存在',
                    buttons: [{
                        text: '确定',
                        action: 'close',
                        className:'btn'
                    }],
                    className:'commonDialog',
                    hasMask: {
                        hideOnClick: true
                    }
                });
            },
            'no-permission': function (data) {
                SingletonDialog.show({
                    title: '操作失败',
                    content: data && data.responseJSON && data.responseJSON.msg,
                    buttons: [{
                        text: '确定',
                        action: 'close',
                        className:'btn'
                    }],
                    className:'commonDialog',
                    hasMask: {
                        hideOnClick: true
                    }
                });
            },
            'fail': function(data) {
                SingletonDialog.show({
                    title: '请求异常',
                    content: '<p class="del_text">' + ((data && data.responseJSON && data.responseJSON.msg) || '请求异常') + '</p>',
                    width: 470,
                    height: 300,
                    buttons: [{
                        text: '确定',
                        action: 'close',
                        className:'btn'
                    }],
                    className:'commonDialog',
                    hasMask: {
                        hideOnClick: true
                    }
                });
            },
            'params-error': function(data) {
                SingletonDialog.show({
                    title: '参数错误',
                    content: '<p class="del_text">' + ((data && data.responseJSON && data.responseJSON.msg) || '请求异常') + '</p>',
                    width: 470,
                    height: 300,
                    buttons: [{
                        text: '确定',
                        action: 'close',
                        className:'btn'
                    }],
                    className:'commonDialog'
                });
            },
            'logic-error': function(data) {
                SingletonDialog.show({
                    title: '错误',
                    content: data && data.responseJSON && data.responseJSON.msg,
                    buttons: [{
                        text: '确定',
                        action: 'close',
                        className:'btn'
                    }],
                    className:'commonDialog',
                    hasMask: {
                        hideOnClick: true
                    }
                });
            },
            'server-error': function() {
                SingletonDialog.show({
                    title: '服务器内部错误',
                    content: '服务器内部错误',
                    buttons: [{
                        text: '确定',
                        action: 'close',
                        className:'btn'
                    }],
                    className:'commonDialog',
                    hasMask: {
                        hideOnClick: true
                    }
                });
            }
        }
    };

    function _Ajax(obj, shell) {
        // 默认success规则
        var options = $.extend({}, _options);
        if (obj instanceof Array) {
            // 多请求
            // obj => 字符串
            var data = JSON.stringify(obj);
            options = $.extend(options, {
                method: 'POST',
                url: '/adapter/m',
                data: data
            });
            var subAjax = $.map(obj, function (index, o) {
                o = $.extend(o, _options);
                // 生成壳子
                return Ajax(o, true);
            });
            // 发送请求
            var ajax = Ajax($.extend(options, {
                // 多请求不返回code\data字段
                success: function (data, textStatus, jqXHR) {
                    this.trigger('done', data, textStatus, jqXHR);
                    return this;
                }
            }));
            // 外层ajax请求成功触发内层ajax事件
            ajax.on('done', function (data) {
                $.each(subAjax, function (index, a) {
                    a.success(data[index]);
                });
            });

            // 返回壳子数组
            return subAjax;
        } else {
            // 单请求
            options = $.extend(options, obj);
            if (obj.method && obj.method.toUpperCase() === 'GET') {
                options.method = 'GET';
            } else {
                options.method = 'POST';
                if (obj.method.toUpperCase() === 'DELETE') {
                    obj.data = obj.data || {};
                    obj.data.method = 'delete';
                }
                options.data = JSON.stringify(obj.data);
                options.contentType = 'application/json; charset=UTF-8';
            }

            var url = obj.url;
            if ($.isFunction(url)) {
                url = url();
            }
            options.url = Global.req_url + url;
            return Ajax(options, shell);
        }
    }

    module.exports = _Ajax;
});
