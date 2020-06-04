import Vue from 'vue'
import axios from 'axios'
import store from '../store'

const http = axios.create({
  baseURL: 'http://localhost:8000/',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
});

http.interceptors.request.use(config => {
  const token = store.state.token;
  if (token) config.headers.Authorization = `Bearer ${token}`;
  return config;
}, error => Promise.reject(error));

http.interceptors.response.use(res => res, error => {
  if (error.response) {
    if (error.response.data && error.response.data.mensagem) store.commit('snackbar', {text: error.response.data.mensagem, color: 'error'});
  } else if (error.request) {
    if (error.request.status === 0) store.commit('snackbar', {text: 'Sem conexão', color: 'error'});
    else store.commit('snackbar', {text: 'Erro ' + error.request.status});
  } else {
    // eslint-disable-next-line no-console
    console.log(error.message);
  }
  return Promise.reject(error);
});

Vue.prototype.$http = http;
export default http;