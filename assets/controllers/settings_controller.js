import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap';

export default class extends Controller {
  static targets = ["modal"];

  connect() {
    this.modalSettings = new Modal(this.modalTarget, {
      keyboard: false
    })
  }

  showModal() {
    this.modalSettings.show()
  }

  closeModal() {
    this.modalSettings.hide()
  }
}
