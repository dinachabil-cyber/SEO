import { Controller } from '@hotwired/stimulus';

/**
 * Color Picker Controller
 * Enhances Symfony ColorType inputs with preview swatches and preset colors
 */
export default class extends Controller {
    static targets = ['input'];

    static values = {
        presets: { type: Array, default: ['#fbbf24', '#000000', '#ffffff', '#6b7280', '#1e3a8a'] },
        fieldName: { type: String, default: '' }
    };

    connect() {
        this.enhanceColorInput();
    }

    enhanceColorInput() {
        const colorInput = this.element.querySelector('input[type="color"]');
        if (!colorInput) return;

        // Create wrapper for the input
        const wrapper = document.createElement('div');
        wrapper.className = 'color-input-wrapper';
        
        // Insert wrapper before the input
        colorInput.parentNode.insertBefore(wrapper, colorInput);
        wrapper.appendChild(colorInput);

        // Create preview swatch
        const preview = document.createElement('div');
        preview.className = 'color-preview-swatch';
        preview.style.backgroundColor = colorInput.value || '#ffffff';
        wrapper.appendChild(preview);

        // Create presets container
        const presetsContainer = document.createElement('div');
        presetsContainer.className = 'color-presets-container';
        
        // Add label
        const label = document.createElement('span');
        label.className = 'color-presets-label';
        label.textContent = 'Presets:';
        presetsContainer.appendChild(label);

        // Add preset buttons
        this.presetsValue.forEach(color => {
            const presetBtn = document.createElement('button');
            presetBtn.type = 'button';
            presetBtn.className = `color-preset-btn ${this.getPresetClass(color)}`;
            presetBtn.dataset.color = color;
            presetBtn.title = this.getColorName(color);
            presetBtn.style.backgroundColor = color;
            
            if (color === colorInput.value) {
                presetBtn.classList.add('active');
            }

            presetBtn.addEventListener('click', (e) => {
                e.preventDefault();
                colorInput.value = color;
                preview.style.backgroundColor = color;
                this.updateActivePreset(colorInput, presetsContainer);
                this.dispatchChangeEvent(colorInput);
            });

            presetsContainer.appendChild(presetBtn);
        });

        // Insert presets after the wrapper
        wrapper.parentNode.insertBefore(presetsContainer, wrapper.nextSibling);

        // Add input event listener for live preview
        colorInput.addEventListener('input', () => {
            preview.style.backgroundColor = colorInput.value;
            this.updateActivePreset(colorInput, presetsContainer);
        });

        colorInput.addEventListener('change', () => {
            this.dispatchChangeEvent(colorInput);
        });
    }

    getPresetClass(color) {
        const presetMap = {
            '#fbbf24': 'preset-brand-yellow',
            '#000000': 'preset-black',
            '#ffffff': 'preset-white',
            '#6b7280': 'preset-gray',
            '#1e3a8a': 'preset-dark-blue'
        };
        return presetMap[color] || '';
    }

    getColorName(color) {
        const names = {
            '#fbbf24': 'Brand Yellow',
            '#000000': 'Black',
            '#ffffff': 'White',
            '#6b7280': 'Gray',
            '#1e3a8a': 'Dark Blue'
        };
        return names[color] || color;
    }

    updateActivePreset(colorInput, presetsContainer) {
        const presets = presetsContainer.querySelectorAll('.color-preset-btn');
        presets.forEach(preset => {
            if (preset.dataset.color.toLowerCase() === colorInput.value.toLowerCase()) {
                preset.classList.add('active');
            } else {
                preset.classList.remove('active');
            }
        });
    }

    dispatchChangeEvent(input) {
        // Dispatch event for live preview
        document.dispatchEvent(new CustomEvent('footer-color-change', {
            detail: {
                field: this.fieldNameValue || input.name,
                value: input.value
            }
        }));
    }
}
