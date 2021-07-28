import { Controller } from 'stimulus';
import echarts from "echarts/lib/echarts";
import heatmap from 'echarts/lib/chart/heatmap';
import calendar from 'echarts/lib/component/calendar';
import visualMap from 'echarts/lib/component/visualMap';
import tooltip from 'echarts/lib/component/tooltip';
import Translator from "../../js/translator.min";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    chart = null;
    isDarkMode =  document.getElementById('settings').dataset.theme === 'dark';

    style = getComputedStyle(document.body);
    lightest = this.style.getPropertyValue('--lightest');
    light = this.style.getPropertyValue('--light');
    medium = this.style.getPropertyValue('--medium');
    dark = this.style.getPropertyValue('--dark');

    monthsLabel = [
        Translator.trans('global.months.january'),
        Translator.trans('global.months.february'),
        Translator.trans('global.months.march'),
        Translator.trans('global.months.april'),
        Translator.trans('global.months.may'),
        Translator.trans('global.months.june'),
        Translator.trans('global.months.july'),
        Translator.trans('global.months.august'),
        Translator.trans('global.months.september'),
        Translator.trans('global.months.october'),
        Translator.trans('global.months.november'),
        Translator.trans('global.months.december')
    ];

    connect() {
        let json = JSON.parse(this.element.dataset.json);
        let year = this.element.dataset.year;
        let data = [];

        Object.entries(json).forEach(([index, value]) => {
            data.push([value[0], value[1]]);
        });

        this.chart = echarts.init(this.element);
        this.chart.setOption({
            tooltip: {
                formatter: function (params) {
                    return Translator.transChoice('statistics.items_added', params.value[1]);
                }
            },
            visualMap: {
                type: 'piecewise',
                orient: 'horizontal',
                right: 215,
                bottom: 'bottom',
                pieces: [
                    {min: 31, color: this.isDarkMode ? '#007C5C' : this.dark},
                    {min: 16, max: 30, color: this.isDarkMode ? '#00ce99' : this.medium},
                    {min: 6, max: 15, color: this.isDarkMode ? '#4DDDB8' : this.light},
                    {min: 1, max: 5, color: this.isDarkMode ? '#b3f0e0' : this.lightest},
                    {min: 0, max: 0, color: '#ededed'}
                ],
                textStyle: {
                    color: this.isDarkMode ? '#f0f0f0': '#323233'
                }
            },
            calendar: {
                splitLine: {
                    show: false,
                },
                top: 'middle',
                left: 'center',
                range: year,
                cellSize: 20,
                yearLabel: {show: false},
                itemStyle: {
                    normal: {
                        borderWidth: 2,
                        borderColor: this.isDarkMode ? '#36393e' : '#ffffff',
                        color: this.isDarkMode ? '#7d7f82' : '#ededed'
                    },
                },
                dayLabel: {
                    show: false
                },
                monthLabel: {
                    show: true,
                    nameMap: this.monthsLabel,
                    color: this.isDarkMode ? '#f0f0f0': '#323233'
                }
            },
            series: [
                {
                    type: 'heatmap',
                    coordinateSystem: 'calendar',
                    calendarIndex: 0,
                    data: data
                }
            ]
        });
    }

    resize() {
        this.chart.resize();
    }
}
