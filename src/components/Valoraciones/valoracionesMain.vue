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
        :filter="filterRecord"
        @exportarExcel = "exportarExcel"
        @getRecords = "filter => getRecords(filter)"
        />
    </div>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
import { openBlobFile } from 'components/General/cordova.js'
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
            facturado2Y: 0,
            impcobropago2Y: 0,
            cval2Y_importe: 0,
            impcompvent2Y: 0,
            comprometido: 0,
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
        obj.facturado2Y += parseFloat(row.facturado2Y)
        obj.impcobropago2Y += parseFloat(row.impcobropago2Y)
        obj.cval2Y_importe += (row.cval2Y_importe === null ? 0 : parseFloat(row.cval2Y_importe))
        obj.impcompvent2Y += parseFloat(row.impcompvent2Y)
        obj.comprometido += parseFloat(row.comprometido)
        obj.nombre = (obj.children.length + 1) + ' activos'
        obj.children.push(row)
      })
      this.registrosSeleccionados = arr
    },
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      Object.assign(this.filterRecord, filter) // no haría falta pero así obliga a refrescar el componente para que visulice el filtro
      var objFilter = Object.assign({}, filter)
      objFilter.estadoActivo = (objFilter.estadoActivo && objFilter.estadoActivo !== null ? objFilter.estadoActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.tipoProducto = (objFilter.tipoProducto && objFilter.tipoProducto !== null ? objFilter.tipoProducto.join() : null) // paso de array a concatenacion de strings (join)
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
      this.filterRecord = { codEmpresa: '01', mes: date.formatDate(new Date(), 'MM/YYYY'), tipoOperacion: 'VALORACION', estadoActivo: ['1', '4'], computa: '1' }
      // this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { idTab: this.value.idTab, filterRecord: this.filterRecord }) // { id: this.value.id, filterRecord: Object.assign({}, this.filterRecord) })
  },
  components: {
    valoracionesFilter: valoracionesFilter,
    valoracionesGrid: valoracionesGrid
  }
}
</script>
