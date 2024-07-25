import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static targets = ['projectsList'];

  async initialize() {
    this.component = await getComponent(this.element);

    this.component.on('model:set', (model, value, component) => {
      console.log(model, value, component)
      if (model !== 'category'){
        this.projectsListTarget.innerHTML = '';
      }
    });
  }
}
