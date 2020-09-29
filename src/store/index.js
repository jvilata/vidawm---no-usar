import Vue from 'vue'
import Vuex from 'vuex'

// todos los stores que queramos usar en la app deben importarse aqui y listarlos abajo en modules
import tasks from './store-tasks'
import login from './store-login'
import tabs from './store-tabs'
import tablasAux from './store-tablasAux'
import entidades from './store-entidades'
import activos from './store-activos'
import mensajeLog from './store-log'

Vue.use(Vuex)

/*
 * If not building with SSR mode, you can
 * directly export the Store instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Store instance.
 */

export default function (/* { ssrContext } */) {
  const Store = new Vuex.Store({
    modules: {
      tasks,
      login,
      tabs,
      tablasAux,
      mensajeLog,
      entidades,
      activos
    },

    // enable strict mode (adds overhead!)
    // for dev mode only
    strict: process.env.DEV
  })

  return Store
}
