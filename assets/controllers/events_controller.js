import {Controller} from '@hotwired/stimulus';
import Swiper from 'swiper/bundle';
import {Navigation, Pagination} from 'swiper/modules';
import 'swiper/css/bundle';

export default class extends Controller {
  static targets = ['swiper'];

  connect() {
    const swiper = new Swiper('.swiper', {
      modules: [Navigation, Pagination],
      slidesPerView: 5,
      speed: 10,
      autoplay: true,
      speed: 800,
      spaceBetween: 100,
      autoHeight: false,
      effect: 'slide',
      direction: 'horizontal',
      loop: true,

      // If we need pagination

      // Navigation arrows
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },

      // And if we need scrollbar
      scrollbar: {
        el: '.swiper-scrollbar',
      },
    });
  }

}
