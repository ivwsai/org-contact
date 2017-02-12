define("dist/app/common/upload-pic", [ "$", "../../lib/util/dom/upload/upload", "../../lib/util/class", "../../lib/util/dom/upload/upload-swfupload", "../../lib/util/dom/upload/swfupload", "../../lib/util/base", "../../lib/util/event", "../../lib/util/aspect", "../../lib/util/attribute", "../../lib/util/dom/upload/upload-html5", "../../lib/util/dom/upload/upload-draggable", "../../lib/cmp/dialog/confirm-box", "../../lib/cmp/dialog/dialog", "../../lib/cmp/overlay", "../../lib/util/dom/position", "../../lib/util/bom/browser", "../../lib/util/dom/iframe-shim", "../../lib/cmp/widget", "../../lib/cmp/daparser", "../../lib/cmp/auto-render", "../../lib/cmp/mask", "../../lib/util/dom/sticky", "../../lib/util/dom/scroll", "../../lib/util/dom/wheel", "../../lib/cmp/dialog/tpl/dialog", "../../lib/cmp/dialog/tpl/button" ], function(require, exports, module) {
    var $ = require("$");
    var Upload = require("../../lib/util/dom/upload/upload");
    var ConfirmBox = require("../../lib/cmp/dialog/confirm-box");
    // 上传图片错误
    var uploadError = function(error) {
        ConfirmBox.alert({
            content: error
        });
    };
    return function(node, url, size, successFunc) {
        var u = new Upload({
            url: url,
            swf: Global.base_url + "static/swf/swfupload.swf",
            node: node,
            type: "*.png",
            maxSize: "3MB",
            // 文件大小限制
            maxCount: -1,
            // 文件数量限制，-1不限制
            multi: false,
            // 是否允许多文件上传
            max: 2,
            fileName: "image",
            data: {},
            width: 95,
            height: 30
        }).on("overSizeLimit", function(size, file) {
            // 超过大小限制
            uploadError("超过限制大小");
        }).on("zeroSize", function(file) {
            // 空文件
            uploadError("空文件");
        }).on("overCountLimit", function(limit) {
            // 超过数量限制
            uploadError("文件数量超过限制");
        }).on("notAllowType", function(file) {
            // 不允许文件类型
            uploadError("文件类型错误");
        }).on("successAdd", function(file) {
            // 成功加入队列
            u.upload();
        }).on("errorAdd", function(file, files) {
            // 加入队列失败
            uploadError("加入队列失败");
        }).on("success", function(file, data) {
            // 上传成功
            ConfirmBox.alert({
                content: data.msg
            });
            typeof successFunc === "function" && successFunc(size);
        }).on("error", function(file, data) {
            // 文件上传失败时或者被终止时触发，引起的可能性有：上传地址不存在/主动终止
            uploadError("上传失败");
        });
        return u;
    };
});