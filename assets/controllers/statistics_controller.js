import {Controller} from '@hotwired/stimulus';
import {createChart} from 'lightweight-charts';

export default class extends Controller {
  chart = null;
  areaSeries = null;

  async initialize() {
    this.initChart();
    this.getStatistics();
  }

  initChart() {
    const chartOptions = {
      layout: {
        background: {color: "#222"},
        textColor: "#C3BCDB",
      },
      grid: {
        vertLines: {color: "#444"},
        horzLines: {color: "#444"},
      },
    };
    this.chart = createChart(document.getElementById('stat-chart'), chartOptions);
    this.chart.priceScale().applyOptions({
      borderColor: "#71649C",
    });
    this.chart.timeScale().applyOptions({
      borderColor: "#71649C",
    });
    this.areaSeries = this.chart.addAreaSeries({lineColor: '#ad3333', topColor: '#ad3333', bottomColor: 'rgba(240,128, 128, 0.28)'});
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

    this.areaSeries.setData(data);
    this.chart.timeScale().fitContent();
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
