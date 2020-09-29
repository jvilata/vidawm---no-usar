  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div style="height: calc(100vh - 105px)">
      <q-item clickable v-ripple @click="expanded = !expanded" class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
        <q-item-section avatar>
          <q-icon name="fas fa-filter" />
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-h6">
            {{ nomFormulario }}
          </q-item-label>
          <q-item-label>
            <!-- poner un campo de fiterRecord que exista en este filtro -->
            <small>{{ Object.keys(filterRecord).length > 1 ? filterRecord : 'Pulse para definir filtro' }}</small>
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

      <q-dialog v-model="expanded"  >
        <!-- formulario con campos de filtro -->
        <facturasFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>

      <!-- formulario tabla de resultados de busqueda -->
      <facturasGrid
        :value="filterRecord"
        fromFacturasMain=true
        :key="refreshKey"
        />

      <div class="absolute-bottom bg-white q-gutter-xs" align="center">
        <q-btn dense label="Acciones OneDrive" color="primary" icon="cloud">
          <q-menu>
            <q-list dense style="min-width: 100px">
              <q-item clickable @click="cargarFacturas()">
                <q-item-section avatar>
                  <q-icon name="backup" />
                </q-item-section>
                <q-item-section>Cargar Facturas Pendientes</q-item-section>
              </q-item>
              <q-item clickable @click="enviarFacturas()">
                <q-item-section avatar>
                  <q-icon name="email" />
                </q-item-section>
                <q-item-section>Enviar Facturas Pendientes</q-item-section>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>
      </div>

      <q-dialog v-model="visibleSendMail"  >
        <sendMail :value="recordSendMail" @close="visibleSendMail=false"/>
      </q-dialog>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import facturasFilter from 'components/Facturas/facturasFilter.vue'
import facturasGrid from 'components/Facturas/facturasGrid.vue'
import sendMail from 'components/SendMail/sendMail.vue'
import { openURL } from 'quasar'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      refreshKey: 0,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Facturas',
      registrosSeleccionados: [],
      recordSendMail: {},
      visibleSendMail: false
    }
  },
  computed: {
    ...mapState('login', ['user']), // importo state.user desde store-login
    ...mapState('entidades', ['listaEntidades']),
    ...mapState('activos', ['listaActivos'])
  },
  methods: {
    ...mapActions('entidades', ['loadEntidades']),
    ...mapActions('activos', ['loadActivos']),
    getRecords (filter) {
      Object.assign(this.filterRecord, filter)
      this.refreshKey++
      this.expanded = false
    },
    cargarFacturas () {
      console.log(this.$axios.defaults)
      var host = this.$axios.defaults.baseURL // 'https://vidawm.com/privado/php/'
      var strUrl = host + 'onedrive/recorrerCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' +
          this.user.nomEmpresa + '&tipo=FACTURAS&carpeta=FACTURAS&estado='
      if (window.cordova === undefined) { // desktop
        /* const link = document.createElement('a')
        link.href = host + 'onedrive/recorrerCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' +
          this.user.nomEmpresa + '&tipo=FACTURAS&carpeta=FACTURAS&estado='
        link.target = '_blank'
        document.body.appendChild(link)
        link.click() */
        openURL(strUrl)
      } else { // dispositivo movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
    },
    enviarFacturas () {
      this.recordSendMail = {
        destino: 'rus@prifiscal.es',
        destinoCopia: 'jvilata@edicom.es',
        asunto: 'Te adjunto facturas de ' + this.user.nomEmpresa,
        texto: 'Hola,<br>Le adjuntamos facturas de la empresa:' + this.user.nomEmpresa + ' en este enlace de OnDrive:%enlace%' +
          '<br>Atentamente,<br>VILATA DARDER HOLDING SL<br>' +
          '<img src="http://vidawm.com/img/VIDA_color.jpg"  width="100">',
        url: 'onedrive/moverElementosCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' + this.user.nomEmpresa +
          '&tipo=FACTURAS&carpeta=FACTURAS&estado='
      }
      this.visibleSendMail = true
    }
  },
  mounted () {
    if (this.listaEntidades.length <= 0) this.loadEntidades() // carga store listaEntidades
    if (this.listaActivos.length <= 0) this.loadActivos(this.user.codEmpresa) // carga store listaActivos
    if (this.value.filterRecord) { // si ya hemos cargado previamente los recargo al volver a este tab
      // Object.assign(this.filterRecord, this.value.filterRecord)
      this.getRecords(this.value.filterRecord) // refresco la lista por si se han hecho cambios
    } else { // es la primera vez que entro, cargo valores po defecto
      // Object.assign(this.filterRecord, { codEmpresa: this.user.codEmpresa, estadoFactura: 'PENDIENTE' })
      this.getRecords({ codEmpresa: this.user.codEmpresa, estadoFactura: 'PENDIENTE' })
    }
  },
  destroyed () {
    this.$emit('changeTab', { idTab: this.value.idTab, filterRecord: Object.assign({}, this.filterRecord), registrosSeleccionados: Object.assign({}, this.registrosSeleccionados) })
  },
  components: {
    facturasFilter: facturasFilter,
    facturasGrid: facturasGrid,
    sendMail: sendMail
  }
}
</script>
