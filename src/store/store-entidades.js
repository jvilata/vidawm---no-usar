import { axiosInstance } from 'boot/axios.js'
const state = {
  listaEntidades: []
}

const mutations = {
  loadEntidades (state, entidades) {
    state.listaEntidades = entidades
  }
}

const actions = {
  loadEntidades ({ commit }) {
    axiosInstance.get('entidades/bd_entidades.php/findEntidadesCombo/', {}, { withCredentials: true })
      .then((response) => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', 'loadEntidades' + 'No existen datos', { root: true })
        } else {
          commit('loadEntidades', response.data)
        }
      })
      .catch(error => {
        this.dispatch('mensajeLog/addMensaje', 'loadEntidades' + error, { root: true })
      })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
