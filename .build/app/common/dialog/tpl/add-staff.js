define("dist/app/common/dialog/tpl/add-staff", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, 'var _s=[];_s.push(\' <form action="" id="addForm">  <div class="basic_info">  <h2>职员信息</h2>  <ul class="input_box">  <li class="ui-form-item">  <label for="username"><b>*</b>姓名</label>  <input type="text" name="username" id="username" />  </li>  <li class="ui-form-item">  <label for="sex"><b>*</b>性别</label>  <select name="sex" id="sex" style="margin-top: 6px;">  <option value="1">男</option>  <option value="2">女</option>  </select>  </li>  <li class="ui-form-item">  <label for="mobile"><b>*</b>手机</label>  <input type="text" name="mobile" id="mobile" />  </li>  <li class="ui-form-item">  <label for="mail">邮箱</label>  <input type="text" name="mail" id="mail" />  </li>  <li>  <label for="classW">部门</label>  <input type="text" name="classW" id="classW" class="gbox" />  <a href="javascript:;" data-action="selectDept" class="select_class">选择</a>  </li>  <li class="ui-form-item">  <label for="title">职务</label>  <input type="text" name="title" id="title" />  </li>  <li>  <label for="idcard1">证件号</label>  <select name="cardSelectBox" id="cardSelectBox">  <option value="1" selected="selected">身份证号</option>  <option value="2">其他证件号</option>  </select>  <input type="text" name="证件号" id="idcard1" />  </li>  <li class="ui-form-item">  <label for="joindate"><b>*</b>入司时间</label>  <input type="text" name="joindate" value="\',joindate,\'" readonly="true" id="joindate" />  </li>  <li>  <label for="birthday">生日</label>  <input type="text" name="birthday" disabled="disabled" readonly="true" id="birthday" />  </li>  <li>  <label for="seat">座位</label>  <input type="text" name="seat" id="seat" />  </li>  </ul>  </div>  <div class="footer">  <input type="button" data-action="nextAdd" value="确定" />  <input type="button" data-action="cancel" value="取消" />  </div> </form>\'); return _s;').apply(null, v).join("");
        }
    };
});