define(function(){return {render:function(map) {var p=[],v =[];for(var i in map) {p.push(i);v.push(map[i]);}return (new Function(p, "var _s=[];_s.push(' <div class=\"globalBody\">  <div class=\"dtMainBody\">  <div class=\"dtSmallBody\">  <form id=\"importEmployee\" action=\"',base_url,'/api/staff/import\" method=\"post\" name=\"importEmployee\" enctype=\"multipart/form-data\">  <div class=\"bigForm divContentTable\">  <table width=\"100%\">  <tr>  <td>  <h3 class=\"first\">步骤一：准备职员信息</h3>  <p>使用模板文件录入信息。</p>  <p>为保证正确导入，请按照模板内的要求进行填写</p>  <div class=\"btn_area\" style=\"margin-bottom: 30px;\">  <a href=\"',base_url,'/static/excel/template.xls\" class=\"btn\">下载模板</a>  <p>若已准备好数据文件，请直接进行步骤二</p>  </div>  </td>  </tr>  <tr>  <td>  <h3>步骤二：上传数据文件</h3>  <p>点击下方按钮选择文件即可上传。目前支持类型为*.xls。</p>  <div class=\"btn_area\">  <div id=\"importBtn\" class=\"btn\" style=\"width: auto;background: transparent;\">选择文件</div>  <p id=\"fileLj\"></p>  </div>  </td>  </tr>  <tr>  <td class=\"footer\">  <span class=\"aBtn yesBtn\"><input type=\"button\" value=\"开始导入\" onclick=\"startImport()\"  /></span>  <span class=\"aBtn noBtn\"><input type=\"button\" value=\"取消\" /></span>  </td>  </tr>  </table>  </div>  </form>  </div>  <div id=\"processContent\" style=\"display: none;\">  <div style=\"margin-bottom: 20px;\">完成时间：<span id=\"importResultTime\"></span></div>  <div class=\"process ovh\">  <div class=\"gc\">完成：<strong>0%</strong></div>  <div id=\"processBar\" class=\"ovh\">  <div id=\"curProcess\"></div>  </div>  </div>  </div>  <div id=\"importResult\" style=\"display: none; padding: 20px 20px 20px 50px;\">  <div>共读取<span class=\"\" id=\"processData\"></span>条记录，其中：成功<span id=\"importSuccNum\"></span>条，失败<span id=\"importFailNum\" style=\"color: red;\"></span>条</div>  <div id=\"importMsg\">  <div class=\"nav_box\">  <ul class=\"nav\">  <li>全部</li>  <li class=\"current\">失败</li>  <li>成功</li>  </ul>  </div>  <div class=\"cnt\">  <ul id=\"allList\" class=\"dn\"></ul>  <ul id=\"errList\"></ul>  <ul id=\"successList\" class=\"dn\"></ul>  </div>  </div>  <div id=\"showExport\">  <p class=\"cp dn\" onclick=\"exportErrorData()\">将日志保存到本地</p>  <div class=\"footer\">  <span class=\"aBtn noBtn\"><input type=\"button\" value=\"确定\" /></span>  </div>  </div>  </div>  <div id=\"timeout\">  <div>抱歉，由于网络问题导入未成功</div>  <div class=\"footer\">  <span class=\"aBtn yesBtn\"><input type=\"button\" value=\"重新导入\" onclick=\"startImport()\" /></span>  <span class=\"aBtn noBtn\"><input type=\"button\" value=\"取消\" /></span>  </div>  </div>  </div> </div>'); return _s;")).apply(null, v).join("");}};});