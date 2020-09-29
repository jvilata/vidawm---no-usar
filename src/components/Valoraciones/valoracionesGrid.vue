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
      table-style="max-height: 70vh; max-width: 93vw"
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
          <q-th>
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
          </q-td>
        </q-tr>
      </template>

      <template v-slot:bottom>
        <div>
          {{ value.length }} Filas
        </div>
      </template>

    </q-table>
  </q-item>
</template>

<script>
import { date } from 'quasar'
import { mapActions } from 'vuex'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
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
        { name: 'valant_fecha', align: 'left', label: 'F.Anterior', field: 'valant_fecha', format: val => (val !== undefined ? date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY') : '') },
        { name: 'difanterior', align: 'right', label: 'Dif.Ant', field: row => parseFloat(row.importe) - parseFloat(row.valant_importe), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'minval_fecha', align: 'left', label: 'F.Inicial', field: 'minval_fecha', format: val => (val !== undefined ? date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY') : '') },
        { name: 'minval_importe', align: 'right', label: 'Imp.Inicio', field: 'minval_importe', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'impcompvent', align: 'right', label: 'Comp/Vent.Año', field: 'impcompvent', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'factuInteres', align: 'right', label: 'Factur/Inter', field: row => parseFloat(row.impcobropago) + parseFloat(row.facturado), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'revalorizacion', align: 'right', label: 'Revalorización', field: b => parseFloat(b.importe) + parseFloat(b.facturado) + parseFloat(b.impcobropago) - (parseFloat(b.minval_importe) + parseFloat(b.impcompvent)), sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'peso', align: 'right', label: '%Peso', field: 'peso', sortable: true, format: val => (val !== undefined ? this.$numeral(parseFloat(val)).format('0.00%') : '') },
        {
          name: 'rentabAcum',
          required: true,
          label: '%Real',
          align: 'right',
          field: b => {
            var res = 0
            var newValue = parseFloat(b.importe) + parseFloat(b.facturado) + parseFloat(b.impcobropago) - (parseFloat(b.minval_importe) + parseFloat(b.impcompvent))
            if (newValue !== 0 && (parseFloat(b.minval_importe) + parseFloat(b.impcompras)) !== 0) {
              res = newValue * 100 / (parseFloat(b.minval_importe) + parseFloat(b.impcompras))
            }
            return res
          },
          format: val => parseFloat(val).toFixed(2)
        },
        { name: 'impcompras', align: 'right', label: 'Imp.Compras', field: 'impcompras', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'rentabilidadEsperada', align: 'right', label: '%Rent.Esper', field: 'rentabilidadEsperada', sortable: true },
        { name: 'user', align: 'left', label: 'user', field: 'user' },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts' }
      ],
      pagination: { rowsPerPage: 0 },
      selectedRowID: {},
      isExpanded: true,
      itemId: null
    }
  },
  computed: {
    arrayTreeObj () {
      const vm = this
      var newObj = []
      if (vm.value !== undefined) vm.recursive(vm.value, newObj, 0, vm.itemId, vm.isExpanded)
      return newObj
    }
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    mostrarDatosPieTabla () {
      return this.value.length + ' Filas'
    },
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

          return (strX > strY) ? 1 : (strX < strY) ? -1 : 0
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
    clickColumn (colName, row) {
      if (colName === 'nombre') {
        row.id = row.idActivo
        this.addTab(['activosFormMain', 'Activo-' + row.idActivo, row, row.idActivo])
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
