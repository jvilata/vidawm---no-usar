  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div>
      <q-card flat>
      <q-card-section   class="q-pa-xs">
            <q-item class="q-pa-xs bg-indigo-1 text-grey-8">
              <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
              <q-item-section avatar>
                <div class="row">
                  <q-btn icon="save"  class="q-ma-xs" :color="colorBotonSave" dense @click="valueTotales.base=value.base; refresh++"/>
                  <q-btn icon="more_vert"  class="q-ma-xs" color="primary" dense>
                    <q-menu>
                      <q-list dense>
                        <q-item
                          v-for="(opcion, index) in listaOpciones"
                          :key="index"
                          clickable
                          v-close-popup
                          @click.native="ejecutarOpcion(opcion.function)"
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
                  {{ title }} {{ value.nroFactura }}
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
        <facturasFormCabecera :value="value" :key="refresh" :hasChanges="hasChanges" :colorBotonSave="colorBotonSave" @hasChanges="value=>cambiaDatos(value)" @input="saveChanges" @calculartotalesfac="calculaTotalesFac"/>
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
          <facturasFormLineas :value="value" @calculaTotalesFac="calculaTotalesFac"/>
        </q-expansion-item>
      </q-list>

    </q-card>
    </q-scroll-area>
    </div>
</template>

<script>
import { mapState } from 'vuex'
import facturasFormCabecera from 'components/Facturas/facturasFormCabecera.vue'
import facturasFormLineas from 'components/Facturas/facturasFormLineas.vue'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      title: 'Factura',
      hasChanges: false,
      colorBotonSave: 'primary',
      refresh: 0,
      valueTotales: {},
      listaOpciones: [
        { name: 'Preview', title: 'Marcar todos', icon: 'add', function: 'marcarTodos' },
        { name: 'borrarMarcas', title: 'Borrar marcas', icon: 'remove', function: 'borrarMarcas' },
        { name: 'generarFichero', title: 'Generar Fichero', icon: 'picture_as_pdf', function: 'generarFichero' },
        { name: 'importarNominas', title: 'Importar Nóminas', icon: 'publish', function: 'importarNominas' },
        { name: 'enviarMails', title: 'Enviar Mails', icon: 'mail_outline', function: 'enviarMails' }
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
    saveChanges (record) {
      if (this.valueTotales.base) { // si ya esta inicializado
        Object.assign(this.value, record)
        Object.assign(this.value, this.valueTotales)
        this.updateRecord()
      }
    },
    updateRecord () {
      return this.$axios.post(`facturas/bd_facturas.php/findFacturasFilter/${this.value.id}`, this.value)
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
      if (!totales.por_retencion) totales.por_retencion = this.value.por_retencion
      this.valueTotales.base = totales.base
      this.valueTotales.totalIva = totales.totalIva
      this.valueTotales.retencion = Math.round(totales.base * (totales.por_retencion / 100.0) * 100.0) / 100
      this.valueTotales.totalFactura = Math.round((totales.base + totales.totalIva - this.valueTotales.retencion) * 100.0) / 100
      this.refresh++
    }
  },
  mounted () {
    setTimeout(() => { this.colorBotonSave = 'primary'; this.hasChanges = false }, 50) // dejo pasar un poco porque en el render se modifica el registro
  },
  destroyed () {
    if (this.hasChanges) {
      this.$q.dialog({ title: 'Aviso', message: '¿ Desea guardar cambios ?', ok: true, cancel: true, persistent: true })
        .onOk(() => { this.updateRecord() })
    }
  },
  components: {
    facturasFormCabecera: facturasFormCabecera,
    facturasFormLineas: facturasFormLineas
  }
}
</script>
