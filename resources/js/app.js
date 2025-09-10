import './bootstrap';

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

// Import Font Awesome
import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { fas } from '@fortawesome/free-solid-svg-icons'

// Add the solid icons to the library
library.add(fas);

// Import AdminLTE CSS and JS
import 'admin-lte/dist/css/adminlte.min.css'
// import 'admin-lte/plugins/jquery/jquery.min.js';
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'admin-lte/dist/js/adminlte.min.js'


createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./admin/**/*.vue', { eager: true })
    return pages[`./admin/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .component('font-awesome-icon', FontAwesomeIcon)
      .mount(el)
  },
})