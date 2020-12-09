import { axiosInstance } from 'boot/axios.js'
const state = {
  listaEntidades: [],
  entidadSelf: {},
  entidadAsesor: {}
}

const mutations = {
  loadEntidades (state, entidades) {
    state.listaEntidades = entidades
  },
  loadEntidadSelf (state, entidad) {
    state.entidadSelf = entidad
  },
  loadEntidadAsesor (state, entidad) {
    state.entidadAsesor = entidad
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
  },
  loadEntidadSelf ({ commit }, codEmpresa) {
    axiosInstance.get(`entidades/bd_entidades.php/findEntidadesFilter?tipoEntidad=SELF&codEmpresa=${codEmpresa}`, {}, { withCredentials: true })
      .then((response) => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', 'loadEntidades' + 'No existen datos', { root: true })
        } else {
          commit('loadEntidadSelf', response.data[0])
        }
      })
      .catch(error => {
        this.dispatch('mensajeLog/addMensaje', 'loadEntidades self' + error, { root: true })
      })
  },
  loadEntidadAsesor ({ commit }, codEmpresa) {
    axiosInstance.get('entidades/bd_entidades.php/findEntidadesFilter?tipoEntidad=ASESOR', {}, { withCredentials: true })
      .then((response) => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', 'loadEntidades asesor' + 'No existen datos', { root: true })
        } else {
          commit('loadEntidadAsesor', response.data[0])
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
