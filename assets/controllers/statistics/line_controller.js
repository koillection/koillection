import { Controller } from 'stimulus';
import echarts from "echarts/lib/echarts";
import line from 'echarts/lib/chart/line';
import tooltip from 'echarts/lib/component/tooltip';
import Translator from "../../js/translator.min";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    chart = null;
    isDarkMode =  document.getElementById('settings').dataset.theme === 'dark';

    style = getComputedStyle(document.body);
    medium = this.style.getPropertyValue('--medium');

    connect() {
        let data = JSON.parse(this.element.dataset.json);

        this.chart = echarts.init(this.element);
        this.chart.setOption({
            tooltip: {
                trigger: 'axis',
                position: function (params) {
                    return [params[0], '10%'];
                },
                formatter: function (params) {
                    return params[0].axisValue + ': ' + Translator.transChoice('statistics.items', params[0].data);
                }
            },
            color: [this.isDarkMode ? '#00ce99' : this.medium],
            xAxis: {
                type: 'category',
                data: Object.keys(data),
                axisLabel: {
                    textStyle: {
                        color: this.isDarkMode ? '#f0f0f0': '#323233'
                    }
                },
                axisTick: {
                    alignWithLabel: true,
                    lineStyle: {
                        color: this.isDarkMode ? '#f0f0f0': '#323233'
                    }
                },
                axisLine: {
                    lineStyle: {
                        color: this.isDarkMode ? '#f0f0f0': '#323233'
                    }
                }
            },
            yAxis: {
                type: 'value',
                axisLabel: {
                    textStyle: {
                        color: this.isDarkMode ? '#f0f0f0': '#323233'
                    }
                },
                axisTick: {
                    lineStyle: {
                        color: this.isDarkMode ? '#f0f0f0': '#323233'
                    }
                },
                axisLine: {
                    lineStyle: {
                        color: this.isDarkMode ? '#f0f0f0': '#323233'
                    }
                }
            },
            dataZoom: [{
                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                handleSize: '80%',
                handleStyle: {
                    color: '#fff',
                    shadowBlur: 3,
                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                    shadowOffsetX: 2,
                    shadowOffsetY: 2
                }
            }],
            series: [{
                data: Object.values(data),
                type: 'line',
                smooth: true,
                symbol: 'none',
                sampling: 'average',
                areaStyle: {
                    normal: {
                        color: this.isDarkMode ? '#00ce99' : this.medium
                    }
                }
            }]
        });
    }

    resize() {
        this.chart.resize();
    }
}
