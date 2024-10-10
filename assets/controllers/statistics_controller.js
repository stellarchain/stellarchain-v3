import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
  async initialize() {
    this.component = await getComponent(this.element);
    this.component.on('render:finished', (component) => {
      console.log('render finished')
    });
  }
  connect() {
    this.debouncedFetchMoreData = this.debounce(this.fetchMoreData.bind(this), 500);
    this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
    this.element.addEventListener('chartjs:connect', this._onConnect.bind(this)); // Bind the context here
  }

  disconnect() {
    this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
    this.element.removeEventListener('chartjs:connect', this._onConnect.bind(this));
  }

  _onPreConnect(event) {
  }

  formatLargeNumber(value) {
    const absValue = Math.abs(value);
    if (absValue >= 1e9) {
      return (value / 1e9).toFixed(1) + 'B'; // Format as billions
    } else if (absValue >= 1e6) {
      return (value / 1e6).toFixed(1) + 'M'; // Format as millions
    } else if (absValue >= 1e3) {
      return (value / 1e3).toFixed(1) + 'K'; // Format as thousands
    } else {
      return value; // No formatting for values less than 1000
    }
  }

  updateChartDataset(newData, event) {
    let chart = event.detail.chart;
    event.detail.chart.data.datasets[0].data = [...newData.chart.datasets[0].data, ...chart.data.datasets[0].data];
    event.detail.chart.data.labels = [...newData.chart.labels, ...chart.data.labels];
    event.detail.chart.update()
  }

  bootstrapEvents(event) {
    let chart = event.detail.chart;
    let chartOptions = chart.options;
    let that = this;

    chart.options.plugins.tooltip.callbacks.title = function (tooltipItems) {
      let statValue = document.getElementById('chart-stat-value');
      statValue.innerHTML = tooltipItems[0].raw;

      if (tooltipItems.length > 0) {
        const item = tooltipItems[0];
        const labels = item.chart.data.labels;
        const labelCount = labels ? labels.length : 0;

        if (this && this.options && this.options.mode === 'dataset') {
          return item.dataset.label || '';
        } else if (item.label) {
          return item.label;
        } else if (labelCount > 0 && item.dataIndex < labelCount) {
          return labels[item.dataIndex];
        }
      }
      return '';
    };

    chartOptions.scales.y.ticks = {
      callback: function (value) {
        return that.formatLargeNumber(value);
      }
    };

    chartOptions.scales.x.ticks = {
      callback: function (value, index, values) {
        const date = new Date(this.getLabelForValue(value))
        if (isNaN(date)) {
          return "Invalid Date";
        }
        return date.toLocaleString('en-US', {
          day: '2-digit',    // Day with leading zero
          month: 'short',    // Short month (Jan, Feb, etc.)
          hour: '2-digit',   // Hour (12-hour clock)
          minute: '2-digit', // Minutes
          hour12: false// AM/PM format
        });
      }
    };

    if (/iPhone|iPad|iPod|Android|webOS|BlackBerry|Windows Phone/i.test(navigator.userAgent) || screen.availWidth < 480) {
      chartOptions.aspectRatio = 1;
    }

    chartOptions.plugins.tooltip.callbacks.title = function (tooltipItems) {
      let statValue = document.getElementById('chart-stat-value');
      statValue.innerHTML = tooltipItems[0].raw.toFixed(6).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });

      if (tooltipItems.length > 0) {
        const item = tooltipItems[0];
        const labels = item.chart.data.labels;
        const labelCount = labels ? labels.length : 0;

        if (this && this.options && this.options.mode === 'dataset') {
          return item.dataset.label || '';
        } else if (item.label) {
          return item.label;
        } else if (labelCount > 0 && item.dataIndex < labelCount) {
          return labels[item.dataIndex];
        }
      }

      return '';
    };

    event.detail.chart.options.plugins.zoom.pan.onPan = ({chart}) => {
      const {min} = chart.scales.x;
      if (min === 0) {
        this.debouncedFetchMoreData();
      }
    };

    event.detail.chart.update();
  }

  _onConnect(event) {
    this.bootstrapEvents(event)
    window.addEventListener('chart_data', (data) => {
      this.updateChartDataset(data.detail, event)
      setTimeout(() => {
        let loading = document.getElementById('loading-chart');
        loading.classList.add('d-none');
      }, 1000)
    });
  }

  debounce(func, wait) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  fetchMoreData() {
    this.component.emit('more')
    let loading = document.getElementById('loading-chart');
    loading.classList.remove('d-none');
  }
}
