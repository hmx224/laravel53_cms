require('./bootstrap');

window.Vue = require('vue');

import VueRouter from 'vue-router';
import router from './routes';
import App from './components/App';
import VueLazyLoad from 'vue-lazyload'

Vue.use(VueRouter);
Vue.use(VueLazyLoad, {
    loading: '/images/mall/loading/loading-bars.svg',
});

new Vue({
    el: '#app',
    router,
    components:{
        App
    }
});
