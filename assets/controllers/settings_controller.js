import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
  static targets = ["modal"];

  connect() {
    this.modalSettings = new bootstrap.Modal(this.modalTarget, {
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
