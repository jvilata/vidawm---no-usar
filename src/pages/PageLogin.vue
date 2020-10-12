<template>
  <q-page padding class="row justify-center">
    <!-- definimos una pagina de LOGIN que tiene un logo arriba y un formulario de empresa, usu y pass abajo -->
    <div class="gutter-sm" >
      <div class="row justify-center q-pa-lg">
        <q-img src="~assets/VIDA_color.jpg" style="width: 40vh"/>
      </div>
      <q-card>
        <div class="text-subtitle2 q-pt-md q-pl-md">Identificación de usuario</div>
        <form class="q-pa-md" @submit.prevent="loginSubmit">
            <q-select label="Empresa"
              v-model="user.codEmpresa"
              :options="listaEmpresas"
              option-value="codElemento"
              option-label="valor1"
              emit-value
              map-options
            />
            <q-input v-model="user.email" type="email" label="E-Mail" />
            <q-input v-model="user.password" type="password" label="Password" />
            <div class="row justify-center q-pa-md">
              <q-btn type="submit" rounded color="primary" class="full-width" label="Sign In"/>
            </div>
            <p v-if="loggingIn">Cargando...</p>
            <p v-if="loginError">{{ loginError }}</p>
            <p v-if="loginSuccessful">Login Successful</p>
          </form>
      </q-card>
    </div>
  </q-page>
</template>

<script>
import { mapActions, mapState } from 'vuex'
export default {
  data () {
    return {
      user: {
        codEmpresa: '01',
        email: '',
        password: ''
      }
    }
  },
  computed: {
    // accedo a VUEX al store/store-login a estos states y aqui los puedo usar como si fueran variables locales
    ...mapState('login', [
      'loggingIn',
      'loginError',
      'loginSuccessful'
    ]),
    ...mapState('tablasAux', ['listaEmpresas'])
  },
  methods: {
    // accedo a VUEX al store/store-login a la action doLogin y aqui la podemos invocar como this.doLogin
    ...mapActions('login', ['doLogin']),
    ...mapActions('tablasAux', ['loadEmpresas']),
    loginSubmit () {
      this.user.nomEmpresa = this.listaEmpresas.find(emp => emp.codElemento === this.user.codEmpresa).valor1 // nombre de empresa
      this.doLogin(this.user)
    }
  },
  mounted () {
    // inicializar datos de stores
    this.loadEmpresas()
    this.user.email = this.$q.localStorage.getItem('email')
    this.user.password = (this.$q.localStorage.getItem('password')) // from base64 to string
  }
}
</script>
