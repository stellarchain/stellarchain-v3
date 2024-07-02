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


console.log('aaa')
