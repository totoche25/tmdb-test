import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
  #modal = null;

  connect() {
    this.#modal = new Modal(this.element);
  }

  open() {
    this.#modal.show();
  }

  close() {
    this.#modal.hide();
  }
}
