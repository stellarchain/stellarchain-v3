import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ['totalLikes'];

  async like({params: {id, type}}) {
    try {
      const response = await fetch(`/like`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({entity_id: id, entity_type: type}),
      });

      if (!response.ok) {
        throw new Error('Network response was not ok');
      }

      const {totalLikes} = await response.json();
      this.totalLikesTarget.textContent = totalLikes;
    } catch (error) {
        console.error('Error liking post:', error);
    }
  }

}
