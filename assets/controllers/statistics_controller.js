import {Controller} from '@hotwired/stimulus';
import {createChart, CrosshairMode, LineStyle} from 'lightweight-charts';

export default class extends Controller {
  chart = null;
  areaSeries = null;
  chartContainer = null;
  toolTip = null;

  async initialize() {
    this.initChart();
  }

  initChart() {
    this.chartContainer = document.getElementById('stat-chart');
    const chartOptions = {
      layout: {
        background: {color: "#111112"},
        textColor: "#C3BCDB",
      },
      grid: {
        vertLines: {color: "transparent"},
        horzLines: {color: "transparent"},
      },
      timeScale: {
        timeVisible: true,
        secondsVisible: true,
      }
    };

    this.chart = createChart(this.chartContainer, chartOptions);
    this.areaSeries = this.chart.addAreaSeries({lineColor: '#F23645', topColor: '#8c1d27', bottomColor: 'rgba(20, 20, 20, 0.1)'});
    this.getStatistics();
  }

  async getStatistics() {
    const stat = this.element.dataset.statisticsStatValue;
    const chart = this.element.dataset.statisticsChartValue;

    const response = await fetch(`/statistics/` + stat + `/` + chart, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
    })
    const data = await response.json();
    if (response.status == 200 && data) {
      this.areaSeries.setData(data);
      this.chart.timeScale().fitContent();
    }
    console.log(data);
  }

  connect() {
    this.debouncedFetchMoreData = this.debounce(this.fetchMoreData.bind(this), 500);
  }

  debounce(func, wait) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  fetchMoreData() {

  }
}
