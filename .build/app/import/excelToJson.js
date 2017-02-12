//excel->jsonList
define("dist/app/import/excelToJson", [], function(require, exports, module) {
    var ExcelFlash = {};
    ExcelFlash.initExcelFlash = function(src, colsNameList, container) {
        _excelGetParam = colsNameList;
        var hasRequestedVersion = DetectFlashVer(10, 0, 0);
        if (hasRequestedVersion) {
            AC_FL_RunContent("src", src.replace(".swf", ""), "container", container, "width", "300", "height", "22", "align", "middle", "id", "excelFlash_2012", "quality", "high", "bgcolor", "#ffffff", "name", "excelFlash_2012", "allowScriptAccess", "sameDomain", "type", "application/x-shockwave-flash", "pluginspage", "http://www.adobe.com/go/getflashplayer");
        } else {
            var alternateContent = "需要flash player10.0及以上版本，<a href=http://www.adobe.com/go/getflash/>点击升级Flash播放器</a>";
            if (container) {
                document.getElementById(container).innerHTML = alternateContent;
            } else {
                document.write(alternateContent);
            }
        }
    };
    module.exports = ExcelFlash;
});

//获取数据
function excelToJson(json) {
    alert(json);
}

//解析开始
function excelToJsonStart(msg) {
    alert(msg);
}

//出错
function excelToJsonError(e) {
    alert(e);
}

//字段
var _excelGetParam = "id,name,count";

function excelGetParam() {
    return _excelGetParam || "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20";
}

// Flash Player Version Detection - Rev 1.6
// Detect Client Browser type
// Copyright(c) 2005-2006 Adobe Macromedia Software, LLC. All rights reserved.
var isIE = navigator.appVersion.indexOf("MSIE") != -1 ? true : false;

var isWin = navigator.appVersion.toLowerCase().indexOf("win") != -1 ? true : false;

var isOpera = navigator.userAgent.indexOf("Opera") != -1 ? true : false;

function ControlVersion() {
    var version;
    var axo;
    var e;
    // NOTE : new ActiveXObject(strFoo) throws an exception if strFoo isn't in the registry
    try {
        // version will be set for 7.X or greater players
        axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
        version = axo.GetVariable("$version");
    } catch (e) {}
    if (!version) {
        try {
            // version will be set for 6.X players only
            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
            // installed player is some revision of 6.0
            // GetVariable("$version") crashes for versions 6.0.22 through 6.0.29,
            // so we have to be careful. 
            // default to the first public version
            version = "WIN 6,0,21,0";
            // throws if AllowScripAccess does not exist (introduced in 6.0r47)        
            axo.AllowScriptAccess = "always";
            // safe to call for 6.0r47 or greater
            version = axo.GetVariable("$version");
        } catch (e) {}
    }
    if (!version) {
        try {
            // version will be set for 4.X or 5.X player
            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
            version = axo.GetVariable("$version");
        } catch (e) {}
    }
    if (!version) {
        try {
            // version will be set for 3.X player
            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
            version = "WIN 3,0,18,0";
        } catch (e) {}
    }
    if (!version) {
        try {
            // version will be set for 2.X player
            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
            version = "WIN 2,0,0,11";
        } catch (e) {
            version = -1;
        }
    }
    return version;
}

// JavaScript helper required to detect Flash Player PlugIn version information
function GetSwfVer() {
    // NS/Opera version >= 3 check for Flash plugin in plugin array
    var flashVer = -1;
    if (navigator.plugins != null && navigator.plugins.length > 0) {
        if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) {
            var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
            var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
            var descArray = flashDescription.split(" ");
            var tempArrayMajor = descArray[2].split(".");
            var versionMajor = tempArrayMajor[0];
            var versionMinor = tempArrayMajor[1];
            var versionRevision = descArray[3];
            if (versionRevision == "") {
                versionRevision = descArray[4];
            }
            if (versionRevision[0] == "d") {
                versionRevision = versionRevision.substring(1);
            } else if (versionRevision[0] == "r") {
                versionRevision = versionRevision.substring(1);
                if (versionRevision.indexOf("d") > 0) {
                    versionRevision = versionRevision.substring(0, versionRevision.indexOf("d"));
                }
            }
            var flashVer = versionMajor + "." + versionMinor + "." + versionRevision;
        }
    } else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.6") != -1) flashVer = 4; else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.5") != -1) flashVer = 3; else if (navigator.userAgent.toLowerCase().indexOf("webtv") != -1) flashVer = 2; else if (isIE && isWin && !isOpera) {
        flashVer = ControlVersion();
    }
    return flashVer;
}

