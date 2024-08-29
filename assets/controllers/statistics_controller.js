import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
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

  _onConnect(event) {
    event.detail.chart.options.plugins.zoom.pan.onPan = ({chart}) => {
      const {min} = chart.scales.x;
      if (min === 0) {
        this.debouncedFetchMoreData(chart);
      }
    };
  }

  debounce(func, wait) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => func.apply(this, args), wait);
    };
  }

  fetchMoreData(chart) {
    const newDataCount = 50;
    const newLabels = [];
    const newData = [];

    const firstLabel = chart.data.labels[0];
    const firstDate = new Date(firstLabel);  // Assuming labels are date strings

    for (let i = 0; i < newDataCount; i++) {
      const newDate = new Date(firstDate);
      newDate.setHours(newDate.getHours() - (i + 1));  // Go back one hour per data point
      newLabels.unshift(newDate.toLocaleString());  // Format date as label
      newData.unshift(Math.random() * (0.1 - 0.09) + 0.09);  // Random price between 50 and 150
    }

    // Prepend the new data to the chart
    chart.data.labels.unshift(...newLabels);
    chart.data.datasets[0].data.unshift(...newData);

    const {min} = chart.scales.x;
    const newMin = newDataCount - min;
    console.log(newMin);
    chart.options.scales.x.min = Math.max(newMin, 0);

    chart.update();
  }
}
