// hemos creado este store para crear unas variables globales utiles accesibles desde cualquier componente de la app.
// la mayoria son listas cargadas de la tabla auxiliar de la BD

// en los stores no se ha cargado todavía this.$axios con nuestra configuracion de boot/axios.js, por eso
// lo importo y uso la variable especifica exportada en ese modulo axiosInstance
import { axiosInstance, headerFormData } from 'boot/axios.js'

// state: accesibles en lectura desde componentes a traves de ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaTipoAcc'])
const state = {
  listaUsers: [], // [{id, codEmpresa, email, username, idPersonal}]
  listaSINO: [{ id: '1', desc: 'SI' }, { id: '0', desc: 'NO' }],
  listaMonedas: ['EUROS', 'DOLAR'],
  listaTipoAcc: [],
  listaTiposActivo: [],
  listaTiposProducto: [],
  listaEstadosActivo: [],
  listaTipoOPeracion: [],
  listaTiposFactura: [],
  listaEstadosFactura: [],
  listaTipoEntidad: [],
  listaEmpresas: [],
  listaTiposJornada: [],
  listaEstadosJornada: [],
  listaRoles: [],
  listaMeses: [] // meses de movimientos: {mes: 01/2020}, {mes: 02/2020}
}
// mutations: solo están accesibles a las actions a traves de commit, p.e., commit('loadUsers')
const mutations = {
  loadUsers (state, users) {
    state.listaUsers = users
  },
  loadTipoAcc (state, tiposAcc) {
    state.listaTipoAcc = tiposAcc
  },
  loadListaRoles (state, tiposAcc) {
    state.listaRoles = tiposAcc
  },
  loadTiposActivo (state, tiposAcc) {
    state.listaTiposActivo = tiposAcc
  },
  loadEstadosActivo (state, tiposAcc) {
    state.listaEstadosActivo = tiposAcc
  },
  loadTipoOperacion (state, tiposAcc) {
    state.listaTipoOperacion = tiposAcc
  },
  loadTiposProducto (state, tiposAcc) {
    state.listaTiposProducto = tiposAcc
  },
  listaTiposFactura (state, tiposAcc) {
    state.listaTiposFactura = tiposAcc
  },
  listaEstadosFactura (state, tiposAcc) {
    state.listaEstadosFactura = tiposAcc
  },
  loadEmpresas (state, tiposAcc) {
    state.listaEmpresas = tiposAcc
  },
  loadListaMeses (state, meses) {
    state.listaMeses = meses
  },
  loadTipoEntidad (state, tiposEnt) {
    state.listaTipoEntidad = tiposEnt
  },
  loadTiposJornada (state, lista) {
    state.listaTiposJornada = lista
  },
  loadEstadosJornada (state, lista) {
    state.listaEstadosJornada = lista
  }
}
// actions: accesibles desde componentes a traves de ...mapActions('tablaAux', ['loadTablasAux'])
// actualmente se esta llamando desde components/mainTabs.vue, es decir, cuando se pasa la validacion de usuario
const actions = {
  loadTablasAux ({ commit }) {
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 9, mutation: 'loadTipoAcc' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 1, mutation: 'loadListaRoles' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 5, mutation: 'loadTipoOperacion' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 4, mutation: 'loadTiposActivo' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 6, mutation: 'loadTiposProducto' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 3, mutation: 'loadEstadosActivo' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 7, mutation: 'listaTiposFactura' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 10, mutation: 'listaEstadosFactura' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 11, mutation: 'loadTiposJornada' })
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 12, mutation: 'loadEstadosJornada' })
    this.dispatch('tablasAux/loadUsers')
    this.dispatch('tablasAux/loadListaMeses')
  },
  loadEmpresas ({ commit }) {
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 8, mutation: 'loadEmpresas' })
  },
  loadTipoEntidad ({ commit }) {
    this.dispatch('tablasAux/loadTablaAux', { codTabla: 2, mutation: 'loadTipoEntidad' })
  },
  loadUsers ({ commit }) {
    axiosInstance.get('users/bd_users.php/findUsersFilter/', {}, { withCredentials: true })
      .then((response) => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', 'loadUsers' + 'No existen datos', { root: true })
        } else {
          commit('loadUsers', response.data)
        }
      })
      .catch(error => {
        this.dispatch('mensajeLog/addMensaje', 'loadUsers' + error, { root: true })
      })
  },
  loadTablaAux ({ commit }, tabAux) { // tabAux: { codTabla: x, mutation: 'mutation' }
    axiosInstance.get(`tablaAuxiliar/bd_tablaAuxiliar.php/findTablaAuxFilter?codTabla=${tabAux.codTabla}`, {}, { withCredentials: true }) // tipo acciones
      .then((response) => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', tabAux.mutation + 'No existen datos', { root: true })
        } else {
          commit(tabAux.mutation, response.data)
        }
      })
      .catch(error => {
        this.dispatch('mensajeLog/addMensaje', tabAux.mutation + error, { root: true })
      })
  },
  borrarTablaAux ({ commit }, id) {
    return axiosInstance.delete(`tablaAuxiliar/bd_tablaAuxiliar.php/findTablaAuxFilter/${id}`, { params: { id: id } }, { withCredentials: true })
  },
  addTablaAux ({ commit }, record) {
    var formData = new FormData()
    for (var key in record) {
      formData.append(key, record[key])
    }
    return axiosInstance.post('tablaAuxiliar/bd_tablaAuxiliar.php/guardarBD', formData, headerFormData)
  },
  actualizarTablaAux ({ commit }, record) {
    return axiosInstance.put(`tablaAuxiliar/bd_tablaAuxiliar.php/findTablaAuxFilter/${record.id}`, record, { withCredentials: true })
  },
  loadListaMeses ({ commit }) {
    axiosInstance.get('movimientos/bd_movimientos.php/findMesesMovimientos/', {}, { withCredentials: true })
      .then(response => {
        if (response.data.length === 0) {
          this.dispatch('mensajeLog/addMensaje', 'loadListaMeses' + 'No existen datos', { root: true })
        } else {
          commit('loadListaMeses', response.data) // array {mes: 01/2020}, {mes: 02/2020}
        }
      })
      .catch(error => {
        this.dispatch('mensajeLog/addMensaje', 'loadListaMeses' + error, { root: true })
      })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
