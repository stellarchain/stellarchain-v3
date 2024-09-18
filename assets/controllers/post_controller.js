import {Controller} from '@hotwired/stimulus';
import api from '../api.js';
import {Modal} from 'bootstrap';

export default class extends Controller {
  static targets = [];
  async initialize() {
    document.addEventListener('close-modal', () => {
      var myModalEl = document.getElementById('commentModal');
      var modal = Modal.getInstance(myModalEl);
      modal.hide(); // Close the modal
    });
  }
  async vote({currentTarget, params: {id, liked, type}}) {
    try {
      const method = liked ? 'DELETE' : 'POST';
      const response = await api({
        method: method,
        url: '/vote',
        data: {
          entityId: id,
          entityType: type,
        },
      });

      const {totalLikes, liked: newLiked} = await response;

      this.totalLikesTarget.textContent = totalLikes;
      this.heartIconTargets.forEach(icon => {
        icon.classList.toggle('bi-caret-up-fill', newLiked);
        icon.classList.toggle('bi-caret-up', !newLiked);
      });

      currentTarget.dataset.likeLikedParam = newLiked ? 'true' : 'false'
    } catch (error) {
      console.error('Error liking post:', error);
    }
  }
}
