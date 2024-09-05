import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    url: String
  }

  connect() {
    this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
    this.element.addEventListener('chartjs:connect', this._onConnect.bind(this)); // Bind the context here

    const eventSource = new EventSource(this.urlValue);
    eventSource.onmessage = event => {
      const data = JSON.parse(event.data);
      for (const key in data) {
        if (data.hasOwnProperty(key)) {
          const stat = data[key];
          const data_el = document.querySelector(`[data-stat="${key}"]`);
          if (data_el) {
            if (key === 'rank') {
              data_el.innerHTML = stat.value;
            } else if (key === 'circulating_supply') {
              data_el.innerHTML = `XLM ${stat.value}`;
            } else if (key === 'market_cap_dominance') {
              data_el.innerHTML = `${stat.value}%`;
            } else {
              data_el.innerHTML = `$${stat.value}`;
            }

          }

          const badge = document.querySelector('.market-info-' + key).querySelector('.badge');
          if (badge) {
            badge.classList.remove('text-success', 'text-danger', 'bg-success', 'bg-danger');
            badge.classList.add(`text-${stat.color}`, `bg-${stat.color}`);
            badge.innerHTML = `<i class="bi bi-caret-${stat.caretDirection}-fill"></i> ${stat.percentageChange}%`;
          }
        }
      };
    }
  }


  disconnect() {
    this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
    this.element.removeEventListener('chartjs:connect', this._onConnect.bind(this));
  }

  _onPreConnect(event) {
  }

  _onConnect(event) {
    event.detail.chart.options.plugins.tooltip = {
      enabled: false,
      callbacks: {
        label: function (tooltipItem) {
          return tooltipItem.raw.toFixed(6);
        },
      },
    };

    event.detail.chart.options.plugins.tooltips = {
      enabled: false,
      custom: function (tooltipModel) {
        console.log(tooltipModel)
      },
    };

  }

}
