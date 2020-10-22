import { axiosInstance } from 'boot/axios.js'
const state = {
  listaPersonal: []
}

const mutations = {
  loadPersonal (state, lista) {
    state.listaPersonal = lista
  }
}

const actions = {
  loadPersonal ({ commit }, empresa) {
    axiosInstance.get('personal/bd_personal.php/findPersonalCombo', { params: { codEmpresa: empresa } }, { withCredentials: true })
      .then((response) => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', 'loadPersonal' + 'No existen datos', { root: true })
        } else {
          commit('loadPersonal', response.data)
        }
      })
      .catch(error => {
        this.dispatch('mensajeLog/addMensaje', 'loadPersonal' + error, { root: true })
      })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
