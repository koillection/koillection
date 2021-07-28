import { Controller } from 'stimulus';

export default class extends Controller {
    static targets = ['form', 'content'];

    delay = (function(){
        let timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    filter(event) {
        let self = this;

        if (event.target.type === 'text') {
            this.delay(function() {
                self.doAjaxCall();
            }, 250 );
        } else {
            self.doAjaxCall();
        }
    }

    doAjaxCall() {
        let url = window.location.href.split('?')[0];
        let self = this;

        let symbol = '?'
        for (let [key, element] of Object.entries(this.formTarget.elements)) {
            if ((element.type === 'text' && element.value !== '') || (element.type === 'checkbox' && element.checked)) {
                url += symbol + element.name + '=' + element.value;
                symbol = '&';
            }
        }

        let headers = new Headers();
        headers.append("X-Requested-With", "XMLHttpRequest");

        fetch(url, {
            method: 'GET',
            headers: headers
        })
        .then(response => response.text())
        .then(function(results) {
            self.contentTarget.innerHTML = results;
            window.history.pushState(null,'', url);
        })
    }
}
