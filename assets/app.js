import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import zoomPlugin from 'chartjs-plugin-zoom';
import {Tooltip, Toast} from 'bootstrap';
import {Interaction} from 'chart.js';
import {CrosshairPlugin, Interpolate} from 'chartjs-plugin-crosshair';
import gradient from 'chartjs-plugin-gradient';
import 'chartjs-adapter-date-fns';

document.addEventListener("turbo:before-prefetch", (event) => {
  event.preventDefault()
})

window.addEventListener('auth:false', (event) => {
  let message = event.detail.message
  let title = event.detail.title
  showToastAuth(title, message)
})

export function truncateMiddle(text, maxLength) {
    if (text.length <= maxLength) return text;

    const halfLength = Math.floor((maxLength - 3) / 2); // Adjust for '...'
    const start = text.slice(0, halfLength);
    const end = text.slice(-halfLength);

    return `${start}...${end}`;
}

function applyTruncateMiddleResponsive() {
    const elements = document.querySelectorAll('.truncate-text');
    elements.forEach(element => {
        const originalText = element.getAttribute('data-full-text') || element.textContent.trim();
        const elementWidth = element.offsetWidth;

        // Dynamically calculate maxLength based on element width (e.g., 1 character per 10px width)
        const maxLength = Math.floor(elementWidth / 13);

        element.setAttribute('data-full-text', originalText);
        element.textContent = truncateMiddle(originalText, maxLength);
    });
}
window.addEventListener('resize', applyTruncateMiddleResponsive);

document.addEventListener('turbo:load', () => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new Tooltip(tooltipTriggerEl))

  const shareModal = document.getElementById('shareModalComment');
  const shareLinkInput = document.getElementById('shareLinkComment');
  const copyButton = document.getElementById('copyButtonComment');

  if (shareModal) {
    shareModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget; // Button that triggered the modal
      const shareUrl = button.getAttribute('data-share-url'); // Extract info from data-* attributes
      shareLinkInput.value = shareUrl; // Update the modal's input with the share URL
    });

    copyButton.addEventListener('click', function () {
      shareLinkInput.select(); // Select the text
      document.execCommand('copy'); // Copy the text to clipboard
      alert('Link copied to clipboard!');
    });
  }

  document.querySelectorAll('.dropdown-toggle-comment').forEach(function (dropdownToggle) {
    dropdownToggle.addEventListener('click', function (event) {
      event.preventDefault(); // Prevents the default anchor behavior

      const dropdownMenu = this.nextElementSibling;
      if (dropdownMenu.classList.contains('show')) {
        dropdownMenu.classList.remove('show');
      } else {
        dropdownMenu.classList.add('show');
      }
    });
  });

  document.addEventListener('click', function (event) {
    document.querySelectorAll('.dropdown-menu.show').forEach(function (openDropdown) {
      if (!openDropdown.contains(event.target) && !openDropdown.previousElementSibling.contains(event.target)) {
        openDropdown.classList.remove('show');
      }
    });
  });


});

document.addEventListener('chartjs:init', function (event) {
  const Chart = event.detail.Chart;
  Chart.register(zoomPlugin);
  Chart.register(CrosshairPlugin);
  Chart.register(gradient);
  Interaction.modes.interpolate = Interpolate;
});

document.addEventListener('DOMContentLoaded', function () {
  const htmlElement = document.documentElement;
  const currentTheme = localStorage.getItem('bsTheme') || 'dark';
  htmlElement.setAttribute('data-bs-theme', currentTheme);
  applyTruncateMiddleResponsive()
});

export function showToastAuth(title = 'Login', message = 'You need to authenticate.') {
  const toastLiveAuth = document.getElementById('liveToastAuth');
  if (toastLiveAuth) {
    const titleElement = toastLiveAuth.querySelector('.toast-header strong');
    const messageElement = toastLiveAuth.querySelector('.toast-body');
    if (titleElement) {
      titleElement.textContent = title;
    }
    if (messageElement) {
      messageElement.textContent = message;
    }
    const toastBootstrap = Toast.getOrCreateInstance(toastLiveAuth);
    toastBootstrap.show();
  } else {
    console.error('Toast element #liveToastAuth not found.');
  }
}

document.addEventListener('turbo:before-fetch-response', function (event) {
  var response = event.detail.fetchResponse;
  if (response.statusCode === 401) {
    showToastAuth();
  }
});

document.addEventListener('turbo:before-stream-render', function (event) {
  var streamElement = event.detail.newStream
  if (streamElement.action == 'append') {
    let commentId = streamElement.getAttribute('comment')
    setTimeout(() => {
      const idElement = 'comment_' + commentId;
      const divElement = document.getElementById(idElement);
      if (divElement) {
        divElement.scrollIntoView({behavior: 'smooth', block: 'start'});
      }
    }, 100)
  }
});

export function timeAgo(ledgerCloseTime) {
  const now = new Date();
  const closeTime = new Date(ledgerCloseTime);
  const diffInSeconds = Math.floor((now - closeTime) / 1000);

  let interval = Math.floor(diffInSeconds / 31536000);
  if (interval >= 1) return interval + (interval === 1 ? " year ago" : " years ago");

  interval = Math.floor(diffInSeconds / 2592000);
  if (interval >= 1) return interval + (interval === 1 ? " month ago" : " months ago");

  interval = Math.floor(diffInSeconds / 604800);
  if (interval >= 1) return interval + (interval === 1 ? " week ago" : " weeks ago");

  interval = Math.floor(diffInSeconds / 86400);
  if (interval >= 1) return interval + (interval === 1 ? " day ago" : " days ago");

  interval = Math.floor(diffInSeconds / 3600);
  if (interval >= 1) return interval + (interval === 1 ? " hour ago" : " hours ago");

  interval = Math.floor(diffInSeconds / 60);
  if (interval >= 1) return interval + (interval === 1 ? " minute ago" : " minutes ago");

  if (diffInSeconds >= 1) return diffInSeconds + (diffInSeconds === 1 ? " second ago" : " seconds ago");

  return "just now";
}
