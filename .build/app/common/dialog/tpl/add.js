define("dist/app/common/dialog/tpl/add", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <div class="input_box">  <div>  <label for="parentnode">父节点</label>  <input type="text" name="parentnode" value="\',parentText,\'" id="parentnode" title="\',parentText,\'" disabled="disabled" />  </div>  <div>  <label for="nodename"><b>*</b>名称</label>  <input type="text" name="nodename" id="nodename" placeholder="请填写节点名称" class="gbox" />  </div>  <div>  <label for="deptseq">序号</label>  <input type="text" name="seq" value="1000" id="deptseq" placeholder="请填写节点序号" />  </div>\');if (isLastNode){_s.push(\'  <div>  <label for="fdy">部门长</label>  <input type="text" name="fdy" id="fdy" />  <ul id="searchList"></ul>  </div>\');}_s.push(\' </div>\'); return _s;').apply(null, v).join("");
        }
    };
});