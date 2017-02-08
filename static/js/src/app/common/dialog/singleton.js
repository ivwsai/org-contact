/**
 * @description 单例弹窗，防止多次触发出现多个弹窗
 * @author <caolvchong@gmail.com>
 * @date 14-3-3.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var ConfirmBox = require('../../../lib/cmp/dialog/confirm-box');

    var CB = ConfirmBox.extend({
        show: function(config) {
            if(this.get('visible')) {
                this.hide();
            }
            CB.superclass.show.call(this, config);
            return this;
        }
    });

    var ins;
    CB.show = function(config) {
        if(!ins) {
            ins = new CB(config);
        }
        return ins.show(config);
    };


    module.exports = CB;
});