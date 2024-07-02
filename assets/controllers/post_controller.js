import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['totalLikes'];

    like() {
      console.log('like')
      this.totalLikesTarget.textContent = 1;
    }

}
