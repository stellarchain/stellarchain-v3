import './bootstrap.js';
import './styles/app.css';

document.addEventListener("turbo:before-prefetch", (event) => {
  event.preventDefault()
})

function isSavingData() {
  return navigator.connection?.saveData
}

function hasSlowInternet() {
  return navigator.connection?.effectiveType === "slow-2g" ||
    navigator.connection?.effectiveType === "2g"
}

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))


document.addEventListener('chartjs:init', function (event) {
  const Chart = event.detail.Chart;
  const Tooltip = Chart.registry.plugins.get('tooltip');
  Tooltip.positioners.bottom = function (items) {
    console.log(items)
  };
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

