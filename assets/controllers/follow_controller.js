import {Controller} from '@hotwired/stimulus';
import {showToastAuth} from 'app';

export default class extends Controller {
  static targets = ['list'];

  async toggleFollowCommunity(event) {
    const button = event.currentTarget;
    const followIcon = button.querySelector('i');
    const response = await fetch(`/follow/community/${event.params.id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
    });

    if (response.ok) {
      const data = await response.json();

      if (data.isFollowed) {
        button.classList.add('followed');
        followIcon.classList.add('text-danger')
        followIcon.classList.add('bi-x-circle')
        followIcon.classList.remove('text-primary')
        followIcon.classList.remove('bi-plus-circle')
      } else {
        button.classList.remove('following');
        followIcon.classList.remove('text-danger')
        followIcon.classList.remove('bi-x-circle')
        followIcon.classList.add('text-primary')
        followIcon.classList.add('bi-plus-circle')
        button.innerHtml = '<i class="bi  align-self-center"></i>';
      }

      const followerCountBadge = document.querySelector('.followers-badge'); // Select the follower count badge
      if (followerCountBadge) {
        followerCountBadge.textContent = data.followers; // Update the follower count
      }
    } else {
      showToastAuth('Authentication.', 'Please login to follow.')
    }
  }

  async toggleFollowProject(event) {
    const projectId = event.params.id;
    const button = event.currentTarget;

    const response = await fetch(`/follow/project/${projectId}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({follow: !button.classList.contains('followed')})
    });

    if (response.ok) {
      const isFollowing = button.textContent.trim() === 'Following';

      button.textContent = isFollowing ? 'Follow' : 'Following';
      button.classList.toggle('bg-success');
      button.classList.toggle('bg-primary');
      button.classList.toggle('text-success');
      button.classList.toggle('text-primary');
    } else {
      showToastAuth('Authentication.', 'Please login to follow.')
    }
  }

  async toggleFollowUser(event) {
    const userId = event.params.id;
    const button = event.currentTarget;

    try {
      const response = await fetch(`/follow/user/${userId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      });

      if (response.ok) {
        const isFollowing = button.textContent.trim() === 'Following';

        button.textContent = isFollowing ? 'Follow' : 'Following';
        button.classList.toggle('bg-success');
        button.classList.toggle('bg-primary');
        button.classList.toggle('text-success');
        button.classList.toggle('text-primary');
      }else {
        showToastAuth('Authentication.', 'Please login to follow.')
      }
    } catch (error) {
      showToastAuth('Authentication.', 'Please login to follow.')
    }
  }
}
