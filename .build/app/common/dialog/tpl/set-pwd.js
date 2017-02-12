define("dist/app/common/dialog/tpl/set-pwd", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <ul id="setPwdBox">  <li>  <input type="radio" value="1" name="setpwd" id="randomPwd" /><label for="randomPwd">随机密码</label>  </li>  <li>  <input type="radio" value="-1" name="setpwd" id="birthdayPwd" /><label for="birthdayPwd">身份证上的8位出生年月日</label>  </li>  <li>  <input type="radio" value="-2" name="setpwd" id="idcardLastSixthPwd" /><label for="idcardLastSixthPwd">身份证上的后6位数</label>  </li>  <li>  <input type="radio" value="0" name="setpwd" id="fixedPwd" /><label for="fixedPwd">固定密码</label>  <input type="password" name="pwd" id="pwd" />  <input type="checkbox" name="show_pwd" id="showPwd" /><label for="showPwd">显示密码</label>  </li>  <li class="footer">  <button type="button" name="save" data-action="save">保存</button>  <input type="button" data-action="cancel" value="取消" />  </li> </ul>\'); return _s;').apply(null, v).join("");
        }
    };
});