// hemos creado este store para que centralice la escritura de mensajes de log de la aplicación.
// De momento sólo los escribe en consola y los guarda en un una lista en memoria. En el futuro se podría volcar a BD.

// state: accesibles en lectura desde componentes a traves de ...mapState('log', ['listaMensajes'])
const state = {
  listaMensajes: []
}
// mutations: solo están accesibles a las actions a traves de commit, p.e., commit('addMensaje', param)
const mutations = {
  addMensaje (state, mensaje) {
    state.listaMensajes.push(mensaje)
  }
}
// actions: accesibles desde componentes a traves de ...mapActions('log', ['addMensaje'])
const actions = {
  addMensaje ({ commit }, mensaje) {
    commit('addMensaje', mensaje)
    console.log('[', new Date(), '] ', mensaje)
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
