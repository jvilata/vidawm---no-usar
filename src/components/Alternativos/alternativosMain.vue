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
        v-model="registrosSeleccionados"
        :columnas="columnas"
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
import alternativosFilter from 'components/Alternativos/alternativosFilter.vue'
import alternativosGrid from 'components/Alternativos/alternativosGrid.vue'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      expanded: false,
      visible: '',
      filterRecord: {},
      nomFormulario: 'Alternativos',
      registrosSeleccionados: [],
      columnas: []
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
    /*
    * Calcula o Valor Presente Líquido para
    * um período constante sem inversão de sinal
    *
    * @taxa => taxa de desconto
    * @montantes => vetor com os valores com os recebimentos ou pagamentos
    * -100,30,30,10,10,10,10
    */
    vpl (taxa, montantes) {
      var ret = montantes[0]

      for (var i = 1; i < montantes.length; i++) {
        ret += montantes[i] / Math.pow((1.0 + taxa), i)
      }
      return ret
    },

    tir (montantes) {
      // var ret = -1000000000.0
      var jurosInicial = -1
      var jurosMedio = 0.0
      var jurosFinal = 1.0
      var vplInicial = 0.0
      var vplFinal = 0.0
      var vplMedio = 0.0
      var erro = 1e-5

      for (var i = 0; i < 100; i++) {
        vplInicial = this.vpl(jurosInicial, montantes)
        vplFinal = this.vpl(jurosFinal, montantes)
        if (this.sinal(vplInicial) !== this.sinal(vplFinal)) break
        jurosInicial -= 1.0
        jurosFinal += 1.0
      }
      var count = 0
      while (count < 100) {
        // Busca por Bisseção
        jurosMedio = (jurosInicial + jurosFinal) / 2.0
        vplMedio = this.vpl(jurosMedio, montantes)
        if (Math.abs(vplMedio) <= erro) {
          // Resultado foi encontrado
          break // return jurosMedio * 100.0
        }
        if (this.sinal(vplInicial) === this.sinal(vplMedio)) {
          jurosInicial = jurosMedio
          vplInicial = this.vpl(jurosMedio, montantes)
        } else {
          jurosFinal = jurosMedio
          vplFinal = this.vpl(jurosMedio, montantes)
        }
        count++
      }
      return jurosMedio * 100.0 // ret
    },

    sinal (x) {
      return x < 0.0 ? -1 : 1
    },

    generarArbol () {
      var arr = []
      var obj = {}
      this.columnas = []
      var acumDistrib = 0
      var acumComprom = 0
      var numEjer = 0
      var valAntFinAnyo = 0
      var distribFuturas = 0
      var flujos = [0.0]
      var strEjer = ''

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Imp.Comp.Enero'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Comprometido'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Comp.Acum.'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Distribuido'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Distrib.Acum.'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Importe 31/12'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Múltiplo 31/12'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'TIR 31/12'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Saldo Vivo'
      }
      arr.push(obj)

      obj = {
        id: Math.floor((Math.random() * 999999) + 999999),
        tipoRegistro: 1, // 1: fila detalle, 2: fila cabecera
        descripcion: 'Capital at Risk'
      }
      arr.push(obj)

      this.registrosSeleccionados.forEach(row => {
        // para cada registro del año , insertamos registros con columnas por años: valoracion enero, comprometidos, distribuciones,valoracion31_12, multiplo,TIR,
        // acumulamos: un registro suma de todo lo anterior
        numEjer++
        strEjer = row.ejercicio
        this.columnas.push({ name: 'ejer' + row.ejercicio, align: 'left', label: row.ejercicio, field: 'ejer' + row.ejercicio, format: val => this.$numeral(val).format('0,0.00') })
        row.valoracion = parseFloat(row.valoracion)
        row.compra = parseFloat(row.compra)
        row.comprometido = parseFloat(row.comprometido) + row.compra
        row.venta = parseFloat(row.venta)
        row.cobro = parseFloat(row.cobro)
        row.distribucion = parseFloat(row.distribucion) + row.venta + row.cobro
        flujos.push(row.distribucion - row.comprometido)

        if (numEjer > 1 && row.valoracion === 0) {
          row.valoracion = valAntFinAnyo // si no tenemos la valoracion real tomamos la del año anterior y marcamos como estimada
          arr[5]['estimado' + row.ejercicio] = true // valor estimada
        } else {
          arr[5]['estimado' + row.ejercicio] = false
        }

        if (numEjer > 1) { // a partir del 2o ejercicio
          if (row.valoracion !== 0) arr[5]['ejer' + (parseInt(row.ejercicio) - 1)] = row.valoracion // valoracion 31/12 anyo anterior es la de enero
          if (acumComprom === 0) arr[6]['ejer' + row.ejercicio] = 0 // multiplo a 31/12
          else arr[6]['ejer' + (parseInt(row.ejercicio) - 1)] = (arr[5]['ejer' + (parseInt(row.ejercicio) - 1)] + acumDistrib) / acumComprom // (row.valoracion + row.comprometido + acumDistrib) / acumComprom
          // arr[7]['ejer' + (parseInt(row.ejercicio) - 1)] = ((arr[5]['ejer' + (parseInt(row.ejercicio) - 1)] + acumDistrib - acumComprom) / acumComprom) * 100 / (numEjer - 1) // TIR
        }
        arr[0]['ejer' + row.ejercicio] = acumComprom // comprometidos hasta la fecha
        arr[1]['ejer' + row.ejercicio] = row.comprometido // comprometidos del ejercicio
        arr[3]['ejer' + row.ejercicio] = row.distribucion // distribuciones del ejercicio
        arr[5]['ejer' + row.ejercicio] = row.valoracion + row.comprometido - row.distribucion // valoracion a 31/12, para el 1o ejercicio y estimadas
        if (arr[5]['ejer' + row.ejercicio] < 0) arr[5]['ejer' + row.ejercicio] = 0
        valAntFinAnyo = arr[5]['ejer' + row.ejercicio]
        acumDistrib += row.distribucion
        acumComprom += row.comprometido
        arr[2]['ejer' + row.ejercicio] = acumComprom // acum comprometido incluido ejercicio actual
        arr[4]['ejer' + row.ejercicio] = acumDistrib // acum distribuido incluido ejercicio actual
        arr[7]['ejer' + row.ejercicio] = this.tir(flujos) // TIR
        arr[8]['ejer' + row.ejercicio] = acumComprom - acumDistrib // saldo vivo
        distribFuturas = this.registrosSeleccionados.reduce((total, row1) => { return total + (row1.ejercicio > row.ejercicio ? (parseFloat(row1.comprometido) > 0 ? parseFloat(row1.comprometido) : parseFloat(row1.compra)) : 0) }, 0)
        arr[9]['ejer' + row.ejercicio] = arr[8]['ejer' + row.ejercicio] + distribFuturas // capital at risk
      })
      // ultima columna
      arr[5]['ejer' + strEjer] = 0 // valoracion 31/12 ultimo año debe ser 0 porque hemos liquidado el fondo
      if (acumComprom === 0) arr[6]['ejer' + strEjer] = 0 // multiplo 31/12
      else arr[6]['ejer' + strEjer] = (arr[5]['ejer' + strEjer] + acumDistrib) / acumComprom // (row.valoracion + row.comprometido + acumDistrib) / acumComprom
      // arr[7]['ejer' + strEjer] = ((arr[5]['ejer' + strEjer] + acumDistrib - acumComprom) / acumComprom) * 100 / (numEjer - 1 - numEjer / 4)

      this.registrosSeleccionados = arr
    },
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      Object.assign(this.filterRecord, filter) // no haría falta pero así obliga a refrescar el componente para que visulice el filtro
      var objFilter = Object.assign({}, filter)
      objFilter.idActivo = (objFilter.idActivo && objFilter.idActivo !== null ? objFilter.idActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.estadoActivo = (objFilter.estadoActivo && objFilter.estadoActivo !== null ? objFilter.estadoActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.tipoProducto = (objFilter.tipoProducto && objFilter.tipoProducto !== null ? objFilter.tipoProducto.join() : null) // paso de array a concatenacion de strings (join)
      return this.$axios.get('movimientos/bd_alternativos.php/findcProyeccionAlternativos', { params: objFilter })
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
      this.filterRecord = { codEmpresa: '01', anyoDesde: (new Date()).getFullYear() - 5, estadoActivo: ['1', '4'], computa: '1' }
      // this.getRecords(this.filterRecord)
    }
  },
  destroyed () {
    this.$emit('changeTab', { idTab: this.value.idTab, filterRecord: this.filterRecord }) // { id: this.value.id, filterRecord: Object.assign({}, this.filterRecord) })
  },
  components: {
    alternativosFilter: alternativosFilter,
    alternativosGrid: alternativosGrid
  }
}
</script>
