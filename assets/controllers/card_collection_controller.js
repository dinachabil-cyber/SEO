import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['item', 'items'];
    static values = {
        index: Number,
        prototype: String
    };

    connect() {
        console.log('Card collection controller connected');
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
        
        if (!this.hasPrototypeValue) {
            console.error('Prototype value not found');
            return;
        }
        
        const prototype = this.prototypeValue;
        console.log('Prototype HTML:', prototype);
        
        const newItem = prototype.replace(/__name__/g, this.indexValue);
        console.log('New item HTML:', newItem);
        
        // Find the items container to append to
        if (this.hasItemsTarget) {
            this.itemsTarget.insertAdjacentHTML('beforeend', newItem);
        } else {
            console.error('Items target not found');
            // Fallback to inserting before the add button
            const addButton = this.element.querySelector('[data-action="card-collection#add"]');
            if (addButton) {
                addButton.insertAdjacentHTML('beforebegin', newItem);
            } else {
                console.error('Add button not found');
                this.element.insertAdjacentHTML('beforeend', newItem);
            }
        }
        
        this.indexValue++;
        console.log('New index value:', this.indexValue);
        
        // Initialize any new elements
        this.initializeNewElements();
    }

    remove(event) {
        event.preventDefault();
        console.log('Remove button clicked');
        
        const item = event.target.closest('[data-card-collection-target="item"]');
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
