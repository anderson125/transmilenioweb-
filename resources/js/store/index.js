import Vue from 'vue'
import Vuex from 'vuex'
import api from '../api'

import createPersistedState from "vuex-persistedstate";
import SecureLS from "secure-ls";
var ls = new SecureLS({ isCompression: false });

Vue.use(Vuex);

export default new Vuex.Store({
    plugins: [createPersistedState({
        storage: {
            getItem: (key) => ls.get(key),
            setItem: (key, value) => ls.set(key, value),
            removeItem: (key) => ls.remove(key),
        },
    }),],
    state: {
        user: null,
        token: null,
        auth: false,
    },
    mutations: {
        SET_USER(state, value) {
            state.user = value;
            state.auth = Boolean(value);
        },
        UPDATE_USER_NAME(state, value) {
            state.user.name = value;
        },
        SET_TOKEN(state, value) {
            state.token = value
            sessionStorage.setItem('token', value)
        }
    },
    actions: {
        async login({ commit }, credentials) {
            await api.get("sanctum/csrf-cookie", credentials)
            const res = await api.post("web/login", credentials)
            commit('SET_USER', res.data.response.data);
            commit('SET_TOKEN', res.data.response.token.access_token);
            return res.status
        },
        async logout({ commit }) {
            const res = await api.post("web/logout")
            commit('SET_USER', null);
            commit('SET_TOKEN', null);
            window.localStorage.clear()
            window.sessionStorage.clear()
            return res.status
        },
    },
    getters: {
        currentUserName: (state) => {
            return state.user[0].name ? state.user[0].name : 'Usuario no identificado';
        },
        currentUser: (state) => {
            return state.user
        },
        currentAuth: (state) => {
            return state.auth
        }
    },
    modules: {}
});
