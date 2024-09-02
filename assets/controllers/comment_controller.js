import {Controller} from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);
        console.log('comment loaded')
        this.component.on('render:finished', (component) => {
            // do something after the component re-renders
        });
    }
}
