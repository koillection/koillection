import { Controller } from '@hotwired/stimulus';
import Sortable from "sortablejs";

export default class extends Controller {
    static targets = ['datumInput', 'valueInput', 'operatorInput']

    loadOperatorAndValueInputs(){
        let self = this;

        fetch('/advanced-item-search/load-operator-and-value-inputs/' + this.datumInputTarget.value, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(function(result) {
            let idOperator = self.datumInputTarget.id.replace('_datum', '_operator');
            let nameOperator = self.datumInputTarget.name.replace('[datum]', '[operator]');
            let operatorInputHtml = result.operatorInput.replace(/__id__/g, idOperator).replace(/__name__/g, nameOperator);

            let idValue = self.datumInputTarget.id.replace('_datum', '_value');
            let nameValue = self.datumInputTarget.name.replace('[datum]', '[value]');
            let valueInputHtml = result.valueInput.replace(/__id__/g, idValue).replace(/__name__/g, nameValue);

            self.valueInputTarget.innerHTML = valueInputHtml;
            self.operatorInputTarget.innerHTML = operatorInputHtml;
        })
    }
}
