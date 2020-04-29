/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import vuetify from "./vuetify";
import router from "./router";

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
// import FlashMessage from "@smartweb/vue-flash-message";
// Vue.use(FlashMessage);

import App from "./components/App";
import Welcome from "./components/Welcome";

import * as auth from './services/auth_service.js'
var route;
if(!auth.isLoggedIn()) {
    route = h => h(Welcome);
} else {
    route = h => h(App);
}
new Vue({
    el: '#app'
    , render: route
    , router
    , vuetify
    // , components: {
    //    'welcome-footer': Footer
    //     , 'welcome-header': Header
    //     , 'welcome-content': Content
    //     , 'user-navbar': Navbar
    //
    // }
});
