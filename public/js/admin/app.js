$.fn.serializeObject = function () {
    var o = {};
    var a = this.serializeArray();
    $.each(a, function () {
        if (o[this.name]) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}

function toast(style, message) {
    toastr.options = {
        'closeButton': true,
        'positionClass': 'toast-bottom-right',
    };
    toastr[style](message);
    return false;
}

var nodeIndex = 0;
function getNodeIndex(id, data) {

    for (var i = 0; i < data.length; i++) {
        if (data[i].id == id) {
            return i;
        }
        nodeIndex++;
        if (data[i].nodes != null && data[i].nodes.length > 0) {
            var ret = getNodeIndex(id, data[i].nodes);
            if (ret >= 0) {
                return nodeIndex;
            }
        }
    }
    return -1;
}

function getTimeIndex(a, data) {

    for (var i = 0; i < data.length; i++) {
        if (data[i].time == a) {
            return i;
        }
        nodeIndex++;
        if (data[i].nodes != null && data[i].nodes.length > 0) {
            var ret = getTimeIndex(a, data[i].nodes);
            if (ret >= 0) {
                return nodeIndex;
            }
        }
    }
    return -1;
}

function booleanFormatter(value, row, index) {
    if (value == 1) {
        return '<i class="fa fa-check"></i>';
    } else {
        return '<i class="fa fa-remove"></i>';
    }
}

