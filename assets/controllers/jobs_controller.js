import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['jobsList'];

  async initialize() {
    this.component = await getComponent(this.element);

    this.component.on('model:set', (model, value, component) => {
      console.log(model, value, component)
      this.jobsListTarget.innerHTML = '';
    });
  }
}
