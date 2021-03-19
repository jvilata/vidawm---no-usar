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
        <alternativosFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>

      <!-- formulario tabla de resultados de busqueda -->
      <alternativosGrid
        :filter="filterRecord"
        @exportarExcel = "exportarExcel"
        :key="refresh"
        />
      <alternativosGraficoMov
        :filter="filterRecord"
        :key="refresh"
      />
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
import { openBlobFile } from 'components/General/cordova.js'
import alternativosFilter from 'components/Alternativos/alternativosFilter.vue'
import alternativosGrid from 'components/Alternativos/alternativosGrid.vue'
import alternativosGraficoMov from '../Alternativos/alternativosGraficoMov.vue'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Alternativos',
      registrosSeleccionados: [],
      columnas: [],
      refresh: 0
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
    getRecords (filter) {
      this.expanded = false
      Object.assign(this.filterRecord, filter)
      this.refresh++
    },
    exportarExcel () {
      var str = ' id is not null and codEmpresa=\'' + this.user.codEmpresa + '\''
      if (this.filterRecord.tipoActivo && this.filterRecord.tipoActivo.length > 0) str += ' and tipoActivo=\'' + this.filterRecord.tipoActivo + '\''
      if (this.filterRecord.tipoProducto && this.filterRecord.tipoProducto.length > 0) {
        for (var x = 0; x < this.filterRecord.tipoProducto.length; x++) {
          str += ' and tipoProducto like \'%' + this.filterRecord.tipoProducto[x] + '%\''
        }
      }
      if (this.filterRecord.idEntidad && this.filterRecord.idEntidad.length > 0) str += ' and idEntidad=' + this.filterRecord.idEntidad
      if (this.filterRecord.nombre && this.filterRecord.nombre.length > 0) str += ' and nombre like \'%' + this.filterRecord.nombre + '%\''
      if (this.filterRecord.fechaDesde && this.filterRecord.fechaDesde.length > 0) str += ' and (fecha  >= \'' + this.filterRecord.fechaDesde + '\')'
      if (this.filterRecord.fechaHasta && this.filterRecord.fechaHasta.length > 0) str += ' and (fecha  <= \'' + this.filterRecord.fechaHasta + '\')'
      if (this.filterRecord.mes && this.filterRecord.mes.length > 0) str += ' and (date_format(fecha,\'%m/%Y\')  = \'' + this.filterRecord.mes + '\')'
      if (this.filterRecord.tipoOperacion && this.filterRecord.tipoOperacion.length > 0) str += ' and tipoOperacion=\'' + this.filterRecord.tipoOperacion + '\''
      if (this.filterRecord.estadoActivo && this.filterRecord.estadoActivo.length > 0) {
        var str1 = ''
        for (x = 0; x < this.filterRecord.estadoActivo.length; x++) {
          if (str1.length > 0) str1 += ' or '
          str1 += ' estadoActivo = \'' + this.filterRecord.estadoActivo[x] + '\''
        }
      }
      str += ' and (' + str1 + ')'
      if (this.filterRecord.computa && this.filterRecord.computa.length > 0) str += ' and computa=' + this.filterRecord.computa

      var sql = 'select * from cvaloraciones where ' + str + ' order by fecha,tipoActivo,nombre'
      console.log(sql)
      var paramRecord = {
        SQL: sql,
        string_con: '',
        nompdf: 'movimientosObjeto.csv'
      }
      var formData = new FormData()
      for (var key in paramRecord) {
        formData.append(key, paramRecord[key])
      }
      this.$axios.post('lib/exportExcel.php', formData, { responseType: 'blob' })
        .then(function (response) {
          var nomFile = 'movimientosObjeto_' + date.formatDate(new Date(), 'YYYYMMDDHHmmss') + '.csv'
          if (window.cordova === undefined) { // desktop
            const url = window.URL.createObjectURL(new Blob([response.data], { type: response.data.type }))
            const link = document.createElement('a')
            link.href = url
            link.download = nomFile
            // link.target = '_blank'
            document.body.appendChild(link)
            // window.open('', 'view') // abre nueva ventana para que no sustituya a la actual
            link.click()
            document.body.removeChild(link)
          } else { // estamos en un disp movil            console.log('hola3')
            const blobPdf = response.data // new Blob([response.data], { type: response.data.type })
            openBlobFile(nomFile, blobPdf, response.data.type)
          }
        }).catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    if (this.value.filterRecord) { // si ya hemos cargado previamente los recargo al volver a este tab
      this.expanded = false
      Object.assign(this.filterRecord, this.value.filterRecord)
      this.getRecords(this.filterRecord)
    } else { // es la primera vez que entro, cargo valores po defecto
      this.filterRecord = { codEmpresa: this.user.codEmpresa, anyoDesde: (new Date()).getFullYear() - 5, estadoActivo: ['1', '4'], computa: '1' }
      // this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { idTab: this.value.idTab, filterRecord: this.filterRecord }) // { id: this.value.id, filterRecord: Object.assign({}, this.filterRecord) })
  },
  components: {
    alternativosFilter: alternativosFilter,
    alternativosGrid: alternativosGrid,
    alternativosGraficoMov: alternativosGraficoMov
  }
}
</script>
