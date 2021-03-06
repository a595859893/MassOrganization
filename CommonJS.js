function GetXMLHTTPRequest() {
    var xmlhttp;
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp = new XMLHttpRequest();
    } else {// code for IE6, IE5
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    return xmlhttp;
}

function toStr(content) {
    var str = "";
    var fir = true;
    for (var n in content) {
        if (!fir)
            str += "&";
        else
            fir = false;
        str += (n + "=" + content[n]);
    }
    return str;
}

function getValue(par) {
    var local_url = document.location.href;
    var get = local_url.indexOf(par + "=");
    if (get == -1)
        return false;
    var get_par = local_url.slice(par.length + get + 1);
    var nextPar = get_par.indexOf("&");
    if (nextPar != -1)
        get_par = get_par.slice(0, nextPar);
    return get_par;
}

function getCookies(par) {
    var cookie = document.cookie;
    var value = cookie.split("; ");
    for (var i = 0; i < value.length; i++) {
        var arr = value[i].split("=");
        if (par == arr[0])
            return arr[1];
    }
    return false;
}

function delCookie(par) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookies(par);
    if (cval != null)
        document.cookie = par + "=" + cval + ";expires=" + exp.toGMTString();
}

function postMessage(content, php, func, failFunc) {
    var failFunc = failFunc ? failFunc : function (json) {
        console.log(json.error);
    };
    var xmlhttp = GetXMLHTTPRequest();
    xmlhttp.open("POST", php, true);
    xmlhttp.setRequestHeader("CONTENT-TYPE", "application/x-www-form-urlencoded");
    xmlhttp.send(toStr(content));
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var respon = xmlhttp.responseText;
            if (respon) {
                var json = JSON.parse(respon);
                if (json.error || (json.success == false))
                    failFunc(json);
                else
                    func(json);
            } else
                console.log("没有返回值！");
        }
    }
}

function getBase64URL(url, width, height, func) {
    var img = new Image();
    img.src = url;
    img.onload = function () {
        var canvas = document.createElement("canvas");
        canvas.width = width;
        canvas.height = height;
        canvas.getContext("2d").drawImage(img, 0, 0, width, height);
        func(canvas.toDataURL('image/jpeg'));
    };
}

//获取滚动条当前的位置
function getScrollTop() {
    var scrollTop = 0;
    if (document.documentElement && document.documentElement.scrollTop) {
        scrollTop = document.documentElement.scrollTop;
    } else if (document.body) {
        scrollTop = document.body.scrollTop;
    }
    return scrollTop;
}

//获取当前可视范围的高度
function getClientHeight() {
    var clientHeight = 0;
    if (document.body.clientHeight && document.documentElement.clientHeight) {
        clientHeight = Math.min(document.body.clientHeight, document.documentElement.clientHeight);
    } else {
        clientHeight = Math.max(document.body.clientHeight, document.documentElement.clientHeight);
    }
    return clientHeight;
}

//获取文档完整的高度
function getScrollHeight() {
    return Math.max(document.body.scrollHeight, document.documentElement.scrollHeight);
}