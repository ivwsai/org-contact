/**
 * @description 导入
 * @authors  (huixiang0922@gmail.com)
 * @date    2014-07-24 16:23:36
 */
define("dist/app/import/import", [ "$", "../common/dialog/tpl/import-list" ], function(require, exports, module) {
    var $ = require("$");
    var ImportList = require("../common/dialog/tpl/import-list");
    //var ErrorTip = require('./import-error-tip');
    // 导入功能封装
    var ImportXsl = {
        // 初始化各变量
        SUCCESS_NUM: 0,
        // 导入成功的条数
        FAIL_NUM: 0,
        // 导入失败的条数
        START_INDEX: 2,
        // 第一条导入记录的索引
        CURR_INDEX: 2,
        // 当前正在导入的记录索引
        LIST_DATA: null,
        // 缓存flash返回的数据
        ERROR_DATA: [ null, null ],
        // 导入出错的数据
        _CALLBACK: null,
        // 提交的数据
        _URL: "",
        // 提交的地址
        _ACTIVE: true,
        // 页面加载完成后执行
        __init: function(url, callback, index) {
            var me = this;
            me._CALLBACK = callback;
            me._URL = url;
            if (typeof index != "undefined") {
                me.START_INDEX = index;
            }
            // 切换、刷新或关闭页面时弹出提示框
            window.onbeforeunload = function(e) {
                if ($("#process").html() != "") {
                    e = e || window.event;
                    if (e) {
                        var targetHtml = $(window.document.activeElement).html();
                        //获取当前活动链接
                        if (targetHtml.replace("模板") == targetHtml) {
                            e.returnValue = "切换、刷新或关闭页面将终止导入功能！";
                        }
                    } else {
                        return "切换、刷新或关闭页面将终止导入功能！";
                    }
                }
            };
            //xlt->json
            window.excelToJson = function(list) {
                // 数据初始化
                me.SUCCESS_NUM = 0;
                me.FAIL_NUM = 0;
                me.CURR_INDEX = me.START_INDEX;
                me.LIST_DATA = list;
                me.ERROR_DATA = [];
                if (!me._ACTIVE) {
                    return;
                }
                if (me.LIST_DATA.length <= 2) {
                    alert("Excel文件格式读取失败，请检查文件后重试。");
                    return;
                }
            };
            // 开始导入
            window.startImport = function() {
                if (!me.LIST_DATA || !me.LIST_DATA.length) {
                    alert("请点击“选择文件”上传文件");
                    return;
                } else if (me.LIST_DATA.length <= me.START_INDEX) {
                    alert("Excel文件没有可导入数据，请检查文件后重试。");
                    return;
                }
                $(".dtSmallBody").hide();
                $("#timeout").hide();
                $("#importResult").show();
                $("#processContent").show();
                me.sendExcelList(me.CURR_INDEX, me.LIST_DATA);
            };
            // 导出错误
            window.exportErrorData = function() {
                var form = $("#exportForm");
                if (!form.length) {
                    form = $("<form>");
                    //定义一个form表单
                    form.attr("id", "exportForm");
                    form.attr("style", "display:none");
                    form.attr("method", "post");
                    form.attr("action", Global.req_url + "/staff/import/error");
                    var input1 = $("<input>");
                    input1.attr("type", "hidden");
                    input1.attr("name", "content");
                    input1.attr("value", JSON.stringify(me.ERROR_DATA));
                    $("body").append(form);
                    //将表单放置在web中
                    form.append(input1);
                } else {
                    form.find(":hidden").attr("value", JSON.stringify(me.ERROR_DATA));
                }
                form.submit();
            };
            //error
            window.excelToJsonError = function(e) {
                me._ACTIVE = false;
                alert("Excel文件格式读取失败，请检查文件后重试。");
            };
            //解析开始
            window.excelToJsonStart = function(msg) {
                $("#pocess").html("正在读取数据文件..");
            };
        },
        //获取当前时间
        getCurrTime: function() {
            var date = new Date();
            return date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
        },
        // excel导入
        sendExcelList: function(i, list) {
            var me = this;
            me.CURR_INDEX = i + 1;
            var beforeDataObj = me._CALLBACK(me.LIST_DATA, i).record;
            var data = {
                deptname: beforeDataObj[0],
                username: beforeDataObj[2],
                workid: beforeDataObj[1],
                joindate: beforeDataObj[3],
                mobilephone: beforeDataObj[5] || "",
                email: beforeDataObj[6] || "",
                cardno: beforeDataObj[7],
                gender: beforeDataObj[4]
            };
            $.ajax({
                url: me._URL,
                type: "POST",
                dataType: "json",
                data: data,
                timeout: 3e4,
                success: function(data) {
                    me.dealBackData(i, "success", data);
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    try {
                        if (textStatus == "timeout") {
                            if (i == me.START_INDEX) {
                                me.CURR_INDEX--;
                                $(".dtSmallBody").hide();
                                $("#processContent").hide();
                                $("#importResult").hide();
                                $("#timeout").show();
                            } else {
                                me.dealBackData(i, "timeout", "导入超时！");
                            }
                        } else {
                            var data = eval("(" + XMLHttpRequest.responseText + ")");
                            me.dealBackData(i, "error", data, data.stop);
                        }
                    } catch (e) {
                        if (i == me.START_INDEX) {
                            me.CURR_INDEX--;
                            $(".dtSmallBody").hide();
                            $("#processContent").hide();
                            $("#importResult").hide();
                            $("#timeout").show();
                        } else {
                            me.dealBackData(i, "error", "服务器异常！");
                        }
                    }
                    if (data && data.errMsg) {
                        me.LIST_DATA[i].msg = data.errMsg;
                        me.ERROR_DATA.push(data);
                    }
                },
                complete: function(XMLHttpRequest, throwError) {
                    var data = eval("(" + XMLHttpRequest.responseText + ")");
                    if (typeof data === "object") {
                        if (data.code == 200) {
                            $("#allList").append(ImportList.render({
                                msglist: data.data,
                                type: data.msg
                            }));
                        } else {
                            var errArr = [];
                            for (var j in data.errMsg) {
                                errArr.push(data.errMsg[j]);
                            }
                            var errmsg = errArr.join("、");
                            $("#allList").append(ImportList.render({
                                msglist: data,
                                type: data.errMsg,
                                errmsg: errmsg
                            }));
                        }
                        if (i == list.length - 1 || list.length === me.START_INDEX) {
                            $(".dtSmallBody").hide();
                            $("#timeout").hide();
                            $("#importResult").show();
                            $("#importResultTime").text(new Date().toLocaleString());
                            $("#importSuccNum").text(me.SUCCESS_NUM);
                            $("#importFailNum").text(me.FAIL_NUM);
                            if (me.FAIL_NUM > 0) {
                                $("#showExport p").show();
                            } else {
                                $("#showExport p").hide();
                                $("#errList").append('<li class="err">恭喜您，全部导入成功！</li>');
                            }
                        }
                    } else {
                        $("#allList").append('<li class="err">服务器出错！</li>');
                    }
                }
            });
        },
        //excel导入结果处理
        dealBackData: function(i, type, msg, stop) {
            var me = this;
            var color = "";
            switch (type) {
              case "error":
                var errArr = [];
                for (var j in msg.errMsg) {
                    errArr.push(msg.errMsg[j]);
                }
                var errmsg = errArr.join("、");
                color = "red";
                $("#errList").append(ImportList.render({
                    msglist: msg,
                    type: msg.errMsg,
                    errmsg: errmsg
                }));
                /*ErrorTip.tipBox();*/
                me.FAIL_NUM++;
                $("#importFailNum").text(me.FAIL_NUM);
                break;

              case "timeout":
                var errArr = [];
                for (var j in msg.errMsg) {
                    errArr.push(msg.errMsg[j]);
                }
                var errmsg = errArr.join("、");
                color = "red";
                $("#errList").append(ImportList.render({
                    msglist: msg,
                    type: msg.errMsg,
                    errmsg: errmsg
                }));
                color = "red";
                me.FAIL_NUM++;
                $("#importFailNum").text(me.FAIL_NUM);
                break;

              case "success":
                color = "green";
                $("#successList").append(ImportList.render({
                    msglist: msg.data,
                    type: msg.msg
                }));
                me.SUCCESS_NUM++;
                $("#importSuccNum").text(me.SUCCESS_NUM);
                break;

              case "empty":
                color = "red";
                me.FAIL_NUM++;
                $("#importFailNum").text(me.FAIL_NUM);

              default:
                break;
            }
            $("#processData").html(me.LIST_DATA.length - me.START_INDEX);
            $(".gc strong").html(Math.ceil(100 * ((i - (me.START_INDEX - 1)) / (me.LIST_DATA.length - me.START_INDEX))) + "%");
            $("#curProcess").width($("#processBar").width() * ((i - (me.START_INDEX - 1)) / (me.LIST_DATA.length - me.START_INDEX)));
            if (i < me.LIST_DATA.length - 1 && !stop) {
                me.sendExcelList(++i, me.LIST_DATA);
            }
        }
    };
    module.exports = ImportXsl;
});