import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    url: String
  }

  connect() {
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

}
