define(function (require, exports, module) {
    var Pagination = require('../../lib/cmp/pagination/pagination');
    var P2 = Pagination.extend({
        attrs: {
            initRun: true
        },
        /**
         * 视图
         * @param flag
         * @returns {*}
         */
        view: function (flag) {
            var html = '<input type="button" class="btn" value="<" data-action="prev"/>';
            html += this.classicHTML();
            html += '<input type="button" class="btn" value=">" data-action="next"/>';
            this.element.html(html);
            this.reflow();
            if(this.get('initRun') && this.get('success')) { // 传递initRun:false
                this.get('success').call(this,this.get('current'));
            }
            this.set('initRun', true);
            return this;
        }
    });
    module.exports = P2;

});
