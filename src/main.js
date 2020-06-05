import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import vuetify from './plugins/vuetify';
import './plugins/vmoney'
import '@babel/polyfill'
import 'roboto-fontface/css/roboto/roboto-fontface.css'
import '@mdi/font/css/materialdesignicons.css'
import '@/assets/custom.css'
import '@/assets/custom-form.css'
import './http'

Vue.config.productionTip = false;
store.subscribe((mutation, state) => { localStorage.setItem('store', JSON.stringify(state)) });
store.dispatch('initialiseStore').then(() => {
  new Vue({
    router,
    store,
    vuetify,
    render: h => h(App)
  }).$mount('#app');
});
