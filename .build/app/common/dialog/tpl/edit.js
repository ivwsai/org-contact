define("dist/app/common/dialog/tpl/edit", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <div class="input_box">  <div>  <label for="parentnode">父节点：</label>  <input type="text" name="parentnode" value="\',parentText,\'" id="parentnode" disabled="disabled" />  </div>  <div>  <label for="nodename"><b>*</b>名称：</label>  <input type="text" name="nodename" id="nodename" value="\',curTitle,\'" />  </div>  <div>  <label for="deptseq">序号</label>  <input type="text" name="seq" id="deptseq" value="\',curSeq,\'" />  </div>\');if (isLastNode){_s.push(\'  <div>  <label for="fdy">部门长：</label>  <input type="text" name="fdy" id="fdy" value="\',managerName,\'" />  <ul id="searchList"></ul>  </div>\');}_s.push(\' </div>\'); return _s;').apply(null, v).join("");
        }
    };
});