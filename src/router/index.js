import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Page404 from '../views/404.vue'
import store from '../store'

Vue.use(VueRouter);

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '*',
    name: '404',
    component: Page404
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import(/* webpackChunkName: "login" */ '../views/Login.vue'),
    meta: {
      public: true
    }
  },
  {
    path: '/config',
    name: 'Config',
    component: () => import('../views/Config.vue')
  },
  {
    path: '/servicos',
    name: 'Servicos',
    component: () => import('../views/Servicos.vue')
  },
  {
    path: '/servico/:id?',
    name: 'Servico',
    component: () => import('../views/Servico.vue'),
    props: true
  },
  {
    path: '/cheques',
    name: 'Cheques',
    component: () => import('../views/Cheques.vue')
  },
  {
    path: '/relatorios',
    name: 'Relatorios',
    component: () => import('../views/Relatorios.vue')
  }
];

const router = new VueRouter({routes});

router.beforeEach((to, from, next) => {
  if (to.name !== 'Login' && !to.meta.public && !store.state.token) next({ name: 'Login' });
  else if (to.name === 'Login' && store.state.token) next('/');
  else next();
});

export default router;
