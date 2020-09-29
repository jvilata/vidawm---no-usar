  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div style="height: 100vh">
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
        <valoracionesFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>

      <!-- formulario tabla de resultados de busqueda -->
      <valoracionesGrid
        v-model="registrosSeleccionados"
        />
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
import valoracionesFilter from 'components/Valoraciones/valoracionesFilter.vue'
import valoracionesGrid from 'components/Valoraciones/valoracionesGrid.vue'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Valoraciones',
      registrosSeleccionados: []
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
    generarArbol () {
      // ordenar el array por tipoActivo
      this.registrosSeleccionados.sort((a, b) => a.tipoActivo.localeCompare(b.tipoActivo))
      var arr = []
      var antTipoActivo = ''
      var obj = {}
      this.registrosSeleccionados.forEach(row => {
        // si tipoActivo != antTipoActivo  Insertamos 'obj' en arr como cabecera tipoActivo
        if (row.tipoActivo.localeCompare(antTipoActivo) !== 0) {
          obj = {
            id: Math.floor((Math.random() * 999999) + 999999),
            tipoActivo: row.tipoActivo,
            // nombre: '',
            importe: 0,
            valant_importe: 0,
            minval_importe: 0,
            impcompvent: 0,
            facturado: 0,
            impcobropago: 0,
            impcompras: 0,
            children: []
          }
          arr.push(obj)
          antTipoActivo = row.tipoActivo
        }
        row.children = []
        obj.importe += parseFloat(row.importe)
        obj.valant_importe += (row.valant_importe === null ? 0 : parseFloat(row.valant_importe))
        obj.minval_importe += (row.minval_importe === null ? 0 : parseFloat(row.minval_importe))
        obj.impcompvent += (row.impcompvent === null ? 0 : parseFloat(row.impcompvent))
        obj.facturado += (row.facturado === null ? 0 : parseFloat(row.facturado))
        obj.impcobropago += (row.impcobropago === null ? 0 : parseFloat(row.impcobropago))
        obj.impcompras += (row.impcompras === null ? 0 : parseFloat(row.impcompras))
        obj.nombre = (obj.children.length + 1) + ' activos'
        obj.children.push(row)
      })
      this.registrosSeleccionados = arr
    },
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      Object.assign(this.filterRecord, filter) // no haría falta pero así obliga a refrescar el componente para que visulice el filtro
      var objFilter = Object.assign({}, filter)
      objFilter.estadoActivo = (objFilter.estadoActivo !== null ? objFilter.estadoActivo.join() : null) // paso de array a concatenacion de strings (join)
      return this.$axios.get('movimientos/bd_movimientos.php/findcMovimientosComparado', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
          // this.registrosSeleccionados.splice(0, 0, { id: -1, tipoActivo: 'CAP.RIESGO', nombre: '' })
          this.generarArbol(this.registrosSeleccionados)
          this.expanded = false
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
      this.filterRecord = { codEmpresa: '01', mes: date.formatDate(new Date(), 'MM/YYYY'), tipoOperacion: 'VALORACION', estadoActivo: ['1', '4'], computa: '1' }
      this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { id: this.value.id, filterRecord: Object.assign({}, this.filterRecord), registrosSeleccionados: Object.assign({}, this.registrosSeleccionados) })
  },
  components: {
    valoracionesFilter: valoracionesFilter,
    valoracionesGrid: valoracionesGrid
  }
}
</script>
