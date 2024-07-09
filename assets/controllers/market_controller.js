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
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    if (key === 'rank') {
                        element.innerHTML = stat.value;
                    } else if (key === 'circulating_supply') {
                        element.innerHTML = `XLM ${stat.value}`;
                    } else if (key === 'market_cap_dominance') {
                        element.innerHTML = `${stat.value}%`;
                    } else if (key === 'price_usd') {
                        element.innerHTML = `$${stat.value}`;
                    } else {
                        element.innerHTML = `$${stat.value}`;
                    }

                    const prevValue = stat.prev_value;
                    const currentValue = stat.value;
                    const change = prevValue != null ? (currentValue - prevValue) : 0;
                    const percentageChange = prevValue != null && prevValue != 0 ? ((change / prevValue) * 100).toFixed(4) : 0;
                    const caretDirection = change < 0 ? 'down' : 'up';
                    const color = change < 0 ? 'danger' : 'success';
                    const displayChange = key === 'rank' ? percentageChange.replace('-', '') : percentageChange;

                    const badge = element.closest('.list-group-item').querySelector('.badge');
                    if (badge) {
                        badge.classList.remove('text-success', 'text-danger', 'bg-success', 'bg-danger');
                        badge.classList.add(`text-${color}`, `bg-${color}`);
                        badge.innerHTML = `<i class="bi bi-caret-${caretDirection}-fill"></i> ${displayChange}%`;
                    }
                }
            }
        };
    }
  }

}
