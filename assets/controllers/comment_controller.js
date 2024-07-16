import {Controller} from '@hotwired/stimulus';
import api from '../api.js';

export default class extends Controller {
  static targets = ['reply'];

  toggle(event) {
    event.preventDefault();
    this.replyTarget.classList.toggle("d-none");
  }

  async like({currentTarget, params: {id, liked, type}}) {
    try {
      const method = liked ? 'DELETE' : 'POST';
      const response = await api({
        method: method,
        url: '/like',
        data: {
          entityId: id,
          entityType: type,
        },
      });

      const {totalLikes, liked: newLiked} = await response;

      this.totalLikesTarget.textContent = totalLikes;
      this.heartIconTargets.forEach(icon => {
        icon.classList.toggle('bi-heart-fill', newLiked);
        icon.classList.toggle('bi-heart', !newLiked);
      });

      currentTarget.dataset.likeLikedParam = newLiked ? 'true' : 'false'
    } catch (error) {
      console.error('Error liking post:', error);
    }
  }
}