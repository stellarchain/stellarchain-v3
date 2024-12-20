import {Controller} from '@hotwired/stimulus';
import {createChart, CrosshairMode, LineStyle} from 'lightweight-charts';

export default class extends Controller {
  chart = null;
  areaSeries = null;
  chartContainer = null;
  toolTip = null;
  loadingStatistics = null;
  currentStartTime = null;
  timeFrame = '10m';

  async initialize() {
    this.initChart();
  }


  setTimeFrame(event) {
    const selectedTimeFrame = event.target.textContent.trim();
    this.timeFrame = selectedTimeFrame;

    console.log(`Timeframe changed to: ${this.timeFrame}`);

    const timestampInSeconds = Math.floor(Date.now() / 1000);

    this.chart.remove();
    this.initChart();

    this.getStatistics(timestampInSeconds);

    const buttons = event.currentTarget.parentElement.children;
    Array.from(buttons).forEach(button => {
      button.classList.remove('bg-primary');
      button.classList.add('bg-black');
    });

    event.target.classList.add('bg-primary');
    event.target.classList.remove('bg-black');
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
      },
      timeScale: {
        timeVisible: true,      // Ensures time is shown
        secondsVisible: true,   // Ensures seconds are shown if desired
      },
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
    this.toolTip.style = `width: 250px; height: 80px; position: absolute; display: none; padding: 8px; box-sizing: border-box; font-size: 12px; text-align: left; z-index: 1000; top: 12px; left: 12px; pointer-events: none; border: 1px solid; border-radius: 2px;font-family: Roboto, Ubuntu, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;`;
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
        this.toolTip.style.display = 'block';
        const data = param.seriesData.get(this.areaSeries);

        let date = new Date(data.time * 1000);
        const dateStr = date.toLocaleDateString(); // This gets the date in a localized format
        const timeStr = date.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
        const dateTimeStr = `${dateStr} ${timeStr}`;
        const price = data.value.toLocaleString()
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

    this.chart.timeScale().subscribeVisibleLogicalRangeChange((logicalRange) => {
      if (logicalRange.from < 0 && this.areaSeries.data()) {
        let startTime = this.areaSeries.data()[0].time
        if (!this.loadingStatistics && startTime !== this.currentStartTime) {
          this.getStatistics(startTime);
          this.currentStartTime = startTime;
        }
      }
    });
    const timestampInSeconds = Math.floor(Date.now() / 1000);
    this.getStatistics(timestampInSeconds).then(() => {
      this.chart.timeScale().fitContent();
    });
  }

  async getStatistics(startTime) {
    const stat = this.element.dataset.statisticsStatValue;
    document.getElementById('loading-statistics').classList.remove('d-none');
    this.loadingStatistics = true;
    const response = await fetch(`/statistics/` + stat, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json', // Ensure the content type is JSON
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        startTime: startTime,
        timeFrame: this.timeFrame,
      })
    })
    const newData = await response.json();
    if (response.status == 200) {
      const existingData = this.areaSeries.data();
      const mergedData = [...existingData, ...newData].reduce((acc, item) => {
        if (!acc.find(d => d.time === item.time)) {
          acc.push(item);
        }
        return acc;
      }, []);

      mergedData.sort((a, b) => a.time - b.time);
      this.areaSeries.setData(mergedData);

    }

    this.loadingStatistics = false;
    document.getElementById('loading-statistics').classList.add('d-none');
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
