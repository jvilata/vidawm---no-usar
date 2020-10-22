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
        <notasFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>

      <!-- formulario tabla de resultados de busqueda -->
      <notasGrid
        v-model="registrosSeleccionados"
        @generarArbol = "getRecords(filterRecord)"
        />
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import notasFilter from 'components/Notas/notasFilter.vue'
import notasGrid from 'components/Notas/notasGrid.vue'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Notas de Gasto',
      registrosSeleccionados: []
    }
  },
  computed: {
    ...mapState('login', ['user']), // importo state.user desde store-login
    ...mapState('personal', ['listaPersonal'])
  },
  methods: {
    ...mapActions('personal', ['loadPersonal']),
    generarArbol () {
      // ordenar el array por tipoActivo
      this.registrosSeleccionados.sort((a, b) => (a.generar <= b.generar ? -1 : 1))
      var arr = []
      var antGenerar = 'x'
      var obj = {}
      this.registrosSeleccionados.forEach(row => {
        // si tipoActivo != antTipoActivo  Insertamos 'obj' en arr como cabecera tipoActivo
        row.generar = row.nroNota + ' - ' + row.nomPersona.substring(0, 9)
        if (row.generar !== antGenerar) {
          obj = {
            id: -Math.floor((Math.random() * 999999) + 999999),
            generar: row.generar,
            // nombre: '',
            expend: true,
            totalNota: 0,
            children: []
          }
          arr.push(obj)
          antGenerar = row.generar
        }
        row.children = []
        obj.totalNota += parseFloat(row.totalNota)
        obj.nroNota = row.nroNota
        obj.children.push(row)
      })
      this.registrosSeleccionados = arr
    },
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      Object.assign(this.filterRecord, filter) // no haría falta pero así obliga a refrescar el componente para que visulice el filtro
      var objFilter = Object.assign({}, filter)
      return this.$axios.get('notas/bd_notas.php/findNotasFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
          // this.registrosSeleccionados.splice(0, 0, { id: -1, tipoActivo: 'CAP.RIESGO', nombre: '' })
          this.generarArbol()
          this.expanded = false
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    if (this.listaPersonal.length === 0) this.loadPersonal(this.user.codEmpresa)
    if (this.value.registrosSeleccionados && Object.keys(this.value.registrosSeleccionados).length > 0) { // si ya hemos cargado previamente los recargo al volver a este tab
      this.expanded = false
      this.registrosSeleccionados = Object.values(this.value.registrosSeleccionados) // v-model: en 'value' podemos leer el valor del v-model
      Object.assign(this.filterRecord, this.value.filterRecord)
    } else { // es la primera vez que entro, cargo valores po defecto
      this.filterRecord = { codEmpresa: this.user.codEmpresa, idPersonal: this.user.pers.id, estadoNota: 'PENDIENTE' }
      this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { id: this.value.id, filterRecord: Object.assign({}, this.filterRecord), registrosSeleccionados: Object.assign({}, this.registrosSeleccionados) })
  },
  components: {
    notasFilter: notasFilter,
    notasGrid: notasGrid
  }
}
</script>
