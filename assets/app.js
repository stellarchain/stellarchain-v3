import './bootstrap.js';
import './styles/app.css';
import zoomPlugin from 'chartjs-plugin-zoom';
import { Interaction } from 'chart.js';
import {CrosshairPlugin,Interpolate} from 'chartjs-plugin-crosshair';

document.addEventListener("turbo:before-prefetch", (event) => {
  event.preventDefault()
})

window.addEventListener('auth:false', () => showToastAuth())

document.addEventListener('turbo:load', () => {
  const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
  const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
});

document.addEventListener('chartjs:init', function (event) {
  const Chart = event.detail.Chart;
  Chart.register(zoomPlugin);
  Chart.register(CrosshairPlugin);
  Interaction.modes.interpolate = Interpolate;
});

document.addEventListener('DOMContentLoaded', function () {
  const links = document.querySelectorAll('a[href^="#"]');
  links.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const targetId = this.getAttribute('href').substring(1);
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        targetElement.scrollIntoView({behavior: 'smooth'});
      }
    });
  });
});


export function showToastAuth() {
  const toastLiveAuth = document.getElementById('liveToastAuth');
  if (toastLiveAuth) {
    const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveAuth);
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



