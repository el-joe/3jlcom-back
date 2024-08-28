@extends('layouts.main')

@section('title')
    Home
@endsection
@section('content')
    <section class="section">

        <div class="row">
            <div class="col-md-3">
                <a href="{{ url('property') }}">
                    <div class="das-card">
                        <div class="des_icon bg2">
                            <i class="fas fa-car text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Total Properties') }}
                            </div>
                            <div class="data text2">
                                {{ $list['total_properties'] ? $list['total_properties'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('property') . '?category=2' }}">
                    <div class="das-card">
                        <div class="des_icon bg3">
                            <i class="fas fa-car text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Properties For Sell') }}
                            </div>
                            <div class="data text3">
                                {{ $list['total_sell_property'] ? $list['total_sell_property'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('property') . '?category=4' }}">
                    <div class="das-card">
                        <div class="des_icon bg4">
                            <i class="fas fa-taxi text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Properties For Rant') }}
                            </div>
                            <div class="data text4">
                                {{ $list['total_rant_property'] ? $list['total_rant_property'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('property') . '?category=13' }}">
                    <div class="das-card">
                        <div class="des_icon bg1">
                            <i class="fas fa-user text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Properties For Caysh') }}
                            </div>
                            <div class="data text1">
                                {{ $list['total_caysh_property'] ? $list['total_caysh_property'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="row">
            <div class="col-md-3">
                <a href="{{ url('categories') }}">
                    <div class="das-card">
                        <div class="des_icon bg2">
                            <i class="fas fa-car text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Categories') }}
                            </div>
                            <div class="data text2">
                                {{ $list['total_categories'] ? $list['total_categories'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('article')}}">
                    <div class="das-card">
                        <div class="des_icon bg3">
                            <i class="fas fa-car text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Article') }}
                            </div>
                            <div class="data text3">
                                {{ $list['total_articles'] ? $list['total_articles'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('customer') . '?role=1' }}">
                    <div class="das-card">
                        <div class="des_icon bg4">
                            <i class="fas fa-user text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Total Agents') }}
                            </div>
                            <div class="data text4">
                                {{ $list['total_agents'] ? $list['total_agents'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ url('customer') . '?role=0' }}">
                    <div class="das-card">
                        <div class="des_icon bg1">
                            <i class="fas fa-user text-white"> </i>
                        </div>
                        <div class="des_info">
                            <div class="title-text">
                                {{ __('Total Customers') }}
                            </div>
                            <div class="data text1">
                                {{ $list['total_customer'] ? $list['total_customer'] : 0 }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card proeprty_chart">
                    <div class="card-header d-flex mt-3">
                        <h3>{{ __('Properties') }}</h3>
                        <div class="chart_tab">
                            <nav>
                                <ul class="tabs">
                                    <li class="tab-li">
                                        <a href="#tab1" class="tab-li__link">{{ __('Monthly') }}</a>
                                    </li>
                                    <li class="tab-li">
                                        <a href="#tab1" class="tab-li__link">{{ __('Weekly') }}</a>
                                    </li>
                                    <li class="tab-li">
                                        <a href="#tab1" class="tab-li__link">{{ __('Daily') }}</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row d-flex">
                            <div class="col-md-3 lable_sell d-flex">

                                <div class="des_icon bg1">
                                    <i class="fas fa-car text-white p-3"> </i>
                                </div>

                                <div class="sell_lable_text">
                                    <div class="total_sell">
                                        {{ __("Total Sale") }}
                                    </div>
                                    <div class="no_of_total_sell">
                                        {{ $list['total_sell_property_in_month'] }} {{ __("Of Cars") }}
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-3 lable_rent d-flex">

                                <div class="des_icon bg1">
                                    <i class="fas fa-car text-white p-3"> </i>
                                </div>

                                <div class="rent_lable_text">
                                    <div class="total_sell">
                                        {{ __("Total Caysh") }}
                                    </div>
                                    <div class="no_of_total_sell">
                                        {{ $list['total_caysh_property_in_month'] }} {{ __("Of Cars") }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 lable_rent d-flex">

                                <div class="des_icon bg1">
                                    <i class="fas fa-car text-white p-3"> </i>
                                </div>

                                <div class="rent_lable_text">
                                    <div class="total_sell">
                                        {{ __("Total Rent") }}
                                    </div>
                                    <div class="no_of_total_sell">
                                        {{ $list['total_rant_property_in_month'] }} {{ __("Of Cars") }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-12 page-content">
                                <section id="tab1" data-tab-content>
                                    <div class="chart" id="chart"></div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">{{ __('Categories') }}</div>
                        <div class="card-body">
                            <div id="pie_chart"></div>
                        </div>

                    </div>

                </div>

                <div class="col-md-6">
                    <div class="card most_view">
                        <div class="card-header border-0 pb-0">
                            <h4>{{ __('Most Viewed Properties') }}</h4>

                        </div>
                        <div class="card-body">
                            @foreach ($properties_data as $key => $value)
                                <div class="d-flex align-items-center property_card mt-2">
                                    <div class="property_img">
                                        <img src="{{ $value->title_image ? $value->title_image : url('assets/images/logo/favicon.png') }}"
                                            width="75" alt="">
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $value->title }}</h5>
                                        <div class="font-w600 mb-0">
                                            {{ $value->price }}&nbsp;{{ $settings['currency_symbol'] }}
                                        </div>

                                    </div>
                                    <div class="ms-auto">
                                        <div class="view">
                                            <i class="fbi bi-eye-fill"></i>
                                            <span class="number">{{ $value->total_click }}</span>
                                        </div>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        <input type="hidden" name="" value="{{ $settings['currency_symbol'] }}" id="currency_symbol">
        <input type="hidden" name="map_data" id="map_data" value="{{ $properties }}">


        </div>
    </section>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script src="{{ url('assets/js/query-jvectormap-world-mill-en.js') }}"></script>


    <script>
        var colors = ['red', 'purple', 'black', 'pink', 'orange'];

        var options = {
            series:[{{ implode(',', array_values($category_count)) }}],
            chart: {
                 type: 'donut',
                 height:"700px"
            },
            labels:[{!! implode(',', ($category_name)) !!}],
              plotOptions: {
            },

            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                      width:50,
                      height:20
                    },
                }
            }],
            legend: {
                show: true,
                showForSingleSeries: false,
                showForNullSeries: true,
                showForZeroSeries: true,
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '18px',
                fontFamily: 'Helvetica, Arial',
                fontWeight: 400,
                itemMargin: {
                    horizontal:30,
                    vertical: 10
                }
            }
        };

        var chart1 = new ApexCharts(document.querySelector("#pie_chart"), options);
        chart1.render();

        var myArray =<?php echo json_encode($chartData); ?>;

        const data = {
            Monthly: {
                series1: [{{ implode(',', array_values($chartData['sellmonthSeries'])) }}],
                series2: [{{ implode(',', array_values($chartData['rentmonthSeries'])) }}],
                series3: [{{ implode(',', array_values($chartData['cayshmonthSeries'])) }}],
                categories: [{!! implode(',', $chartData['monthDates']) !!}],

            },
            Weekly: {
                series1:[{{ implode(',', array_values($chartData['sellweekSeries'])) }}],
                series2: [{{ implode(',', array_values($chartData['rentweekSeries'])) }}],
                series3: [{{ implode(',', array_values($chartData['cayshweekSeries'])) }}],
                categories: [{!! implode(',', $chartData['weekDates']) !!}],

            },
            Daily: {
                series1: [{{ implode(',', array_values($chartData['sellcountForCurrentDay'])) }}],
                series2: [{{ implode(',', array_values($chartData['rentcountForCurrentDay'])) }}],
                series3: [{{ implode(',', array_values($chartData['cayshcountForCurrentDay'])) }}],
                categories: [{!! implode(',', $chartData['currentDates']) !!}],

            },
        };

        var chartData = data['Monthly'];
        var options = {
            series: [
                    { name: 'Caysh', data: chartData.series3 },
                    { name: 'Rent', data: chartData.series2 },
                    { name: 'Sell', data: chartData.series1 },
            ],
            chart: {
                height: 350,
                type: 'area',
                zoom: {
                    enabled: false // Disable zooming
                },
                toolbar: {
                    show: false // Hide the toolbar (including download button)
                }
            },
            dataLabels: {
            enabled: false
            },
            stroke: {
                curve: 'smooth'
            },
            xaxis: {
                type: 'date',
                categories:chartData.categories,
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy HH:mm'
                },
            },
            colors: ['#EB9D55', '#47BC78'],
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();

        $(document).ready(function(){

            $('#tab1').attr('class','active');
            chart.render();
        });

        var nestedTabSelect = (tabsElement, currentElement) => {
            const tabs = tabsElement ?? 'ul.tabs';
            const currentClass = currentElement ?? 'active';

            document.querySelectorAll(tabs).forEach(function (tabContainer) {
                let activeLink, activeContent;
                const links = Array.from(tabContainer.querySelectorAll("a"));

                activeLink =links.find(function (link) {
                    return link.getAttribute("href") === location.hash;
                }) || links[0];
                activeLink.classList.add(currentClass);

                activeContent = document.querySelector(activeLink.getAttribute("href"));
                activeContent.classList.add(currentClass);

                links.forEach(function (link) {
                    if (link !== activeLink) {
                            const content = document.querySelector(link.getAttribute("href"));
                            content.classList.remove(currentClass);
                    }
                });

                tabContainer.addEventListener("click", function (e) {
                    if (e.target.tagName === "A") {
                        tab=e.target.text;
                        chartData = data[tab];
                        chart.updateOptions({
                            series: [
                                { name: 'Sell', data: chartData.series1 },
                                { name: 'Rent', data: chartData.series2 },
                                { name: 'Caysh', data: chartData.series3 },
                            ],
                            xaxis:{
                            categories:chartData.categories
                            }

                        });
                        // Make the old tab inactive.
                        activeLink.classList.remove(currentClass);
                        activeContent.classList.remove(currentClass);

                        // Update the variables with the new link and content.
                        activeLink = e.target;
                        activeContent = document.querySelector(activeLink.getAttribute("href"));

                        // Make the tab active.
                        activeLink.classList.add(currentClass);
                        activeContent.classList.add(currentClass);

                        e.preventDefault();
                    }
                });
            });
        };

        nestedTabSelect('ul.tabs', 'active');
    </script>
@endsection
