/**
 * @description ajax模块封装
 * @author <chengbapi@gmail.com>
 * @date 14-3-5.
 */
define("dist/app/common/_ajax", [ "../../lib/util/event", "$" ], function(require, exports, module) {
    var Event = require("../../lib/util/event");
    var $ = require("$");
    function Ajax(params, shell) {
        /******************************************************************************
         * success/error/complete 里的this将被替换,原来是ajax返回对象，不是ajax请求对象
         ******************************************************************************/
        /*
         * params = {
         // params.success可包含自定义的权限、状态等控制
         success: function(data, res, ajax) {
         if (data.cmd === '111') {
         this.trigger('no-permission');
         }
         }
         }
         */
        var events = new Event();
        $.extend(events, {
            done: function(callback) {
                this.on("done", callback);
                return this;
            },
            fail: function(callback) {
                debugger;
                this.on("fail", callback);
                return this;
            },
            error: function(callback) {
                this.on("error", callback);
                return this;
            },
            then: function(callback) {
                this.on("always", callback);
                return this;
            }
        });
        // 默认参数
        params.timeout = params.timeout || 60 * 1e3;
        // 请求成功
        params.success = params.success || function(data, textStatus, jqXHR) {
            this.trigger("done", data, textStatus, jqXHR);
            return this;
        };
        params.success = $.proxy(params.success, events);
        // 请求失败
        params.error = params.error || function(jqXHR, textStatus, errorThrown) {
            if (jqXHR.readyState === 0) {
                // 网络中断 或 连接超时
                if (textStatus === "timeout") {
                    // 连接超时
                    this.trigger("timeout", jqXHR, textStatus, errorThrown);
                } else {
                    // 网络中断
                    this.trigger("disconnect", jqXHR, textStatus, errorThrown);
                }
            }
            if (jqXHR.readyState === 4) {
                var status = jqXHR.status;
                switch (status) {
                  case 400:
                    this.trigger("params-error", jqXHR, textStatus, errorThrown);

                  case 401:
                    this.trigger("not-allowed", jqXHR, textStatus, errorThrown);
                    break;

                  case 403:
                    this.trigger("no-permission", jqXHR, textStatus, errorThrown);
                    break;

                  case 404:
                    this.trigger("no-found", jqXHR, textStatus, errorThrown);
                    break;

                  case 405:
                    this.trigger("params-error", jqXHR, textStatus, errorThrown);
                    break;

                  case 406:
                    this.trigger("logic-error", jqXHR, textStatus, errorThrown);
                    break;

                  case 500:
                    this.trigger("server-error", jqXHR, textStatus, errorThrown);
                    break;
                }
            }
            this.trigger("error", jqXHR, textStatus, errorThrown);
            return this;
        };
        params.error = $.proxy(params.error, events);
        // 不管请求成功或失败
        params.complete = params.complete || function(jqXHR, textStatus) {
            this.trigger("always", jqXHR, textStatus);
            return this;
        };
        params.complete = $.proxy(params.complete, events);
        // 默认事件
        if (params.defaults) {
            $.each(params.defaults, function(event, callback) {
                events.on(event, callback, events);
            });
        }
        if (shell) {
            // 仅返回一个可注册事件的壳 -- 仅用于mulit-api
            return $.extend(events, {
                success: params.success,
                resend: function() {},
                abort: function() {}
            });
        }
        function sendRequest() {
            var prev = events.ajax || {};
            // 正在发送则终止
            if (prev.abort) {
                prev.abort();
            }
            events.ajax = $.ajax(params);
            events.abort = function() {
                return events.ajax.abort();
            };
            return events;
        }
        events.resend = sendRequest;
        events = sendRequest();
        return events;
    }
    module.exports = Ajax;
});