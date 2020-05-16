import Vue from 'vue';
import VueRouter from 'vue-router';
// const foo = {template:"<v-alert type='error'>I'm Foo component</v-alert>"}
// const bar = {template:"<v-alert type='error'>I'm Bar component</v-alert>"}
// const user = {template:"<v-alert type='info'>I'm {{$route.params.name}} component</v-alert>"}

import Journal from "./components/user/Journal";
import * as auth from './services/auth_service.js'
import Welcome from "./components/Welcome";
import App from "./components/App";
Vue.use(VueRouter)
var routes = [];
if(!auth.isLoggedIn()) {
    routes = []
    } else {
        routes = [
            {
                path: '/journal'
                , component: Journal
                , name: 'journal'
            }
        ]
    }
export default new VueRouter({
    mode: 'history'
    , routes
});
