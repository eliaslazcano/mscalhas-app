import Vue from 'vue'
import Vuex from 'vuex'
import http from '../http'
import {JwtHelper} from 'eliaslazcano-helpers'

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    token: null
  },
  mutations: {
    signin: (state, token) => state.token = token,
    signout: state => state.token = null
  },
  actions: {
    initialiseStore({state}) {
      if (localStorage.getItem('store')) {
        this.replaceState(Object.assign(state, JSON.parse(localStorage.getItem('store'))));
      }
    },
    login({commit}, {login, senha}) {
      return new Promise((resolve, reject) => {
        http.post('/auth/login', {login, senha})
          .then(r => {
            commit('signin', r.data);
            resolve(r.data);
          })
          .catch(e => {
            console.log(e);
            reject(e);
          });
      });
    }
  },
  getters: {
    logged: state => Boolean(state.token),
    tokenPayload: state => !state.token ? null : new JwtHelper(state.token).getDados()
  },
  modules: {
  }
})
