import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item', 'prototype'];
    static values = {
        index: Number
    };

    connect() {
        console.log('Form field collection controller connected');
        console.log('Item targets:', this.itemTargets.length);
        
        // Initialize index value if not provided
        if (!this.hasIndexValue) {
            this.indexValue = this.itemTargets.length;
        }
        console.log('Current index value:', this.indexValue);
    }

    add(event) {
        event.preventDefault();
        console.log('Add button clicked');
        
        if (!this.hasPrototypeTarget) {
            console.error('Prototype target not found');
            return;
        }
        
        const prototype = this.prototypeTarget;
        console.log('Prototype HTML:', prototype.innerHTML);
        
        const newItem = prototype.innerHTML.replace(/__name__/g, this.indexValue);
        console.log('New item HTML:', newItem);
        
        // Find the add button to insert before it
        const addButton = this.element.querySelector('[data-action="form-field-collection#add"]');
        if (addButton) {
            addButton.insertAdjacentHTML('beforebegin', newItem);
        } else {
            console.error('Add button not found');
            this.element.insertAdjacentHTML('beforeend', newItem);
        }
        
        this.indexValue++;
        console.log('New index value:', this.indexValue);
        
        // Initialize any new elements
        this.initializeNewElements();
    }

    remove(event) {
        event.preventDefault();
        console.log('Remove button clicked');
        
        const item = event.target.closest('[data-form-field-collection-target="item"]');
        if (item) {
            item.remove();
            console.log('Item removed');
        }
    }

    initializeNewElements() {
        console.log('Initializing new elements');
        // Add initialization for any third-party libraries here
    }
}
