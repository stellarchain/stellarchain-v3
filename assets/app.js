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



