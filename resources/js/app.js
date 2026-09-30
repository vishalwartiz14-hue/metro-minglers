import './bootstrap';
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js';
import './echo';

window.Alpine = Alpine;
window.Livewire = Livewire;
Livewire.start();

document.addEventListener('DOMContentLoaded', () => {
    if (window.jQuery && window.jQuery.fn.dropify) {
        window.jQuery('.dropify').dropify();
    }

    if (window.flatpickr) {
        window.flatpickr('#date_of_birth', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)),
            disableMobile: true,
            allowInput: false,
        });
    }
});

const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.nav');

menuButton?.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    navigation?.classList.toggle('menu-open', !expanded);
});
