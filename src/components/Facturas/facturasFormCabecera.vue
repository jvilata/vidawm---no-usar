  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card class="q-pt-none q-pl-xs q-pr-xs">
      <div class="row">
        <q-select
          class="col-xs-6 col-sm-3"
          outlined
          label="Tipo Factura"
          stack-label
          v-model="recordToSubmit.tipoFactura"
          :options="listaTiposFactura"
          option-value="codElemento"
          option-label="codElemento"
          emit-value
        />
        <q-input class="col-xs-6 col-sm-2" outlined label="Nro Factura" stack-label v-model="recordToSubmit.nroFactura" />
        <q-select
          class="col-xs-7 col-sm-5"
          outlined
          label="Cliente/Prov."
          stack-label
          v-model="recordToSubmit.idCliente"
          :options="listaEntidadesFilter"
          option-value="id"
          option-label="nombre"
          emit-value
          map-options
          @filter="filterEntidades"
          use-input
          hide-selected
          fill-input
          input-debounce="0"
        />
        <q-input class="col-xs-5 col-sm-2" outlined label="Fecha" stack-label :value="formatDate(recordToSubmit.fecha)" @input="val => recordToSubmit.fecha=val" >
          <template v-slot:append>
              <q-icon name="event" class="cursos-pointer">
                <q-popup-proxy>
                  <wgDate v-model="recordToSubmit.fecha" />
                </q-popup-proxy>
              </q-icon>
          </template>
        </q-input>
      </div>
      <div class="row">
        <q-input class="col-xs-10 col-sm-5" outlined stack-label v-model="recordToSubmit.archivoDrive" label="Archivo Drive"/>
        <q-btn @click="abrirURL" class="col-xs-2 col-sm-1 bg-primary text-white" dense icon="open_in_browser"/>
        <q-select
          class="col-xs-5 col-sm-2"
          label="Estado Factura"
          stack-label
          outlined
          v-model="recordToSubmit.estadoFactura"
          :options="listaEstadosFactura"
          option-value="codElemento"
          option-label="codElemento"
          emit-value
        />
        <q-input class="col-xs-7 col-sm-4" outlined stack-label v-model="recordToSubmit.urlinfo" label="Carpeta Drive"/>
      </div>
      <div class="row">
        <q-input class="col-xs-6 col-sm-3" outlined stack-label v-model="recordToSubmit.base" label="Base"/>
        <q-input class="col-xs-2 col-sm-2" outlined stack-label v-model="recordToSubmit.por_retencion" label="%Retención"/>
        <q-input class="col-xs-4 col-sm-2" outlined stack-label v-model="recordToSubmit.retencion" label="Retención"/>
        <q-input class="col-xs-6 col-sm-2" outlined stack-label v-model="recordToSubmit.totalIva" label="Total Iva"/>
        <q-input class="col-xs-6 col-sm-3" outlined stack-label v-model="recordToSubmit.totalFactura" label="Total Factura"/>
      </div>
  </q-card>
</template>

<script>
import { mapState } from 'vuex'
import { date, openURL } from 'quasar'
import wgDate from 'components/General/wgDate.vue'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      recordToSubmit: {},
      listaEntidadesFilter: this.listaEntidades,
      listaActivosFilter: []
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaMeses', 'listaEstadosFactura', 'listaTiposFactura']),
    ...mapState('entidades', ['listaEntidades']),
    ...mapState('activos', ['listaActivos']),
    ...mapState('login', ['user'])
  },
  methods: {
    filterEntidades (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaEntidadesFilter = this.listaEntidades.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterActivos (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaActivosFilter = this.listaActivos.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    getRecords () {
      this.$emit('getRecords', this.recordToSubmit) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    },
    abrirURL () {
      if (this.recordToSubmit.archivoDrive !== '') {
        var str1 = ''
        if (this.recordToSubmit.estadoFactura === 'PENDIENTE') str1 = ''
        else str1 = this.recordToSubmit.carpeta + '/'
        var strUrl = 'https://vidawealth-my.sharepoint.com/personal/jvilata_vidawm_com/Documents/' +
          this.user.nomEmpresa + '/FACTURAS/' + str1 +
          this.recordToSubmit.archivoDrive
        if (window.cordova === undefined) { // desktop
          openURL(strUrl)
        } else { // estamos en un disp movil
          window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
        }
      }
    }
  },
  components: {
    wgDate: wgDate
  },
  mounted () {
    this.listaEntidadesFilter = this.listaEntidades
    this.recordToSubmit = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
  },
  destroyed () {
    // guardamos valor en tabs por si despus queremos recuperarlo
    this.$emit('input', this.recordToSubmit)
  }
}
</script>
