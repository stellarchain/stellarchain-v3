
import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["darkMode"];

  connect() {
    const htmlElement = document.documentElement;
    const currentTheme = localStorage.getItem('bsTheme') || 'dark';
    htmlElement.setAttribute('data-bs-theme', currentTheme);
    this.darkModeTarget.checked = currentTheme === 'dark';
  }

  toggle() {
    const htmlElement = document.documentElement;
    console.log('aaaa')
    setTimeout(() => {
      if (this.darkModeTarget.checked) {
        htmlElement.setAttribute('data-bs-theme', 'light');
        localStorage.setItem('bsTheme', 'light');
        this.darkModeTarget.checked = false
      } else {
        htmlElement.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('bsTheme', 'dark');
        this.darkModeTarget.checked = true
      }
    }, 0);
  }
}
