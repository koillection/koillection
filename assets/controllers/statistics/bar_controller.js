import { Controller } from 'stimulus';
import echarts from "echarts/lib/echarts";
import bar from 'echarts/lib/chart/bar';
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
                formatter: function (params) {
                    return Translator.transChoice('statistics.items_added', params.value);
                }
            },
            color: [this.isDarkMode ? '#00ce99' : this.medium],
            xAxis: {
                type : 'category',
                data: data.map(element => element.abscissa),
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
                splitLine: {
                    lineStyle: {
                        color: this.isDarkMode ? '#7d7f82': '#cccccc'
                    }
                },
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
            series: [{
                type: 'bar',
                data: data.map(element => element.count)
            }]
        });
    }

    resize() {
        this.chart.resize();
    }
}
