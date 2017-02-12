define("dist/app/common/dialog/tpl/search", [], function() {
    return {
        render: function(map) {
            var p = [], v = [];
            for (var i in map) {
                p.push(i);
                v.push(map[i]);
            }
            return new Function(p, "var _s=[];_s.push('');for(var key=0; key<sinfo.length;key+=1){var msg=sinfo[key];_s.push(' <li name=\"',msg.user_id,'\">',msg.name);if (msg.deptname !== \"\"){_s.push('  - ',msg.deptname);}_s.push(' </li>');} return _s;").apply(null, v).join("");
        }
    };
});