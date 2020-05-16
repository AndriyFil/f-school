import Vue from 'vue';
import Vuex from 'vuex';
import * as auth from './services/auth_service.js'
import * as journal from './services/journal_service.js'

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        isLoggedIn: null
        , apiURL: 'http://localhost:8079/api'
        , serverPath: 'http://localhost:8079'
        , profile: {}
        , subjects: {}
    }
    , mutations: {
        authenticate(state, payload) {
            state.isLoggedIn = auth.isLoggedIn();
            if(state.isLoggedIn) {
                state.profile = payload
            } else {
                state.profile = {}
            }
        }
        // , subjects(state, payload) {
        //     state.subjects = journal.getSubjects(payload)
        // }
    }
    , actions: {
        authenticate(context, payload) {
            context.commit('authenticate', payload);
        },
        // subjects(context, payload) {
        //     context.commit('subjects', payload);
        // }
    }
})

