/**
 * @description /index
 * @author <huixiang0922@gmail.com>
 * @date 14-7-1 11:02
 */

define(function(require, exports, module) {
    var $ = require('$');
    var ShowInfo = require('./show-info');
    var Placeholder = require('../lib/util/dom/placeholder');
    require('./common/dialog/add-node');
    require('./common/dialog/edit-node');
    require('./common/dialog/del-node');
    require('./common/dialog/add-staff');
    require('./common/dialog/edit-staff');
    require('./common/dialog/del-staff');
    require('./common/dialog/xls-import');
    require('./common/dialog/xls-outport');
    require('./search');
    require('./common/dialog/set-pwd');

    //页面进入时获取相关信息
    $('.person_msg').html(Global.unitname);
    ShowInfo.init();

    Placeholder.render();
});
