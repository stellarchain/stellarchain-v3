import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["darkModeSwitch", "modal"];

  connect() {
    const htmlElement = document.documentElement;
    const currentTheme = localStorage.getItem('bsTheme') || 'dark';
    htmlElement.setAttribute('data-bs-theme', currentTheme);
    this.darkModeSwitchTarget.checked = currentTheme === 'dark';
    this.modalSettings = new bootstrap.Modal(this.modalTarget, {
      keyboard: false
    })
  }

  toggle() {
    const htmlElement = document.documentElement;
    setTimeout(() => {
      if (this.darkModeSwitchTarget.checked) {
        htmlElement.setAttribute('data-bs-theme', 'light');
        localStorage.setItem('bsTheme', 'light');
        this.darkModeSwitchTarget.checked = false
      } else {
        htmlElement.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('bsTheme', 'dark');
        this.darkModeSwitchTarget.checked = true
      }
    }, 0);
  }

  showModal() {
    this.modalSettings.show()
  }

  closeModal() {
    this.modalSettings.hide()
  }
}
