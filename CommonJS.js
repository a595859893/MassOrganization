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