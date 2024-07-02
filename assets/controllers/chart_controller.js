import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  connect() {
    this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
    this.element.addEventListener('chartjs:connect', this._onConnect);
  }

  disconnect() {
    // You should always remove listeners when the controller is disconnected to avoid side effects
    this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
    this.element.removeEventListener('chartjs:connect', this._onConnect);
  }

  _onPreConnect(event) {
  }

  _onConnect(event) {
    console.log(event.detail.chart); // You can access the chart instance using the event details
  }
}
