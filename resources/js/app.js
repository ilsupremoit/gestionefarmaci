import './bootstrap';
import { createIcons, icons } from 'lucide';

window.lucide = {
    createIcons: (attrs = {}) => createIcons({ icons, attrs })
};

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
