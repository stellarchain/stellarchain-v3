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

    event.detail.chart.options.onHover = (event, chartElement) => {
      event.native.target.style.cursor = 'default';
    }
    event.detail.chart.options.plugins.tooltip = {
      enabled: false,
      position: 'nearest',
      external: function (context) {
        // Custom tooltip logic
        const {chart, tooltip} = context;

        // Create tooltip element if it doesn't exist
        let tooltipEl = document.getElementById('chartjs-tooltip');
        if (!tooltipEl) {
          tooltipEl = document.createElement('div');
          tooltipEl.id = 'chartjs-tooltip';
          tooltipEl.className = 'tooltip fade rounded bs-tooltip-top bg-dark'; // Bootstrap classes
          tooltipEl.setAttribute('role', 'tooltip');
          tooltipEl.innerHTML = '<div class="tooltip-body"></div>';
          document.body.appendChild(tooltipEl);
        }

        if (tooltip.opacity === 0) {
          tooltipEl.style.opacity = 0;
          tooltipEl.style.display = 'none';
          return;
        }
        tooltipEl.style.display = 'block';
        const {title, body} = tooltip;
        const titleLines = title || [];
        const bodyLines = body.map(item => item.lines.join('<br>'));

        let innerHtml = '';

        titleLines.forEach(function (title) {
          innerHtml += '<span>Hour: ' + title + ',</span>';
        });

        bodyLines.forEach(function (body, i) {
          const style = `
                    border-width: 2px;
                    display: inline-block;
                    padding: 0.2rem 0.4rem;
                    border-radius: 0.2rem;
                    margin-right: 0.5rem;
          `;
          innerHtml += `<span style="${style}"><strong>${parseFloat(body).toFixed(2)}%</strong></span>`;
        });

        tooltipEl.querySelector('.tooltip-body').innerHTML = innerHtml;

        const position = chart.canvas.getBoundingClientRect();
        tooltipEl.style.opacity = 1;
        tooltipEl.style.position = 'absolute';
        tooltipEl.style.left = `${position.left + window.pageXOffset + tooltip.caretX}px`;
        tooltipEl.style.top = `${position.top + window.pageYOffset + tooltip.caretY - 25}px`;
        tooltipEl.style.font = tooltip.options.bodyFont.family;
        tooltipEl.style.fontSize = tooltip.options.bodyFont.size;
        tooltipEl.style.zIndex = 9999;
      },
    };
  }

}
