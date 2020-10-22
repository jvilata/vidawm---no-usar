<template>
  <div >
    <q-card flat >
      <q-card-section   class="q-pa-xs">
            <q-item class="q-pa-xs bg-indigo-1 text-grey-8">
              <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
              <q-item-section avatar>
                <div class="row">
                  <q-btn  icon="save"  class="q-ma-xs" :color="colorBotonSave" dense @click="updateRecord" />
                  <q-btn icon="more_vert"  class="q-ma-xs" color="primary" dense>
                    <q-menu ref="menu1">
                      <q-list dense>
                        <q-item
                          v-for="(opcion, index) in listaOpciones"
                          :key="index"
                          clickable
                          v-close-popup
                          @click.native="ejecutarOpcion(opcion)"
                          >
                          <q-item-section avatar>
                            <q-icon :name="opcion.icon" />
                          </q-item-section>
                          <q-item-section>{{opcion.title}}</q-item-section>
                        </q-item>
                      </q-list>
                    </q-menu>
                  </q-btn>
                </div>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-h6">
                  {{ value.nombre }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn
                @click="$emit('close')"
                flat
                round
                dense
                icon="close"/>
              </q-item-section>
            </q-item>
      </q-card-section>
    </q-card>
    <q-scroll-area style="height: calc(100vh - 210px); ">
    <q-card>
      <q-card-section  class="q-pt-none q-pl-xs q-pr-xs">
        <div class="row q-mb-sm">
          <q-input outlined v-model="recordToSubmit.id" label="Id" class="col-xs-4 col-sm-3" />
          <q-select outlined label="Empresa" class="col-xs-8 col-sm-9"
            v-model="recordToSubmit.codEmpresa"
            :options="listaEmpresas"
            option-value="codElemento"
            option-label="valor1"
            emit-value
            map-options
          />
        </div>
        <q-input class="row q-mb-sm" autofocus outlined v-model="recordToSubmit.nombre" label="Nombre"/>
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.carpetaDrive" label="Nombre en OneDrive"/>
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.direccion" label="Direccion"/>
        <div class="row q-mb-sm">
          <q-input class="col-xs-6 col-sm-6" outlined v-model="recordToSubmit.cpostal" label="Cod.Postal"/>
          <q-input class="col-xs-6 col-sm-6" outlined v-model="recordToSubmit.poblacion" label="Población"/>
        </div>
        <div class="row q-mb-sm">
          <q-input class="col-xs-6 col-sm-6" outlined v-model="recordToSubmit.provincia" label="Provincia"/>
          <q-input class="col-xs-6 col-sm-6" outlined v-model="recordToSubmit.pais" label="Pais"/>
        </div>
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.cif" label="CIF"/>
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.cuentaCorriente" label="IBAN"/>
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.logo" label="logo" />
        <q-select
            class="row q-mb-sm"
            label="Tipo Entidad"
            v-model="recordToSubmit.tipoEntidad"
            :options="listaTipoEntidad"
            option-value="codElemento"
            option-label="codElemento"
            emit-value
        />
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.web" label="Web" />
        <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.comentarios" label="Comentarios"
            type="textarea"
            counter
            @keyup.enter.stop />
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="contacto1" label="Contacto" />
          <q-tab name="contacto2" label="Contacto2" />
          <q-tab name="contacto3" label="Contacto3" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="contacto1">
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.personaContacto" label="Persona Contacto" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.cargo" label="Cargo" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.telefono" label="Telefono" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.email" label="Email" />
          </q-tab-panel>
          <q-tab-panel name="contacto2">
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.persona2" label="Persona Contacto" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.cargo2" label="Cargo" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.telefono2" label="Telefono" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.email2" label="Email" />
          </q-tab-panel>
          <q-tab-panel name="contacto3">
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.persona3" label="Persona Contacto" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.cargo3" label="Cargo" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.telefono3" label="Telefono" />
            <q-input class="row q-mb-sm" outlined v-model="recordToSubmit.email3" label="Email" />
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>
    </q-card>
    </q-scroll-area>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { axiosInstance } from 'boot/axios.js'
import { openURL } from 'quasar'
export default {
  props: ['value', 'id', 'keyValue'],
  data () {
    return {
      title: 'Entidades',
      hasChanges: false,
      colorBotonSave: 'primary',
      tab: 'contacto1',
      listaEntidadesFilter: [],
      recordToSubmit: {
        id: -1,
        nombre: '',
        codEmpresa: '01',
        carpetaDrive: '',
        direccion: '',
        cpostal: '',
        poblacion: '',
        provincia: '',
        pais: '',
        cif: '',
        cuentaCorriente: '',
        logo: '',
        tipoEntidad: '',
        web: '',
        comentarios: '',
        personaContacto: '',
        cargo: '',
        telefono: '',
        email: '',
        persona2: '',
        cargo2: '',
        telefono2: '',
        email2: '',
        persona3: '',
        cargo3: '',
        telefono3: '',
        email3: ''
      }, // inicializamos los campos, sino no funciona bien
      listaOpciones: [
        { name: 'carpetaOneDrive', title: 'Carpeta OneDrive', icon: 'cloud', function: 'carpetaOneDrive' }
      ]
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaEmpresas', 'listaTipoEntidad']),
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tablasAux', ['loadTipoEntidad']),
    ...mapActions('entidades', ['loadEntidades']),
    updateRecord () {
      return this.$axios.put(`entidades/bd_entidades.php/findEntidadesFilter/${this.recordToSubmit.id}`, this.recordToSubmit)
        .then(response => {
          this.colorBotonSave = 'primary'
          this.hasChanges = false
          this.$q.notify('Se ha actualizado registro')
          this.loadEntidades()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    ejecutarOpcion (opcion) {
      this[opcion.function](this.value)
      this.$refs.menu1.hide()
    },
    carpetaOneDrive () {
      var url = axiosInstance.defaults.baseURL + 'onedrive/abrirCarpeta.php?empresa=' + this.user.nomEmpresa + '&tipo=ENTIDADES&carpeta=' +
        this.recordToSubmit.carpetaDrive
      this.openWindow(url)
    },
    openWindow (strUrl) {
      if (window.cordova === undefined) { // desktop
        openURL(strUrl)
      } else { // estamos en un disp movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
    }
  },
  mounted () {
    this.loadTipoEntidad() // carga tabla auxiliar
    // Object.assign(this.recordToSubmit, this.value) // v-model: en 'value' podemos leer el valor del v-model
    // no voy a usar el anterior, prefiero buscar de nuevo en la BD
    var objFilter = {
      id: this.value.id
    }
    this.$axios.get(`entidades/bd_entidades.php/findEntidadesFilter/${objFilter.id}`, { params: objFilter })
      .then(response => {
        this.recordToSubmit = response.data[0]
        setTimeout(() => { this.colorBotonSave = 'primary'; this.hasChanges = false }, 50) // dejo pasar un poco porque en el render se modifica el registro
      })
      .catch(error => {
        this.$q.dialog({ title: 'Error', message: error })
      })
  },
  watch: {
    recordToSubmit: { // detecta cambios en las propiedades de este objeto (tienen que estar inicializadas en data())
      handler (val) {
        this.colorBotonSave = 'red'
        this.hasChanges = true
      },
      deep: true
    }
  },
  destroyed () {
    if (this.hasChanges) {
      this.$q.dialog({ title: 'Aviso', message: '¿ Desea guardar cambios ?', ok: true, cancel: true, persistent: true })
        .onOk(() => { this.updateRecord() })
    }
    this.$emit('input', this.recordToSubmit) // v-model: para devolver el valor a atributo 'value', evento input
    this.$emit('changeTab', { idTab: this.value.idTab, filterRecord: {}, registrosSeleccionados: Object.assign({}, this.recordToSubmit) }) // para conservar valores cuando vuelva a selec tab
  }
}
</script>

<style>

</style>
