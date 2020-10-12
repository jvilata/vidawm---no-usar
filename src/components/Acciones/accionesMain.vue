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
            <small>{{ Object.keys(filterRecord).length > 0 ? filterRecord : 'Pulse para definir filtro' }}</small>
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
        <accionesFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>

      <!-- formulario tabla de resultados de busqueda -->
      <accionesGrid
        v-model="registrosSeleccionados"
        />
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
import accionesFilter from 'components/Acciones/accionesFilter.vue'
import accionesGrid from 'components/Acciones/accionesGrid.vue'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Acciones',
      registrosSeleccionados: []
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      var objFilter = Object.assign({}, filter)
      objFilter.idUserQuienProx = (objFilter.idUserQuienProx !== null ? objFilter.idUserQuienProx.join() : null) // paso de array a concatenacion de strings (join)
      return this.$axios.get('acciones/bd_acciones.php/findAccionesFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
          this.expanded = false
          this.$emit('contadorAcciones', response.data.length) // lo captura MainTabs y se lo pasa a MainLayout
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    if (this.value.registrosSeleccionados && Object.keys(this.value.registrosSeleccionados).length > 0) { // si ya hemos cargado previamente los recargo al volver a este tab
      this.expanded = false
      this.registrosSeleccionados = Object.values(this.value.registrosSeleccionados) // v-model: en 'value' podemos leer el valor del v-model
      Object.assign(this.filterRecord, this.value.filterRecord)
    } else { // es la primera vez que entro, cargo valores po defecto
      var muser = []
      muser.push(this.user.user.email)
      this.filterRecord = { idUserQuienProx: muser, fechaProxHasta: date.formatDate(date.addToDate(new Date(), { days: 5 }), 'YYYY-MM-DD 00:00:00'), realizada: '0' }
      this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { id: this.value.id, filterRecord: Object.assign({}, this.filterRecord), registrosSeleccionados: Object.assign({}, this.registrosSeleccionados) })
  },
  components: {
    accionesFilter: accionesFilter,
    accionesGrid: accionesGrid
  }
}
</script>
