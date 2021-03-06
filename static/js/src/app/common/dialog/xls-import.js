/**
 * @description 导入
 * @author <huixiang0922@gmail.com>
 * @date 14-7-21.
 */
define(function(require, exports, module) {
    var $ = require('$');
    var Dialog = require('../../../lib/cmp/dialog/dialog');
    var Action = require('../../../lib/util/dom/action');
    var XlsImport = require('./tpl/ag_empl_import2');
    var ExcelTJ = require('../../import/excelToJson');
    var ImportXsl = require('../../import/import');

    Action.listen({

        'ipt': function(e, node){

            var iptStaff = new Dialog({
                title: '导入',
                repositionOnResize: true,
                content: XlsImport.render({base_url: Global.base_url}),
                width: 850,
                height: 650,
                fixed: false,
                hasMask: {
                    hideOnClick: true
                }
            });

            iptStaff.show();
            iptStaff.after('hide', function() {
                this.destroy();
            });

            iptStaff.element.find('.noBtn').click(function(){
                iptStaff.hide();
            })

            ExcelTJ.initExcelFlash(Global.base_url + "/static/js/src/app/import/excelToJson.swf","", 'importBtn');

            ImportXsl.__init(Global.req_url + '/staff/import', function(data, i) {
                return {
                    record: data[i]
                }
            }, 5);

            $('.widget-dialog').on('click', '.nav li', function(){
                $(this).addClass('current').siblings().removeClass('current');

                $('#importMsg .cnt ul').eq($(this).index()).show().siblings().hide()
            })
        }
    })
});
