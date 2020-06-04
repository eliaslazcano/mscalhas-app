import Vue from 'vue'
import Vuex from 'vuex'
import http from '../http'
import {JwtHelper} from 'eliaslazcano-helpers'

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    token: null,
    snackbar: null,
    snackbarOptions: null
  },
  mutations: {
    signin: (state, token) => state.token = token,
    signout: state => state.token = null,
    snackbar: (state, payload) => {
      const def = {
        top: false,
        bottom: false,
        left: false,
        right: false,
        timeout: 4500,
        vertical: false,
        multiLine: false,
        color: undefined,
        absolute: false,
        text: ''
      };
      if (payload) {
        state.snackbarOptions = def;
        if (payload.top)        state.snackbarOptions.top       = payload.top;
        if (payload.bottom)     state.snackbarOptions.bottom    = payload.bottom;
        if (payload.left)       state.snackbarOptions.left      = payload.left;
        if (payload.right)      state.snackbarOptions.right     = payload.right;
        if (payload.timeout)    state.snackbarOptions.timeout   = payload.timeout;
        if (payload.vertical)   state.snackbarOptions.vertical  = payload.vertical;
        if (payload.multiLine)  state.snackbarOptions.multiLine = payload.multiLine;
        if (payload.color)      state.snackbarOptions.color     = payload.color;
        if (payload.absolute)   state.snackbarOptions.absolute  = payload.absolute;
        if (payload.text)       state.snackbarOptions.text      = payload.text;
        state.snackbar = true;
      }
      else {
        state.snackbar = null;
        state.snackbarOptions = null;
      }
    }
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
    tokenPayload: state => !state.token ? null : new JwtHelper(state.token).getDados(),
    nameShort: (state, getters) => {
      //Retorna o primeiro e ultimo nome
      if (!state.token) return '';
      const names = getters.tokenPayload.name.split(' ');
      if (names.length === 1) return names[0];
      else return names[0] + ' ' + names[names.length - 1];
    },
    nameInitials: (state, getters) => {
      if (!getters.nameShort) return '';
      const names = getters.nameShort.split(' ');
      if (names.length === 1) return names[0].substr(0,2);
      else return names[0].substr(0,1) + names[names.length - 1].substr(0,1);
    }
  },
  modules: {
  }
})
