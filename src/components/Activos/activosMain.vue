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
        <activosFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>
      <!-- q-btn class="col-1" label="cargar" color="primary" @click="cargarRecords"/ -->
      <!-- formulario tabla de resultados de busqueda -->
      <activosGrid
        v-model="registrosSeleccionados"
        />
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import activosFilter from 'components/Activos/activosFilter.vue'
import activosGrid from 'components/Activos/activosGrid.vue'
import { date } from 'quasar'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Activos',
      registrosSeleccionados: []
    }
  },
  computed: {
    ...mapState('login', ['user']), // importo state.user desde store-login
    ...mapState('entidades', ['listaEntidades'])
  },
  methods: {
    ...mapActions('entidades', ['loadEntidades']),
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      Object.assign(this.filterRecord, filter) // no haría falta pero así obliga a refrescar el componente para que visulice el filtro
      var objFilter = Object.assign({}, filter)
      // objFilter.estadoActivo = (objFilter.estadoActivo !== null ? objFilter.estadoActivo.join() : null) // paso de array a concatenacion de strings (join)
      return this.$axios.get('activos/bd_activos.php/findActivosFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
          this.expanded = false
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    limpiarComillas (s) {
      var res = ''
      for (var i = 0; i < s.length; i++) {
        if (s.charAt(i) !== '"') res += s.charAt(i)
      }
      return res
    },
    llamarFun (record) {
      this.$axios.post('activos/bd_act_clasificacion.php/act_clasificacion', JSON.stringify(record))
        .then(response => {
          console.log(response.data.id)
        })
    },
    cargarRecords () {
      var objFilter = {}
      var t = 0
      return this.$axios.get('activos/bd_activos.php/findActivosFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
          this.registrosSeleccionados.forEach(row => {
            var tipoProd = row.tipoProducto.split(',')
            if (tipoProd.length > 0) {
              tipoProd.forEach(tprod => {
                if (tprod.length > 0) {
                  var record = {
                    idActivo: row.id,
                    clasificacion: this.limpiarComillas(tprod),
                    porcentaje: 100,
                    user: this.user.user.email,
                    ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
                  }
                  setTimeout(this.llamarFun, t, record)
                  t = t + 300
                }
              })
            }
          })
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error.message })
        })
    }
  },
  mounted () {
    if (this.listaEntidades.length <= 0) this.loadEntidades() // carga store listaEntidades
    if (this.value.registrosSeleccionados && Object.keys(this.value.registrosSeleccionados).length > 0) { // si ya hemos cargado previamente los recargo al volver a este tab
      this.expanded = false
      Object.assign(this.filterRecord, this.value.filterRecord)
      // this.registrosSeleccionados = Object.values(this.value.registrosSeleccionados) // v-model: en 'value' podemos leer el valor del v-model
      this.getRecords(this.filterRecord) // refresco la lista por si se han hecho cambios
    } else { // es la primera vez que entro, cargo valores po defecto
      this.filterRecord = { codEmpresa: this.user.codEmpresa, computa: '1' }
      this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { idTab: this.value.idTab, filterRecord: Object.assign({}, this.filterRecord), registrosSeleccionados: Object.assign({}, this.registrosSeleccionados) })
  },
  components: {
    activosFilter: activosFilter,
    activosGrid: activosGrid
  }
}
</script>
