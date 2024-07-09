import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.updateTabFromUrl();
        this.element.addEventListener('shown.bs.tab', this.updateUrl.bind(this));
    }

    updateUrl(event) {
        const tabId = event.target.getAttribute('href');
        history.pushState(null, null, tabId);
    }

    updateTabFromUrl() {
        const hash = window.location.hash;
        if (hash) {
            const tab = this.element.querySelector(`a[href="${hash}"]`);
            if (tab) {
                new bootstrap.Tab(tab).show();
            }
        }
    }
}
