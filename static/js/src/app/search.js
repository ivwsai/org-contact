/**
 * @description /search
 * @author <huixiang0922@gmail.com>
 * @date 14-7-17
 */

define(function(require, exports, module) {
    var $ = require('$');
    var Action = require('../lib/util/dom/action');
    var pageCommon = require('./page');

    Action.listen({
        'searchBtn': function(e, node){

            var name = $('#searchText').val();

            if (name === '' || (/^\s+$/.test(name))){
                return false;
            }

            $('#pagination').empty();

            pageCommon.requestSearch(1, true, encodeURI(name));

            $('#searchText').val('');
        }
    })

    //搜索回车事件
    $('body').on('keydown', function(e){
        if (e.keyCode == 13){
            $('.search_btn').click();
        }
    })
});
