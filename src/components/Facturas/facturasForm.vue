  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div>
      <q-card flat>
      <q-card-section   class="q-pa-xs">
            <q-item class="q-pa-xs bg-indigo-1 text-grey-8">
              <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
              <q-item-section avatar>
                <div class="row">
                  <q-btn icon="save"  class="q-ma-xs" :color="colorBotonSave" dense @click="refresh++"/>
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
                  {{ title }} {{ recordToSubmit.nroFactura }}
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
    <q-card flat>
      <q-list bordered>
        <q-expansion-item
          class="q-pt-none q-pl-xs q-pr-xs"
          group="somegroup"
          dense
          label="Cabecera Factura"
          default-opened
          header-class="bg-orange-1 text-grey-8"
        >
        <facturasFormCabecera :value="recordToSubmit" :key="refresh" @hasChanges="value=>cambiaDatos(value)" @input="saveChanges" @calculartotalesfac="calculaTotalesFac"/>
        </q-expansion-item>
        <q-separator />
        <q-expansion-item
          class="q-pt-none q-pl-xs q-pr-xs"
          group="somegroup1"
          dense
          label="Detalle"
          default-opened
          header-class="bg-orange-1 text-grey-8"
        >
          <facturasFormLineas :key="refresh" :value="recordToSubmit" @calculaTotalesFac="calculaTotalesFac"/>
        </q-expansion-item>
      </q-list>

    </q-card>
    </q-scroll-area>

    <q-dialog v-model="visibleSendMail"  >
      <sendMail :value="recordSendMail" @close="visibleSendMail=false"/>
    </q-dialog>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import facturasFormCabecera from 'components/Facturas/facturasFormCabecera.vue'
