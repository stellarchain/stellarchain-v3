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
        background: {color: "#222"},
        textColor: "#C3BCDB",
      },
      grid: {
        vertLines: {color: "#444"},
        horzLines: {color: "#444"},
      },
      crosshair: {
        // Change mode from default 'magnet' to 'normal'.
        // Allows the crosshair to move freely without snapping to datapoints
        mode: CrosshairMode.Normal,

        // Vertical crosshair line (showing Date in Label)
        vertLine: {
          width: 8,
          color: '#C3BCDB44',
          style: LineStyle.Solid,
          labelBackgroundColor: '#9B7DFF',
        },

        // Horizontal crosshair line (showing Price in Label)
        horzLine: {
          color: '#9B7DFF',
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
    this.areaSeries = this.chart.addAreaSeries({lineColor: '#ad3333', topColor: '#ad3333', bottomColor: 'rgba(240,128, 128, 0.28)'});

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
        const dateStr = date.toDateString();
        this.toolTip.style.display = 'block';
        const data = param.seriesData.get(this.areaSeries);
        const price = data.value !== undefined ? data.value : data.close;
        this.toolTip.innerHTML = `<div style="color: ${'rgba( 38, 166, 154, 1)'}"></div><div style="font-size: 24px; margin: 4px 0px; color: ${'white'}">
            ${price}
            </div><div style="color: ${'white'}">
            ${dateStr}
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
