// hemos creado este store para que centralice la escritura de mensajes de log de la aplicación.
// De momento sólo los escribe en consola y los guarda en un una lista en memoria. En el futuro se podría volcar a BD.

// en los stores no se ha cargado todavía this.$axios con nuestra configuracion de boot/axios.js, por eso
// lo importo y uso la variable especifica exportada en ese modulo axiosInstance
import { axiosInstance, headerFormData } from 'boot/axios.js'
import { LocalStorage } from 'quasar'
// state: accesibles en lectura desde componentes a traves de ...mapState('login', ['user'])
const state = {
  loggingIn: false,
  loginError: null,
  loginSuccessful: false,
  user: {} // { codEmpresa, nomEmpresa, user: { id, codEmpresa, email, username, idPersonal }, pers: { id, codEmpresa, nombre, nombreAbreviado, email }}
}
// mutations: solo están accesibles a las actions a traves de commit, p.e., commit('loginStart')
const mutations = {
  loginStart: state => { state.loggingIn = true },
  loginStop: (state, errorMessage) => {
    state.loggingIn = false
    state.loginError = errorMessage
    state.loginSuccessful = !errorMessage
  },
  setUser: (state, updated) => {
    Object.assign(state.user, updated)
  },
  desconectar: (state) => {
    state.user = {}
  }
}
// actions: accesibles desde componentes a traves de ...mapActions('login', ['doLogin'])
const actions = {
  doLogin ({ commit }, loginData) {
    commit('loginStart')
    // para esta llamada al backend es necesario pasar los parametros en formato formData (campos de formulario)
    // tenemos que convertir los atributos del objeto loginData a campos (fields) de un formData
    loginData.action = 'login' // añadimos el atributo action que espera el backend
    var formData = new FormData()
    for (var key in loginData) {
      formData.append(key, loginData[key])
    }
    axiosInstance.post('/users/users.php', formData, headerFormData)
      .then((response) => {
        var str = response.data.replace(/'/g, '"') // el JSON que devuelve no es correcto porque es con comillas simples y hay que pasarlo a dobles
        var user = JSON.parse(str)
        if (user.failure) {
          commit('loginStop', 'Credenciales incorrectas. Inténtelo de nuevo')
        } else {
          // si el usuario existe, busco sus datos personales
          axiosInstance.get('personal/bd_personal.php/findPersonalEmail', { params: { email: user.data.email } }, { withCredentials: true })
            .then((response) => {
              if (response.data.length === 0) {
                commit('loginStop', 'No existe persona asociada al usuario. Inténtelo de nuevo')
              } else {
                LocalStorage.set('email', loginData.email)
                LocalStorage.set('password', loginData.password)
                commit('setUser', { codEmpresa: formData.get('codEmpresa'), nomEmpresa: formData.get('nomEmpresa'), user: user.data, pers: response.data[0] }) // llamo a mutation->setUser, en user tengo el login y en pers los datos personales
                this.dispatch('tablasAux/loadTablasAux', formData.get('codEmpresa')) // cargar tablas auxiliares
                this.dispatch('entidades/loadEntidadSelf', formData.get('codEmpresa')) // cargar entidadSelf
                this.dispatch('entidades/loadEntidadAsesor', formData.get('codEmpresa')) // cargar entidadAsesor
                this.$router.push('/sinTabs')
              }
            })
            .catch(error => {
              commit('loginStop', error) // .response.data.error
            })
        }
      })
      .catch(error => {
        commit('loginStop', error) // .response.data.error
      })
  },
  desconectarLogin ({ commit }) {
    // cerramos todos los tabs y redirigimos al login
    this.dispatch('tabs/removeAllTabs', [], { root: true })
    commit('loginStop', 'Introduzca credenciales')
    commit('desconectar')
    this.$router.push('/')
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}
