define("dist/app/common/dialog/tpl/select-dept", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <ul id="selectDept" class="ztree"></ul> <div class="sc_footer footer">  <input type="button" value="确定" data-action="nextSC">  <input type="button" value="取消" data-action="cancel"> </div>\'); return _s;').apply(null, v).join("");
        }
    };
});