// When called with reqMajorVer, reqMinorVer, reqRevision returns true if that version or greater is available
function DetectFlashVer(reqMajorVer, reqMinorVer, reqRevision) {
    versionStr = GetSwfVer();
    if (versionStr == -1) {
        return false;
    } else if (versionStr != 0) {
        if (isIE && isWin && !isOpera) {
            // Given "WIN 2,0,0,11"
            tempArray = versionStr.split(" ");
            // ["WIN", "2,0,0,11"]
            tempString = tempArray[1];
            // "2,0,0,11"
            versionArray = tempString.split(",");
        } else {
            versionArray = versionStr.split(".");
        }
        var versionMajor = versionArray[0];
        var versionMinor = versionArray[1];
        var versionRevision = versionArray[2];
        // is the major.revision >= requested major.revision AND the minor version >= requested minor
        if (versionMajor > parseFloat(reqMajorVer)) {
            return true;
        } else if (versionMajor == parseFloat(reqMajorVer)) {
            if (versionMinor > parseFloat(reqMinorVer)) return true; else if (versionMinor == parseFloat(reqMinorVer)) {
                if (versionRevision >= parseFloat(reqRevision)) return true;
            }
        }
        return false;
    }
}

function AC_AddExtension(src, ext) {
    if (src.indexOf("?") != -1) return src.replace(/\?/, ext + "?"); else return src + ext;
}

function AC_Generateobj(objAttrs, params, embedAttrs, container) {
    var str = "";
    if (isIE && isWin && !isOpera) {
        str += "<object ";
        for (var i in objAttrs) str += i + '="' + objAttrs[i] + '" ';
        str += ">";
        for (var i in params) str += '<param name="' + i + '" value="' + params[i] + '" /> ';
        str += "</object>";
    } else {
        str += "<embed ";
        for (var i in embedAttrs) str += i + '="' + embedAttrs[i] + '" ';
        str += "> </embed>";
    }
    if (container) {
        document.getElementById(container).innerHTML = str;
    } else {
        document.write(str);
    }
}

function AC_FL_RunContent() {
    var ret = AC_GetArgs(arguments, ".swf", "movie", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
    AC_Generateobj(ret.objAttrs, ret.params, ret.embedAttrs, ret.container);
}

function AC_GetArgs(args, ext, srcParamName, classid, mimeType) {
    var ret = new Object();
    ret.embedAttrs = new Object();
    ret.params = new Object();
    ret.objAttrs = new Object();
    for (var i = 0; i < args.length; i = i + 2) {
        var currArg = args[i].toLowerCase();
        switch (currArg) {
          case "classid":
            break;

          case "pluginspage":
            ret.embedAttrs[args[i]] = args[i + 1];
            break;

          case "src":
          case "movie":
            args[i + 1] = AC_AddExtension(args[i + 1], ext);
            ret.embedAttrs["src"] = args[i + 1];
            ret.params[srcParamName] = args[i + 1];
            break;

          case "container":
            ret.container = args[i + 1];

          case "onafterupdate":
          case "onbeforeupdate":
          case "onblur":
          case "oncellchange":
          case "onclick":
          case "ondblClick":
          case "ondrag":
          case "ondragend":
          case "ondragenter":
          case "ondragleave":
          case "ondragover":
          case "ondrop":
          case "onfinish":
          case "onfocus":
          case "onhelp":
          case "onmousedown":
          case "onmouseup":
          case "onmouseover":
          case "onmousemove":
          case "onmouseout":
          case "onkeypress":
          case "onkeydown":
          case "onkeyup":
          case "onload":
          case "onlosecapture":
          case "onpropertychange":
          case "onreadystatechange":
          case "onrowsdelete":
          case "onrowenter":
          case "onrowexit":
          case "onrowsinserted":
          case "onstart":
          case "onscroll":
          case "onbeforeeditfocus":
          case "onactivate":
          case "onbeforedeactivate":
          case "ondeactivate":
          case "type":
          case "codebase":
            ret.objAttrs[args[i]] = args[i + 1];
            break;

          case "id":
          case "width":
          case "height":
          case "align":
          case "vspace":
          case "hspace":
          case "class":
          case "title":
          case "accesskey":
          case "name":
          case "tabindex":
            ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i + 1];
            break;

          default:
            ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i + 1];
        }
    }
    ret.objAttrs["classid"] = classid;
    if (mimeType) ret.embedAttrs["type"] = mimeType;
    return ret;
}