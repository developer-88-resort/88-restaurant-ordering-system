import './bootstrap';
import '@hotwired/turbo';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
