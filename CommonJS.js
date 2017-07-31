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