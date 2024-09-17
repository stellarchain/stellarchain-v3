
import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["darkMode"];

  toggle() {
    const htmlElement = document.documentElement;
    const currentTheme = localStorage.getItem('bsTheme') || 'dark';
    const isDark = currentTheme === 'dark';
    if (isDark) {
        htmlElement.setAttribute('data-bs-theme', 'light');
        localStorage.setItem('bsTheme', 'light');
        this.darkModeTarget.checked = false
      } else {
        htmlElement.setAttribute('data-bs-theme', 'dark');
        localStorage.setItem('bsTheme', 'dark');
        this.darkModeTarget.checked = true
      }
  }
}