import facturasFormLineas from 'components/Facturas/facturasFormLineas.vue'
import { openBlobFile } from 'components/General/cordova.js'
import sendMail from 'components/SendMail/sendMail.vue'
import { openURL } from 'quasar'
// import { newPostWindow } from 'components/General/libGeneral.js'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      title: 'Factura',
      recordToSubmit: {
        por_retencion: '',
        base: '',
        totalIva: '',
        retencion: '',
        totalFactura: ''
      },
      hasChanges: false,
      colorBotonSave: 'primary',
      primeraVez: true,
      refresh: 0,
      visibleSendMail: false,
      recordSendMail: {},
      valueTotales: {},
      listaOpciones: [
        { name: 'imprimir', title: 'Imprimir', icon: 'print', function: 'imprimirPreview' },
        { name: 'duplicar', title: 'Duplicar', icon: 'file_copy', function: 'duplicar' },
        { name: 'enviarEmail', title: 'Enviar por email', icon: 'email', function: 'enviarEmail' },
        { name: 'generarPago', title: 'Generar Pago', icon: 'brightness_5', function: 'generarPago' },
        { name: 'imprimirOneDrive', title: 'Imprimir a OneDrive', icon: 'backup', function: 'imprimirOneDrive' }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
    cambiaDatos (record) {
      this.hasChanges = record.hasChanges
      this.colorBotonSave = record.colorBotonSave
    },
    getRecord () {
      this.$axios.get(`facturas/bd_facturas.php/findFacturasFilter/${this.value.id}`, { params: { id: this.value.id } })
        .then(response => {
          Object.assign(this.recordToSubmit, response.data[0])
          Object.assign(this.valueTotales, response.data[0])
          setTimeout(() => { this.primeraVez = false; this.colorBotonSave = 'primary'; this.hasChanges = false }, 100) // dejo pasar un poco porque en el render se modifica el registro
          this.refresh++ // refresca datos cabecera
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    saveChanges (record) {
      if (this.hasChanges) {
        Object.assign(this.recordToSubmit, record)
        Object.assign(this.recordToSubmit, this.valueTotales) // se pierden los valores de totales por lo que viene de cabecera
        this.updateRecord()
      }
    },
    updateRecord () {
      return this.$axios.post(`facturas/bd_facturas.php/findFacturasFilter/${this.recordToSubmit.id}`, this.recordToSubmit)
        .then(response => {
          this.colorBotonSave = 'primary'
          this.hasChanges = false
          this.$q.notify('Se ha actualizado registro')
          this.valueTotales = {} // inicializamos estado
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    calculaTotalesFac (totales) { // cuando se guardan cambios en una linea de detalle
      if (this.recordToSubmit.por_retencion === '' || this.recordToSubmit.por_retencion === null) this.recordToSubmit.por_retencion = '0'
      if (!totales.por_retencion) totales.por_retencion = this.recordToSubmit.por_retencion
      totales.base = Math.round(parseFloat(totales.base) * 100.0) / 100
      totales.totalIva = Math.round(parseFloat(totales.totalIva) * 100.0) / 100
      totales.retencion = Math.round(parseFloat(totales.base) * (parseFloat(totales.por_retencion) / 100.0) * 100.0) / 100
      totales.totalFactura = Math.round((parseFloat(totales.base) + parseFloat(totales.totalIva) - parseFloat(totales.retencion)) * 100.0) / 100
      Object.assign(this.valueTotales, totales) // para que no se pierdan los valores en el saveChanges
      Object.assign(this.recordToSubmit, totales)
      this.refresh++ // refresca datos cabecera
    },
    // funciones de menu de factura
    ejecutarOpcion (opcion) {
      this[opcion.function](this.value)
      this.$refs.menu1.hide()
    },
    imprimirPreview (selected) {
      this.imprimir(selected, 0)
    },
    imprimirOneDrive (selected) {
      // var strUrl = 'facturas/pdf_invoice.php/'
      // newPostWindow(strUrl, 'id', selected.id, 'aDisco', 1, '', '')
      this.imprimir(selected, 2) // da mas control que la version newPostWindow
        .then(result => {
          var strUrl = 'https://vidawm.com' + result.data.success
          if (window.cordova === undefined) { // desktop
            openURL(strUrl)
          } else { // estamos en un disp movil
            window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
          }
          this.getRecord()
        })
        .catch(error => console.log(error))
    },
    imprimir (selected, aDisco) { // aDisco: 0 -> preview; aDisco: 1 -> imprime en onedrive
      return new Promise((resolve, reject) => {
        var paramRecord = {
          id: selected.id,
          aDisco: aDisco
        }
        var formData = new FormData()
        for (var key in paramRecord) {
          formData.append(key, paramRecord[key])
        }
        this.$axios.post('facturas/pdf_invoice.php/', formData, { responseType: (aDisco === 0 ? 'blob' : '') })
          .then(function (response) {
            if (aDisco === 2) resolve(response)
            else {
              if (window.cordova === undefined) { // desktop
                const url = window.URL.createObjectURL(new Blob([response.data], { type: response.data.type }))
                const link = document.createElement('a')
                link.href = url
                link.target = '_blank'
                document.body.appendChild(link)
                // window.open('', 'view') // abre nueva ventana para que no sustituya a la actual
                link.click()
              } else { // estamos en un disp movil            console.log('hola3')
                const blobPdf = response.data // new Blob([response.data], { type: response.data.type })
                openBlobFile(selected.archivoDrive, blobPdf, response.data.type)
              }
              resolve('ok')
            }
          }).catch(error => {
            reject(error.message)
          })
      })
    },
    duplicar (selected) {
      this.$axios.get('facturas/bd_facturas.php/copiarFactura', { params: { id: selected.id } })
        .then(response => {
          this.$q.notify('Factura copiada')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    enviarEmail (selected) {
      this.recordSendMail = {
        destino: (selected.emailEntidad === '' ? 'jvilata@edicom.es' : selected.emailEntidad),
        destinoCopia: 'jvilata@edicom.es',
        asunto: 'Factura de ' + this.user.nomEmpresa + ' número: ' + selected.nroFactura,
        texto: 'Hola,<br>Le adjuntamos factura ' + selected.nroFactura + ' por los servicios prestados de la empresa:' +
          this.user.nomEmpresa + '<br>Atentamente,<br>VILATA DARDER HOLDING SL<br>' +
          '<img src="http://vidawm.com/img/VIDA_color.jpg"  width="100">',
        url: 'onedrive/downloadFactura.php?empresa=' + this.user.nomEmpresa + '&nombrePDF=' + selected.archivoDrive + '&carpeta=' + selected.carpeta
      }
      this.visibleSendMail = true
    },
    generarPago (selected) {
      this.$axios.get('facturas/bd_facturas.php/generarPagoCobroFactura', { params: { id: selected.id } })
        .then(response => {
          this.$q.dialog({ title: 'Confirmar', message: 'Se ha generado movimiento' })
          this.$emit('getCounters')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    this.getRecord()
  },
  destroyed () {
    if (!this.primeraVez && this.hasChanges) {
      this.$q.dialog({ title: 'Aviso', message: '¿ Desea guardar cambios ?', ok: true, cancel: true, persistent: true })
        .onOk(() => { this.updateRecord() })
    }
  },
  components: {
    facturasFormCabecera: facturasFormCabecera,
    facturasFormLineas: facturasFormLineas,
    sendMail: sendMail
  }
}
</script>
