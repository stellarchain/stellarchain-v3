import {Controller} from '@hotwired/stimulus';
import {createChart, CrosshairMode, LineStyle} from 'lightweight-charts';

export default class extends Controller {
  chart = null;
  areaSeries = null;
  chartContainer = null;
  toolTip = null;

  async initialize() {
    this.initChart();
    this.getStatistics();
  }

  initChart() {
    const currentLocale = window.navigator.languages[0];
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
      crosshair: {
        // Change mode from default 'magnet' to 'normal'.
        // Allows the crosshair to move freely without snapping to datapoints
        mode: CrosshairMode.Normal,

        // Vertical crosshair line (showing Date in Label)
        vertLine: {
          width: 2,
          color: '#C3BCDB',
          style: LineStyle.Solid,
          labelBackgroundColor: '#9B7DFF',
        },

        horzLine: {
          color: 'green',
          labelBackgroundColor: '#9B7DFF',
        },
      }
    };

    this.chart = createChart(this.chartContainer, chartOptions);
    this.chart.priceScale().applyOptions({
      borderColor: "#71649C",
    });
    this.chart.timeScale().applyOptions({
      borderColor: "#71649C",
    });
    this.areaSeries = this.chart.addAreaSeries({lineColor: '#F23645', topColor: '#8c1d27', bottomColor: 'rgba(20, 20, 20, 0.1)'});

    this.toolTip = document.createElement('div');
    this.toolTip.style = `width: 220px; height: 80px; position: absolute; display: none; padding: 8px; box-sizing: border-box; font-size: 12px; text-align: left; z-index: 1000; top: 12px; left: 12px; pointer-events: none; border: 1px solid; border-radius: 2px;font-family: Roboto, Ubuntu, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;`;
    this.toolTip.style.background = 'black';
    this.toolTip.style.color = 'white';

    this.chartContainer.appendChild(this.toolTip);

    this.chart.subscribeCrosshairMove(param => {
      if (
        param.point === undefined ||
        !param.time ||
        param.point.x < 0 ||
        param.point.y < 0
      ) {
        this.toolTip.style.display = 'none';
      } else {
        let date = new Date(param.time * 1000);
        const dateStr = date.toLocaleDateString(); // This gets the date in a localized format
        const timeStr = date.toLocaleTimeString(); // This gets the time in a localized format
        const dateTimeStr = `${dateStr} ${timeStr}`;
        this.toolTip.style.display = 'block';
        const data = param.seriesData.get(this.areaSeries);
        const price = data.value !== undefined ? data.value : data.close;
        this.toolTip.innerHTML = `<div style="color: ${'rgba( 38, 166, 154, 1)'}"></div><div style="font-size: 24px; margin: 4px 0px; color: ${'white'}">
            ${price}
            </div><div style="color: ${'white'}">
            ${dateTimeStr}
            </div>`;
        const toolTipWidth = 150;
        const toolTipHeight = 80;
        const toolTipMargin = 15;
        const y = param.point.y;
        let left = param.point.x + toolTipMargin;
        if (left > this.chartContainer.clientWidth - toolTipWidth) {
          left = param.point.x - toolTipMargin - toolTipWidth;
        }

        let top = y + toolTipMargin;
        if (top > this.chartContainer.clientHeight - toolTipHeight) {
          top = y - toolTipHeight - toolTipMargin;
        }
        this.toolTip.style.left = left + 'px';
        this.toolTip.style.top = top + 'px';
      }
    });



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
