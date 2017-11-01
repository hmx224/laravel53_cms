@extends('layouts.master')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                订单汇总
            </h1>
            <ol class="breadcrumb">
                <li><a href="/index"><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">订单汇总</li>
            </ol>
        </section>
        @include('orders.query')

        <section class="content">
            <div class="row">
                <div class="col-xs-12">

                    <section class="connectedSortable ui-sortable" style="min-height: 500px;">
                        <div class="nav-tabs-custom" style="border:1px solid #ddd;cursor: move;">
                            <ul class="nav nav-tabs pull-right ui-sortable-handle">
                                <li><a href="#" id="charge" data-toggle="modal"
                                       data-target="#charge_query">查询</a></li>
                                <li class="active">
                                    <a href="#chart1" data-toggle="tab">图表</a>
                                </li>
                                <li class="pull-left header">年报表(充值)</li>
                            </ul>
                            <div class="tab-content no-padding margin-bottom" style="margin-top: 20px;">
                                <div class="chart tab-pane active" id="chart1" style="position: relative;">
                                    <div class="col-xs-4" style="width:37%;">
                                        <table id="chargeTable" data-toggle="table">
                                            <thead>
                                            <tr>
                                                <th data-field="created_at" data-width="60" data-align="center">月份</th>
                                                <th data-field="sum" data-width="60" data-align="center">金额（元）</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="col-xs-8" style="width:63%;">
                                        <div id="chargeChart" style="min-height:380px;border: 1px solid #ccc;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="col-xs-12">

                    <section class="connectedSortable ui-sortable" style="min-height: 500px;">
                        <div class="nav-tabs-custom" style="border:1px solid #ddd;cursor: move;">
                            <ul class="nav nav-tabs pull-right ui-sortable-handle">
                                <li><a href="#" id="buy" data-toggle="modal"
                                       data-target="#buy_query">查询</a></li>
                                <li class="active">
                                    <a href="#chart2" data-toggle="tab">图表</a>
                                </li>
                                <li class="pull-left header">年报表(充值)</li>
                            </ul>
                            <div class="tab-content no-padding margin-bottom" style="margin-top: 20px;">
                                <div class="chart tab-pane active" id="chart2" style="position: relative;">
                                    <div class="col-xs-4" style="width:37%;">
                                        <table id="buyTable" data-toggle="table">
                                            <thead>
                                            <tr>
                                                <th data-field="created_at" data-width="60" data-align="center">月份</th>
                                                <th data-field="sum" data-width="60" data-align="center">金额（元）</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="col-xs-8" style="width:63%;">
                                        <div id="buyChart" style="min-height:380px;border: 1px solid #ccc;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </div>

    <script>
        function chart(id, url, object) {
            var id = document.getElementById(id);
            var chart = echarts.init(id);
            var created_at = [];
            var sum = [];

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    'type': object['type'],
                    'pay_type': object['pay_type'],
                    'product_type':object['product_type'],
                    'start_year': object['start_year'],
                    'end_year': object['end_year'],
                    '_token': '{{ csrf_token() }}',
                },
                success: function (data) {
                    $(data.rows).each(function (k, obj) {
                        created_at.push(obj.created_at);
                        sum.push(obj.sum);
                    });
                },
                error: function () {
                    alert('系统繁忙')
                },
                complete: function () {
                    var option = {
                        tooltip: {},
                        legend: {
                            data: ['金额']
                        },
                        xAxis: {
                            data: created_at
                        },
                        yAxis: {},
                        series: [{
                            name: '金额',
                            type: 'bar',
                            itemStyle: {
                                normal: {
                                    color: '#00a65a'
                                }
                            },
                            data: sum
                        }]
                    };
                    // 使用刚指定的配置项和数据显示图表。
                    chart.setOption(option);
                }
            });
        }


        $('#chargeTable').bootstrapTable({
            method: 'get',
            url: '/admin/orders/chargeTable',
//            pagination: true,
//            pageNumber: 1,
//            pageSize: 12,
//            pageList: ['All'],
            sidePagination: 'server',
            striped: true,
            queryParams: function (params) {
                var object = new Object();

                object['type'] = '{{ App\Models\Order::TYPE_CHARGE }}';
                object['start_year'] = $('#start_year').val();
                object['end_year'] = $('#end_year').val();
                object['pay_type'] = $('#pay_type').val();
                object['_token'] = '{{ csrf_token() }}';
                object['offset'] = params.offset;
                object['limit'] = params.limit;

                chart('chargeChart','/admin/orders/chargeTable', object);
                return object;
            },
        });

        $(".common").delegate('#charge_confirm', "click", function () {
            $('#chargeTable').bootstrapTable('refresh', {silent: true});

            $('#charge_query').modal('hide')
        });

        $('#charge').click(function () {
            $('#modal_title').text('充值查询');
            $('#buy_html').hide();
            $('#charge_html').show();
            $('.common').prop('id', 'charge_query');
            $('.query').prop('id', 'charge_confirm');
        });


        $('#buyTable').bootstrapTable({
            method: 'get',
            url: '/admin/orders/buyTable',
//            pagination: true,
//            pageNumber: 1,
//            pageSize: 12,
//            pageList: ['All'],
            sidePagination: 'server',
            striped: true,
            queryParams: function (params) {
                var object = new Object();

                object['type'] = '{{ App\Models\Order::TYPE_BUY }}';
                object['start_year'] = $('#start_year').val();
                object['end_year'] = $('#end_year').val();
                object['product_type'] = $('#product_type').val();
                object['_token'] = '{{ csrf_token() }}';
                object['offset'] = params.offset;
                object['limit'] = params.limit;

                chart('buyChart','/admin/orders/buyTable', object);
                return object;
            },
        });

        $(".common").delegate('#buy_confirm', "click", function () {
            $('#buyTable').bootstrapTable('refresh', {silent: true});

            $('#buy_query').modal('hide')
        });

        $('#buy').click(function () {
            $('#modal_title').text('消费查询');
            $('#charge_html').hide();
            $('#buy_html').show();
            $('.common').prop('id', 'buy_query');
            $('.query').prop('id', 'buy_confirm');
        });
    </script>

@endsection