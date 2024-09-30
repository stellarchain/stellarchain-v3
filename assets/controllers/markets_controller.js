import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
  static targets = ['assetsList'];

  async initialize() {
    this.component = await getComponent(this.element);

    this.component.on('model:set', (model, value, component) => {
      const items = this.assetsListTarget.querySelectorAll('.list-item');
      items.forEach(item => item.remove());
    });
  }

  connect() {
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
    event.detail.chart.options.plugins.crosshair = {
      sync: {
        enabled: false,
        group: 1,
        suppressTooltips: true,
      }
    }
  }
}
