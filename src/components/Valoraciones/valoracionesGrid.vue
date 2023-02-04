<template>
  <q-item class="row">
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="valoracionesGrid-header-table"
      dense
      virtual-scroll
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="arrayTreeObj"
      :columns="columns"
      :sort-method="customSort"
      table-style="max-height: 66vh; max-width: 93vw"
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
          <q-th>
            <q-btn icon="more_vert"  class="q-ma-xs" color="primary" dense>
              <q-menu ref="menu1">
                <q-list dense>
                  <q-item
                    v-for="(opcion, index) in listaOpciones"
                    :key="index"
                    clickable
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
            Tipo Activo
          </q-th>
          <q-th
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            {{ col.label }}
          </q-th>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <q-tr
          :key="props.row.id"
          :class="[(props.row.id !== selectedRowID.id) ? '':'bg-green-1']"
          @click="selectedRow(props.row)"
          @mouseover="rowId=props.row.id">
          <q-td @click="toggle(props.row)">
            <span v-bind:style="setPadding(props.row)">
              <q-icon  style="cursor: pointer;"
              :name="iconName(props.row)" color="secondary"  >
              </q-icon>
              {{props.row.tipoActivo}}
            </span>
          </q-td>
          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div :style="col.style"  @click="clickColumn(col.name, props.row)">
              {{ col.value }}
            </div>
            <q-popup-edit v-if="props.row.children.length===0 && ['importe'].includes(col.name)"
              v-model="props.row[col.name]"
              max-height="600px"
              buttons
              @save="updateRecord(props.row)">
              <!-- aqui definimos las ediciones especificas para cada columna -->
              <q-input v-model="props.row[col.name]" />
            </q-popup-edit>
          </q-td>
        </q-tr>
      </template>

      <template v-slot:bottom>
        <div>
          {{ value.length }} Filas
        </div>
      </template>

    </q-table>
    <q-dialog v-model="visibleGenerar"  >
      <valoracionesGenerar :value="recordGenerar" @close="visibleGenerar=false" @generarValoraciones="record => generarValoraciones(record)"/>
    </q-dialog>
    <q-dialog v-model="visibleBorrar"  >
      <valoracionesBorrar :value="recordGenerar" @close="visibleBorrar=false"
        @borrarValoraciones="record => efectuarBorrarValoraciones(record)"/>
    </q-dialog>
  </q-item>
</template>

<script>
import { date, exportFile, openURL } from 'quasar'
import { mapActions, mapState } from 'vuex'
import valoracionesGenerar from 'components/Valoraciones/valoracionesGenerar.vue'
import valoracionesBorrar from 'components/Valoraciones/valoracionesBorrar.vue'

