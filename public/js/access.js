function guid() {
    function S4() {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    }

    return (S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4());
}

function setCookie(name, value) {
    var exp = new Date();
    exp.setTime(exp.getTime() + 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + escape(value) + ';path=/;expires=' + exp.toGMTString();
}

function getCookie(name) {
    var arr, reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)');

    if (arr = document.cookie.match(reg))

        return unescape(arr[2]);
    else
        return null;
}

var uvid = getCookie('uvid');
if (uvid == null) {
    setCookie('uvid', guid());
}
var ajax = new XMLHttpRequest();
var url = '/api/access/log?app_key=&url=' + window.location.href + '&title=' + document.title;
ajax.open('get', url);
ajax.send();
