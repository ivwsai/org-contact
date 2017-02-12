/**
 * @description 奖项设置
 * @author <zx1943h@gmail.com>
 * @date 14-4-8
 */
define("dist/app/common/util", [], function(require, exports, module) {
    var Util = {
        getRankOpt: [ {
            value: 1,
            text: "一等奖"
        }, {
            value: 2,
            text: "二等奖"
        }, {
            value: 3,
            text: "三等奖"
        }, {
            value: 4,
            text: "四等奖"
        }, {
            value: 5,
            text: "五等奖"
        }, {
            value: 6,
            text: "六等奖"
        }, {
            value: 7,
            text: "七等奖"
        }, {
            value: 8,
            text: "八等奖"
        }, {
            value: 9,
            text: "九等奖"
        }, {
            value: 10,
            text: "十等奖"
        } ],
        getRankMap: {
            1: "一等奖",
            2: "二等奖",
            3: "三等奖",
            4: "四等奖",
            5: "五等奖",
            6: "六等奖",
            7: "七等奖",
            8: "八等奖",
            9: "九等奖",
            10: "十等奖"
        },
        /**
         * 获取奖项图片
         */
        getCateImgUrl: function(cid, size) {
            return Global.base_url + "api/jackpot/img" + "?cid=" + cid + "&unitid=" + Global.unitid + "&size=" + size + "&timestamp=" + new Date().getTime();
        },
        /**
         * 获取奖品图片
         */
        getPrizeImgUrl: function(pid, size) {
            return Global.base_url + "api/prize/img" + "?pid=" + pid + "&unitid=" + Global.unitid + "&size=" + size + "&timestamp=" + new Date().getTime();
        },
        /**
         * 获取加载中图片
         */
        getLoadImgUrl: function() {
            return Global.img_url + "loading.gif";
        },
        /**
         * 获取加载中图片
         */
        getBlacklistImgUrl: function() {
            return Global.img_url + "blacklist.png";
        },
        /**
         * 获取当前页码
         */
        getCurrentPage: function(pagination, pageSize, page) {
            if (Util.isNum(page)) {
                return page;
            } else if (page === "current") {
                // 跳转到当前页
                return pagination.get("current");
            } else if (page === "last") {
                // 跳转到最后一页
                var total = pagination.get("total");
                var currentPage = Math.ceil(+total / pageSize);
                return total % pageSize === 0 ? ++currentPage : currentPage;
            } else if (page === "remove") {
                // 删除后跳转到当前页
                var total = pagination.get("total");
                var currentPage = pagination.get("current");
                return total % pageSize === 1 ? --currentPage : currentPage;
            } else if (page === "removeAll") {
                // 删除所有后跳转到当前页
                var currentPage = pagination.get("current");
                return currentPage === 1 ? currentPage : --currentPage;
            }
            return 0;
        },
        /**
         * 获取表单数据
         */
        getFormData: function(form) {
            var formData = {};
            var arr = form.serializeArray();
            for (var i = 0, len = arr.length; i < len; i++) {
                formData[arr[i].name] = arr[i].value;
            }
            return formData;
        },
        /**
         * 0或正整数
         */
        isNum: function(num) {
            return /^\d+$/.test(num);
        }
    };
    Global.Util = Util;
    module.exports = Util;
});