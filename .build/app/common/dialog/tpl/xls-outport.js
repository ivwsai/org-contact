define("dist/app/common/dialog/tpl/xls-outport", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <p class="sdata">请选择要导出的数据目录：</p> <ul id="outputTree" class="ztree"></ul> <div class="sc_footer footer">  <a href="javascript:;" data-action="startOutport" class="exportBtn">开始导出</a>  <input type="button" value="取消" data-action="cancel" /> </div>\'); return _s;').apply(null, v).join("");
        }
    };
});