  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div style="height: calc(100vh - 105px)">
    <q-card>
      <q-list bordered>
        <q-expansion-item
          class="q-pt-none q-pl-xs q-pr-xs"
          group="somegroup"
          dense
          label="Cabecera Factura"
          default-opened
          header-class="bg-orange-1 text-grey-8"
        >
        <facturasFormCabecera :value="value" :key="refresh" @input="saveChanges"/>
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
        <q-item>
          <div class="absolute-bottom bg-white q-gutter-xs" align="center">
          <q-btn dense label="Guardar" color="primary" style="width: 150px" icon="save" @click="valueTotales.base=value.base; refresh++"/>
          </div>
        </q-item>
      </q-list>

    </q-card>

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
      title: 'Facturas',
      refresh: 0,
      valueTotales: {}
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
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
          this.$q.dialog({ title: 'Aviso', message: 'Se ha actualizado registro', ok: true, persistent: true })
          this.valueTotales = {} // inicializamos estado
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    calculaTotalesFac (totales) { // cuando se guardan cambios en una linea de detalle
      this.valueTotales.base = totales.base
      this.valueTotales.totalIva = totales.totalIva
      this.valueTotales.retencion = Math.round(totales.base * (this.value.por_retencion / 100.0) * 100.0) / 100
      this.valueTotales.totalFactura = totales.base + totales.totalIva - this.valueTotales.retencion
      this.refresh++
    }
  },
  mounted () {
  },
  components: {
    facturasFormCabecera: facturasFormCabecera,
    facturasFormLineas: facturasFormLineas
  }
}
</script>
