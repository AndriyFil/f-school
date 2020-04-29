import Vue from 'vue';
import VueRouter from 'vue-router';
// const foo = {template:"<v-alert type='error'>I'm Foo component</v-alert>"}
// const bar = {template:"<v-alert type='error'>I'm Bar component</v-alert>"}
// const user = {template:"<v-alert type='info'>I'm {{$route.params.name}} component</v-alert>"}

import Welcome from "./components/Welcome";
import * as auth from './services/auth_service.js'
Vue.use(VueRouter)
const routes = [
    {
        path: '/'
        , component: Welcome
        , name: 'welcome'

    }
    // , {
    //     path: '/bar'
    //     , component: bar
    // }
    // , {
    //     path: '/user/:name'
    //      , component: user
    // }
]

export default new VueRouter({
    mode: 'history'
    , routes
});
