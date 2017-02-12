define("dist/app/common/dialog/tpl/reset-pwd", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <div class="input_box">  <div>  <label for="pwd">重置密码</label>  <input type="password" value="\',initialPwd,\'" name="pwd" id="pwd" class="srbox" style="width: 160px;" />  <input type="checkbox" id="showPwd" style="width: auto;margin-left: 8px;" /> <span class="v">显示密码</span>  </div>  <div class="tip-error dn">密码必须6~12位</div> </div> <div class="footer" style="bottom: 43px;">  <input type="button" value="确定" data-action="reset">  <input type="button" value="取消" data-action="cancel"> </div>\'); return _s;').apply(null, v).join("");
        }
    };
});