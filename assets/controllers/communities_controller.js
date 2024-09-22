import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
  static targets = ['list'];

  async initialize() {
    this.component = await getComponent(this.element);

    this.component.on('render:finished', (component) => {
      console.log(component);
    });
  }

  async toggleFollow(event) {
    console.log(event.params);
    const button = event.currentTarget;
    console.log(button)

    const response = await fetch(`/follow/community/${event.params.id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({follow: !button.classList.contains('followed')})
    });

    if (response.ok) {
      button.classList.toggle('followed');
      button.textContent = button.classList.contains('followed') ? 'Following' : 'Follow';
    }
  }
}