export default {
  props: ['value', 'filter'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      columns: [
        // { name: 'tipoActivo', label: 'tipoActivo', align: 'left', field: 'tipoActivo', sortable: true },
        { name: 'nombre', align: 'left', label: 'Activo', field: 'nombre', sortable: true, style: 'width: 200px; whiteSpace: normal' },
        { name: 'nombreEntidad', align: 'left', label: 'Gestor/Arrend', field: 'nombreEntidad', sortable: true, style: 'width: 130px; whiteSpace: normal' },
        { name: 'tipoOperacion', align: 'left', label: 'Tipo', field: 'tipoOperacion' },
        { name: 'fecha', align: 'left', label: 'Fecha', field: 'fecha', format: val => (val !== undefined ? date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY') : '') },
        { name: 'importe', align: 'right', label: 'Importe Neto', field: 'importe', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometido', align: 'right', label: 'Compr.Rest', field: 'comprometido', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'valant_fecha', align: 'left', label: 'F.Anterior', field: 'valant_fecha', format: val => (val !== undefined ? date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY') : '') },
        { name: 'difanterior', align: 'right', label: 'Dif.Ant', field: row => parseFloat(row.importe) - parseFloat(row.valant_importe), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'minval_fecha', align: 'left', label: 'F.Inicial', field: 'minval_fecha', format: val => (val !== undefined ? date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY') : '') },
        { name: 'minval_importe', align: 'right', label: 'Imp.Inicio', field: 'minval_importe', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'impcompvent', align: 'right', label: 'Comp/Vent.Año', field: 'impcompvent', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'factuInteres', align: 'right', label: 'Factur/Inter', field: row => parseFloat(row.impcobropago) + parseFloat(row.facturado), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'revalorizacion', align: 'right', label: 'Reval.Año', field: b => parseFloat(b.importe) + parseFloat(b.facturado) + parseFloat(b.impcobropago) - (parseFloat(b.minval_importe) + parseFloat(b.impcompvent)), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'peso', align: 'right', label: '%Peso', field: 'peso', sortable: true, format: val => (val !== undefined ? this.$numeral(parseFloat(val)).format('0,0.00') : '') },
        { name: 'pesoGlobal', align: 'right', label: '%PesoGlobal', field: 'pesoGlobal', sortable: true, format: val => (val !== undefined ? this.$numeral(parseFloat(val)).format('0,0.00') : '') },
        {
          name: 'rentabAcum',
          required: true,
          label: '%Real',
          align: 'right',
          field: 'rentabAcum',
          sortable: true,
          format: val => this.$numeral(parseFloat(val)).format('0,0.00')
        },
        // { name: 'impcompras', align: 'right', label: 'Imp.Compras', field: 'impcompras', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'rentabilidadEsperada', align: 'right', label: '%Rent.Esper', field: 'rentabilidadEsperada', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'revalorizacion2Y', align: 'right', label: 'Reval.2Y', field: row => parseFloat(row.importe) + parseFloat(row.facturado2Y) + parseFloat(row.impcobropago2Y) - (parseFloat(row.cval2Y_importe === null ? 0 : row.cval2Y_importe) + parseFloat(row.impcompvent2Y)), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        {
          name: 'rentabAcum2Y',
          required: true,
          label: '%Rentab2Y',
          align: 'right',
          field: b => {
            var res = 0
            var newValue = b.importe + parseFloat(b.facturado2Y === null ? 0 : b.facturado2Y) + parseFloat(b.impcobropago2Y === null ? 0 : b.impcobropago2Y) - (parseFloat(b.cval2Y_importe === null ? 0 : b.cval2Y_importe) + parseFloat(b.impcompvent2Y === null ? 0 : b.impcompvent2Y))
            if (newValue !== 0 && (parseFloat(b.cval2Y_importe === null ? 0 : b.cval2Y_importe) + parseFloat(b.impcompvent2Y === null ? 0 : b.impcompvent2Y)) !== 0) {
              res = newValue * 100 / (parseFloat(b.cval2Y_importe === null ? 0 : b.cval2Y_importe) + parseFloat(b.impcompvent2Y === null ? 0 : b.impcompvent2Y))
            }
            return res
          },
          format: val => this.$numeral(parseFloat(val)).format('0,0.00')
        },
        { name: 'impcompventastotales', align: 'right', label: 'Comp/Vent Tot', field: 'impcompventastotales', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'revalorizacionTotal', align: 'right', label: 'Reval.Total', field: row => parseFloat(row.importe) - parseFloat(row.impcompventastotales), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'multiplo', align: 'right', label: 'Multiplo', field: 'multiplo', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'urlinfo', align: 'right', label: 'URL', field: 'urlinfo', sortable: true },
        { name: 'user', align: 'left', label: 'user', field: 'user' },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts' }
      ],
      pagination: { rowsPerPage: 0 },
      selectedRowID: {},
      isExpanded: true,
      itemId: null,
      listaOpciones: [
        { name: 'generarValoracion', title: 'Generar Valoración', icon: 'add', function: 'mostrarGenerarValoraciones' },
        { name: 'exportarExcel', title: 'Exportar Excel', icon: 'table_view', function: 'exportarExcel' },
        { name: 'borrarValoracion', title: 'Borrar Valoración', icon: 'add', function: 'mostrarBorrarValoraciones' }
      ],
      visibleGenerar: false,
      visibleBorrar: false,
      recordGenerar: { mes: '', fecha: '', idEntidad: '' }
    }
  },
  computed: {
    ...mapState('login', ['user']),
    ...mapState('tablasAux', ['listaMeses']),
    arrayTreeObj () {
      const vm = this
      var newObj = []
      if (vm.value !== undefined) vm.recursive(vm.value, newObj, 0, vm.itemId, vm.isExpanded)
      return newObj
    }
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    ...mapActions('tablasAux', ['loadListaMeses']),
    mostrarDatosPieTabla () {
      return this.value.length + ' Filas'
    },
    updateRecord (record) {
      record.user = this.user.user.email
      record.ts = date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      var updated = {
        id: record.id,
        importe: record.importe,
        user: record.user,
        ts: record.ts
      }
      return this.$axios.put(`movimientos/bd_movimientos.php/findcMovimientosComparado/${updated.id}`, JSON.stringify(updated))
        .then(response => {
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    ejecutarOpcion (opcion) {
      this[opcion.function](this.selectedRowID)
      this.$refs.menu1.hide()
    },
    mostrarGenerarValoraciones () {
      this.visibleGenerar = true
    },
    mostrarBorrarValoraciones () {
      this.visibleBorrar = true
    },
    efectuarBorrarValoraciones (record) {
      this.borrarValoraciones(record)
        .then(response => {
          var vfilter = this.filter
          vfilter.mes = date.formatDate(this.recordGenerar.fecha, 'MM/YYYY')
          this.$emit('getRecords', vfilter)
          this.loadListaMeses()
        })
    },
    efectuarGenerarValoraciones () {
      var paramRecord = {
        codEmpresa: this.user.codEmpresa,
        mes: this.recordGenerar.mes,
        fecha: this.recordGenerar.fecha,
        idEntidad: this.recordGenerar.idEntidad,
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      var formData = new FormData()
      for (var key in paramRecord) {
        formData.append(key, paramRecord[key])
      }
      this.$axios.post('movimientos/bd_movimientos.php/generarValoraciones/', formData)
        .then(response => {
          var vfilter = this.filter
          vfilter.mes = date.formatDate(this.recordGenerar.fecha, 'MM/YYYY')
          this.loadListaMeses()
          this.$emit('getRecords', vfilter) // recarga grid
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    generarValoraciones (record) {
      this.recordGenerar = record
      this.visibleGenerar = false
      var venc = this.listaMeses.find(row => row.mes === date.formatDate(this.recordGenerar.fecha, 'MM/YYYY'))
      if (venc !== undefined) {
        this.$q.dialog({
          title: 'ATENCIÓN',
          message: 'Ya existen valoraciones para ese mes. Desea borrar primero las valoraciones de este tipo del destino?',
          ok: true,
          cancel: true,
          persistent: true
        }).onOk(() => {
          this.borrarValoraciones(record)
            .then(response => {
              this.efectuarGenerarValoraciones()
            })
        })
      } else {
        this.efectuarGenerarValoraciones()
      }
    },
    borrarValoraciones (record) {
      this.recordGenerar = record
      this.visibleBorrar = false
      var paramRecord = {
        codEmpresa: this.user.codEmpresa,
        fecha: this.recordGenerar.fecha,
        idEntidad: this.recordGenerar.idEntidad,
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      var formData = new FormData()
      for (var key in paramRecord) {
        formData.append(key, paramRecord[key])
      }
      return this.$axios.post('movimientos/bd_movimientos.php/borrarValoraciones/', formData)
    },
    wrapCsvValue (val, formatFn, row) {
      // formatFn = undefined // quito formatos
      let formatted = formatFn !== undefined
        ? formatFn(val, row)
        : val

      formatted = formatted === undefined || formatted === null
        ? ''
        : String(formatted)

      formatted = formatted.split('"').join('""')
      /**
       * Excel accepts \n and \r in strings, but some other CSV parsers do not
       * Uncomment the next two lines to escape new lines
       */
      // .split('\n').join('\\n')
      // .split('\r').join('\\r')

      return `"${formatted}"`
    },
    exportTable () {
      // naive encoding to csv format
      var rows = this.arrayTreeObj
      var columns1 = []
      columns1.push({})
      Object.assign(columns1[0], this.columns[0])
      columns1[0].field = 'tipoActivo'
      columns1[0].name = 'tipoActivo'
      columns1[0].label = 'Tipo Activo'
      columns1 = columns1.concat(this.columns)
      const content = [columns1.map(col => this.wrapCsvValue(col.label))].concat(
        rows.map(row => columns1.map(col => this.wrapCsvValue(
          typeof col.field === 'function'
            ? col.field(row)
            : row[col.field === undefined ? col.name : col.field],
          col.format,
          row
        )).join(','))
      ).join('\r\n')

      const status = exportFile(
        'table-export.csv',
        content,
        'text/csv'
      )

      if (status !== true) {
        this.$q.notify({
          message: 'Browser denied file download...',
          color: 'negative',
          icon: 'warning'
        })
      }
    },
    exportarExcel () {
      this.exportTable()
      // this.$emit('exportarExcel')
    },
    // METODOS DEL ARBOL DEL GRID
    customSort (rows, sortBy, descending) {
      const data = [...rows]
      if (sortBy) {
        data.sort((a, b) => {
          const x = descending ? b : a
          const y = descending ? a : b
          if (['nombre', 'nombreEntidad'].includes(sortBy)) {
            // string sort
            var strX = x.tipoActivo + x[sortBy]
            var strY = y.tipoActivo + y[sortBy]
            if (x[sortBy] === undefined) {
              if (descending) strX = x.tipoActivo + 'ZZZZZZZZ'
              else strX = x.tipoActivo
            }
            if (y[sortBy] === undefined) {
              if (descending) strY = y.tipoActivo + 'ZZZZZZZZ'
              else strY = y.tipoActivo
            }
          } else {
            // numeric sort
            strX = x.tipoActivo + this.$numeral(parseFloat(x[sortBy])).format('0000000000000.00')
            strY = y.tipoActivo + this.$numeral(parseFloat(y[sortBy])).format('0000000000000.00')
          }
          return strX.localeCompare(strY) // > strY) ? 1 : (strX < strY) ? -1 : 0
        })
      }
      return data
    },
    recursive (obj, newObj, level, itemId, isExpend) {
      const vm = this
      obj.forEach(function (o) {
        if (o.children && o.children.length !== 0) {
          o.level = level
          newObj.push(o)
          if (o.id === itemId) {
            o.expend = isExpend
          }
          if (o.expend === true) {
            vm.recursive(o.children, newObj, o.level + 1, itemId, isExpend)
          }
        } else {
          o.level = level
          newObj.push(o)
        }
      })
    },
    iconName (item) {
      if (item.expend === true) {
        return 'remove_circle_outline'
      }

      if (item.children && item.children.length > 0) {
        return 'control_point'
      }

      return 'done'
    },
    toggle (item) {
      const vm = this
      vm.itemId = item.id
      if (item.expend === true && item.children !== undefined) { // si ya estaba seleccionado lo quito
        vm.$set(item, 'expend', undefined)
        vm.itemId = null
      }
    },
    openWindow (strUrl) {
      if (window.cordova === undefined) { // desktop
        openURL(strUrl)
      } else { // estamos en un disp movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
    },
    clickColumn (colName, row) {
      if (colName === 'nombre') {
        row.id = row.idActivo
        this.addTab(['activosFormMain', 'Activo-' + row.idActivo, row, row.idActivo])
      }
      if (colName === 'urlinfo') {
        row.id = row.idActivo
        this.openWindow(row.urlinfo)
      }
    },
    setPadding (item) {
      return `padding-left: ${item.level * 30}px;`
    },
    selectedRow (item) {
      if (this.selectedRowID !== null) {
        this.$set(this.selectedRowID, 'id', item.id)
      }
    }
  },
  components: {
    valoracionesGenerar: valoracionesGenerar,
    valoracionesBorrar: valoracionesBorrar
  }
}
</script>
<style lang="sass">
  .valoracionesGrid-header-table
    .q-table__top,
    .q-table__bottom,
    thead tr:first-child th
      /* bg color is important for th; just specify one */
      background-color: $indigo-1

    thead tr th
      position: sticky
      z-index: 1
    thead tr:first-child th
      top: 0

    td:first-child
      background-color: $orange-1
    th:first-child
      position: sticky
      left: 0
      z-index: 3
    td:first-child
      position: sticky
      left: 0
      z-index: 2

    /* this is when the loading indicator appears */
    &.q-table--loading thead tr:last-child th
      /* height of all previous header rows */
      top: 48px
</style>
