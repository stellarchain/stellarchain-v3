import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static values = {
    url: String
  }
  eventSource = null;
  countdownTimer = null;

  connect() {

    this.startCountdown();

    this.eventSource = new EventSource(this.urlValue);
    this.eventSource.onmessage = event => {
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

          const market_info_badge = document.querySelector('.market-info-' + key);
          if (market_info_badge) {
            let badge = market_info_badge.querySelector('.badge')
            badge.classList.remove('text-success', 'text-danger', 'bg-success', 'bg-danger');
            badge.classList.add(`text-${stat.color}`, `bg-${stat.color}`);
            badge.innerHTML = `<i class="bi bi-caret-${stat.caretDirection}-fill"></i> ${stat.percentageChange}%`;
          }
        }
      };
    }
  }

  startCountdown() {
    const countdownElement = document.getElementById("countdown");
    const intervalDuration = 10 * 60 * 1000;

    const now = new Date();
    const currentTime = now.getTime();
    const timeSinceLastInterval = currentTime % intervalDuration;
    const nextInterval = currentTime - timeSinceLastInterval + intervalDuration;

    localStorage.setItem("nextUpdateTime", nextInterval);

    function updateCountdown() {
      const currentTime = new Date().getTime();
      const remainingTime = Math.max(0, nextInterval - currentTime);

      const remainingSeconds = Math.floor(remainingTime / 1000);
      const minutes = Math.floor(remainingSeconds / 60);
      const seconds = remainingSeconds % 60;

      countdownElement.textContent = `${minutes}m ${seconds}s`;

      if (remainingTime <= 0) {
        clearInterval(timer);
        this.startCountdown();
      }
    }
    updateCountdown();
    const timer = setInterval(updateCountdown, 1000);
  }

  disconnect() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource = null;
      console.log("EventSource disconnected.");
    }
  }
}
