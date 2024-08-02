import {Controller} from '@hotwired/stimulus';
import Swiper from 'swiper/bundle';
import {Navigation, Pagination} from 'swiper/modules';
import 'swiper/css/bundle';
import 'add-to-calendar-button';

export default class extends Controller {
  static targets = ['swiper'];

  connect() {
    new Swiper('.swiper', {
      modules: [Navigation, Pagination],
      slidesPerView: 5,
      breakpoints: {
        '@0.25': {
          slidesPerView: 1,
          spaceBetween: 50,
        },
        '@0.75': {
          slidesPerView: 2,
          spaceBetween: 50,
        },
        '@1.00': {
          slidesPerView: 3,
          spaceBetween: 200,
        },
        '@1.50': {
          slidesPerView: 5,
        }
      },
      speed: 10,
      autoplay: true,
      speed: 800,
      spaceBetween: 150,
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
