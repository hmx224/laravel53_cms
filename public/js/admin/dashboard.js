//小时统计
var lineDates = [];
var lineOptions = [];
$.ajax({
    type: "get",
    url: "/admin/hours",
    async: false,
    success: function (res) {
        lineDates = res.dates;
        var lineMax = res.max;
        var lineHour = res.hours;
        var pvData = res.pvs;
        var uvData = res.uvs;
        var ipData = res.ips;
        var rmData = res.rms;

        lineOptions = [
            {
                tooltip: {'trigger': 'axis'},
                legend: {
                    x: 'center',
                    'data': ['PV', 'UV', 'IP', 'RM'],
                    'selected': {
                        'PV': true,
                        'UV': true,
                        'IP': false,
                        'RM': false
                    }
                },
                calculable: true,
                grid: {'y': 30, 'y2': 100},
                xAxis: [{
                    'type': 'category',
                    'axisLabel': {'interval': 0},
                    'data': lineHour
                }],
                yAxis: [
                    {
                        'type': 'value',
                        'max': Math.floor(lineMax / 100 + 1) * 100,
                    },
                    {
                        'type': 'value',
                    }
                ],
                series: [
                    {
                        'name': 'PV', 'type': 'bar',
                        'data': pvData[lineDates[0]]
                    },
                    {
                        'name': 'UV', 'yAxisIndex': 1, 'type': 'bar',
                        'data': uvData[lineDates[0]]
                    },
                    {
                        'name': 'IP', 'yAxisIndex': 1, 'type': 'bar',
                        'data': ipData[lineDates[0]]
                    },
                    {
                        'name': 'RM', 'yAxisIndex': 1, 'type': 'bar',
                        'data': rmData[lineDates[0]]
                    },
                ]
            }
        ];

        for (var i = 1; i < lineDates.length; i++) {
            lineOptions[i] = {
                series: [
                    {'data': pvData[lineDates[i]]},
                    {'data': uvData[lineDates[i]]},
                    {'data': ipData[lineDates[i]]},
                    {'data': rmData[lineDates[i]]},
                ]
            };
        }
    }
});

//地区统计
var mapDates = [];
var mapOptions = [];
$.ajax({
    type: "get",
    url: "/admin/areas",
    async: false,
    success: function (res) {
        mapDates = res.date;
        var mapMax = res.max;
        var mapData = res.data;

        mapOptions = [
            {
                tooltip: {'trigger': 'item'},
                dataRange: {
                    min: 0,
                    max: Math.floor(mapMax / 100 + 1) * 100,
                    text: ['高', '低'],
                    calculable: true,
                    x: 'left',
                    color: ['orangered', 'yellow', 'lightskyblue']
                },
                series: [
                    {
                        'name': 'PV',
                        'type': 'map',
                        'data': mapData[mapDates[0]]
                    }
                ]
            }
        ];

        for (var i = 1; i < mapDates.length; i++) {
            mapOptions[i] = {
                series: [
                    {'data': mapData[mapDates[i]]}
                ]
            };
        }
    }
});

//浏览器统计
var browserLegend = [];
var browserData = [];
$.ajax({
    type: "get",
    url: "/admin/browsers",
    async: false,
    success: function (res) {
        browserLegend = res.browsers;
        browserData = res.data;
    }
});

// 路径配置
require.config({
    paths: {
        echarts: 'http://echarts.baidu.com/build/dist'
    }
});

// 使用
require(
    [
        'echarts',
        'echarts/chart/line',
        'echarts/chart/bar',
        'echarts/chart/pie',
        'echarts/chart/map'
    ],
    function (echarts) {
        var lineChart = echarts.init(document.getElementById('lineChart'), 'macarons');
        var option = {
            timeline: {
                data: lineDates,
                label: {
                    formatter: function (s) {
                        return s.slice(0, 10);
                    }
                },
                autoPlay: true,
                playInterval: 1000
            },
            options: lineOptions
        };

        // 加载数据
        lineChart.setOption(option);
        
        var mapChart = echarts.init(document.getElementById('mapChart'), 'macarons');
        var option = {
            timeline: {
                data: mapDates,
                label: {
                    formatter: function (s) {
                        return s.slice(0, 10);
                    }
                },
                autoPlay: true,
                playInterval: 1000
            },
            options: mapOptions
        };

        // 加载数据
        mapChart.setOption(option);
         
        var pieChart = echarts.init(document.getElementById('pieChart'), 'macarons');
        var option = {
            tooltip: {
                trigger: 'item',
                formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            legend: {
                orient: 'vertical',
                x: 'right',
                data: browserLegend
            },
            calculable: true,
            series: [
                {
                    name: '浏览器',
                    type: 'pie',
                    radius: [30, 110],
                    center: ['50%', 200],
                    roseType: 'area',
                    x: '50%',               // for funnel
                    max: 40,                // for funnel
                    sort: 'ascending',     // for funnel
                    data: browserData
                }
            ]
        };

        // 加载数据
        pieChart.setOption(option);
        window.onresize = function(){
        	lineChart.resize();
        	mapChart.resize();
            pieChart.resize();
        };
    }
);