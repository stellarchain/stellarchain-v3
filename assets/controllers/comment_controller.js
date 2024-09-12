import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
  static targets = ['list'];

  async initialize() {
    this.component = await getComponent(this.element);
    console.log('comment loaded')
    this.component.on('render:finished', (component) => {
      // do something after the component re-renders
    });
  }

  scrollToComment(event) {
    event.preventDefault(); // Prevent default link behavior

    console.log(1)
    const link = event.currentTarget; // The clicked link element
    const commentId = link.getAttribute('href'); // Get the href value (e.g., #comment_123)
    const commentElement = this.listTarget.querySelector(commentId); // Find the comment in the commentList

    if (commentElement) {
      this.listTarget.scrollTo({
        top: commentElement.offsetTop - this.listTarget.offsetTop, // Scroll to the comment relative to the list
        behavior: 'smooth' // Smooth scrolling effect
      });
    }
  }
}
