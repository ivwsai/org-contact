define("dist/app/common/dialog/tpl/import-list", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, "var _s=[];_s.push('');if (typeof type == 'object'){_s.push(' <li class=\"err\">  <span>',msglist.workid,'</span>  <span>',msglist.username,'</span>  <span>',msglist.joindate,'</span>  <span>',msglist.mobilephone,'</span>  <span>',msglist.cardno,'</span>  <span>FALSE（',errmsg,'）!</span> </li>');}else{_s.push(' <li>  <span>',msglist.workid,'</span>  <span>',msglist.username,'</span>  <span>',msglist.joindate,'</span>  <span>',msglist.mobilephone,'</span>  <span>',msglist.cardno,'</span>  <span>',type,'</span> </li>');} return _s;").apply(null, v).join("");
        }
    };
});