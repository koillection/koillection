import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['filter', 'filtersContainer']

    connect() {
        this.index = this.filterTargets.length;
    }

    add(event) {
        event.preventDefault();
        let prototype = this.element.dataset.prototype;
        let newForm = prototype.replace(/__name__/g, this.index);
        this.filtersContainerTarget.insertAdjacentHTML('beforeend', newForm);
        this.index++;
    }

    remove(event) {
        event.preventDefault();
        event.target.closest('.filter').remove();
    }
}
