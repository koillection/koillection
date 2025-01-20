import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['typeInput', 'datumInput', 'datumInputContainer', 'valueInputContainer', 'operatorInputContainer']

    loadTypeInputs(){
        let self = this;

        if (this.typeInputTarget.value === '') {
            self.operatorInputContainerTarget.innerHTML = '';
            self.valueInputContainerTarget.innerHTML = '';
            self.datumInputContainerTarget.innerHTML = '';

            return;
        }

        fetch('/advanced-item-search/load-type-inputs/' + this.typeInputTarget.value, {
            method: 'GET'
        })
            .then(response => response.json())
            .then(function(result) {
                self.operatorInputContainerTarget.innerHTML = '';
                self.valueInputContainerTarget.innerHTML = '';
                self.datumInputContainerTarget.innerHTML = '';


                if (result.operatorInput) {
                    let idOperator = self.typeInputTarget.id.replace('_type', '_operator');
                    let nameOperator = self.typeInputTarget.name.replace('[type]', '[operator]');
                    self.operatorInputContainerTarget.innerHTML = result.operatorInput.replace(/__id__/g, idOperator).replace(/__name__/g, nameOperator);
                }

                if (result.valueInput) {
                    let idValue = self.typeInputTarget.id.replace('_type', '_value');
                    let nameValue = self.typeInputTarget.name.replace('[type]', '[value]');
                    self.valueInputContainerTarget.innerHTML = result.valueInput.replace(/__id__/g, idValue).replace(/__name__/g, nameValue);
                }

                if (result.datumInput) {
                    let idValue = self.typeInputTarget.id.replace('_type', '_datum');
                    let nameValue = self.typeInputTarget.name.replace('[type]', '[datum]');
                    self.datumInputContainerTarget.innerHTML = result.datumInput.replace(/__id__/g, idValue).replace(/__name__/g, nameValue);
                }
            })
    }

    loadDatumInputs(){
        let self = this;

        fetch('/advanced-item-search/load-datum-inputs/' + this.datumInputTarget.value, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(function(result) {
            self.operatorInputContainerTarget.innerHTML = '';
            self.valueInputContainerTarget.innerHTML = '';

            let idOperator = self.typeInputTarget.id.replace('_type', '_operator');
            let nameOperator = self.typeInputTarget.name.replace('[type]', '[operator]');
            let operatorInputHtml = result.operatorInput.replace(/__id__/g, idOperator).replace(/__name__/g, nameOperator);

            let idValue = self.typeInputTarget.id.replace('_type', '_value');
            let nameValue = self.typeInputTarget.name.replace('[type]', '[value]');
            let valueInputHtml = result.valueInput.replace(/__id__/g, idValue).replace(/__name__/g, nameValue);

            self.valueInputContainerTarget.innerHTML = valueInputHtml;
            self.operatorInputContainerTarget.innerHTML = operatorInputHtml;
        })
    }
}
