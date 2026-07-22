import './bootstrap';
import '@hotwired/turbo';

import Alpine from 'alpinejs';
import { initDraftPersistence, readDraft, writeDraft, clearDraft } from './draft-persistence';

window.Alpine = Alpine;

// x-persist="{ key: 'unique-name', paths: ['someArray', 'someFlag'] }"
// Narrow counterpart to draft-persistence.js's generic <form data-draft-key>
// mechanism, for the handful of forms where an Alpine x-for array (menu item
// variants, modifier group options, the staff order cart) needs its rows
// restored into Alpine's reactive data directly — the DOM rows for those
// don't exist until Alpine renders them, so there's nothing for the generic
// DOM-scraping mechanism to restore values onto.
Alpine.directive('persist', (el, { expression }, { effect, cleanup }) => {
    const config = Alpine.evaluate(el, expression);
    if (!config || !config.key || !Array.isArray(config.paths)) return;

    const { key, paths } = config;
    const data = Alpine.$data(el);

    const draft = readDraft(key);
    if (draft) {
        paths.forEach((path) => {
            if (Object.prototype.hasOwnProperty.call(draft, path)) {
                data[path] = draft[path];
            }
        });
    }

    effect(() => {
        const snapshot = {};
        paths.forEach((path) => { snapshot[path] = data[path]; });
        writeDraft(key, snapshot);
    });

    const form = el.closest('form');
    if (form) {
        const clearOnSubmit = () => clearDraft(key);
        form.addEventListener('submit', clearOnSubmit);
        cleanup(() => form.removeEventListener('submit', clearOnSubmit));
    }
});

Alpine.start();

initDraftPersistence();

import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Swal = Swal;

// Registers a one-shot cleanup that runs right before Turbo Drive caches
// the current page (i.e. right when navigating away). Echo listeners and
// setInterval timers started in x-init don't get torn down on their own
// when Turbo swaps the page instead of doing a full reload, so any x-init
// that starts one must pair it with turboCleanup(() => ...) to avoid
// stacking duplicate listeners/timers across repeat visits.
window.turboCleanup = (fn) => document.addEventListener('turbo:before-cache', fn, { once: true });
