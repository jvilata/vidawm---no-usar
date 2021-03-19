<template>
  <q-item class="row center" >
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="col dashboardGridPlanif-header-table"
      dense
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="registrosSeleccionados"
      :columns="columns"
      table-style="max-height: 70vh; max-width: 93vw"
      hide-bottom
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
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
        <q-tr :props="props" :key="`m_${props.row.id}`" @mouseover="rowId=`m_${props.row.id}`">
          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div :style="col.style">
              {{ col.value }}
              <q-popup-edit v-if="['pRentTeorica', 'pTeorico'].includes(col.name)"
                v-model="props.row[col.name]"
                max-height="600px"
                buttons
                @save="updateRecord(props.row)">
                <!-- aqui definimos las ediciones especificas para cada columna -->
                <q-input v-if="['pRentTeorica'].includes(col.name)" v-model="props.row[col.name]"
                  autofocus counter />
                <q-input v-if="['pTeorico'].includes(col.name)" v-model="props.row[col.name]"
                  autofocus counter />
              </q-popup-edit>
            </div>
          </q-td>
        </q-tr>
      </template>

      <template v-slot:bottom-row>
        <!-- BOTTOM-ROW DE LA TABLA -->
        <q-tr >
          <q-th
            v-for="col in columns"
            :key="col.name"
            :align="col.align"
          >
            <div v-if="['pRentTeorica'].includes(col.name)">{{ registrosSeleccionados.reduce((a, b) => a + (parseFloat(b.pRentTeorica) * parseFloat(b.pTeorico)/10000 || 0), 0) | numeralFormat('0.00%') }}</div>
            <div v-if="['pRentEsperada'].includes(col.name)">{{ registrosSeleccionados.reduce((a, b) => a + (parseFloat(b.pRentEsperada) * parseFloat(b.prealInicial)/10000 || 0), 0) | numeralFormat('0.00%') }}</div>
            <div v-if="['rentabAcum'].includes(col.name)">{{ parseFloat(registrosPanelDatos.rentabilidadReal)/100 | numeralFormat('0.00%') }}</div>
            <div v-if="!['tipoActivo', 'pRentTeorica', 'pRentEsperada', 'rentabAcum'].includes(col.name)">{{ registrosSeleccionados.reduce((a, b) => a + (parseFloat(b[col.name]) || 0), 0) | numeralFormat }}</div>
          </q-th>
        </q-tr>
      </template>

    </q-table>
  </q-item>
</template>

<script>
// import numeral from 'numeral'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      registrosSeleccionados: [],
      registrosPanelDatos: [],
      columns: [
        { name: 'tipoActivo', required: true, label: 'Tipo Activo', align: 'right', field: 'tipoActivo' },
        { name: 'pRentTeorica', required: true, label: 'Rent.Libros', align: 'right', field: 'pRentTeorica' },
        { name: 'pRentEsperada', required: true, label: 'Rent.Esper', align: 'right', field: 'pRentEsperada', format: val => parseFloat(val).toFixed(2) },
        {
          name: 'rentabAcum',
          required: true,
          label: '%Rent.Real',
          align: 'right',
          field: b => {
            var res = 0
            var newValue = parseFloat(b.patrimonio) + parseFloat(b.facturado) + parseFloat(b.impcobropago) - (parseFloat(b.patrimonioInicial) + parseFloat(b.impcompvent))
            if (newValue !== 0 && (parseFloat(b.patrimonioInicial) + parseFloat(b.impcompras)) !== 0) {
              res = newValue * 100 / (parseFloat(b.patrimonioInicial) + parseFloat(b.impcompras))
            }
            return res
          },
          format: val => parseFloat(val).toFixed(2)
        },
        { name: 'patrimonio', required: true, label: 'Importe Actual', align: 'right', field: 'patrimonio', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pTeorico', required: true, label: '%Part.Ideal', align: 'right', field: 'pTeorico' },
        { name: 'preal', required: true, label: '%Real', align: 'right', field: 'preal', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometido6m', required: true, label: 'comprom.+6m', align: 'right', field: 'comprometido6m', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        // { name: 'prealComprometidoAnyoSig', required: true, label: '%R+C 1Y', align: 'right', field: 'prealComprometidoAnyoSig', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometido7_24m', required: true, label: 'comprom.7-24m', align: 'right', field: 'comprometido7_24m', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometido', required: true, label: 'Comprom.Total', align: 'right', field: 'comprometido', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'prealComprometido', required: true, label: '%Real+Compr', align: 'right', field: 'prealComprometido', format: val => parseFloat(val).toFixed(2) },
        { name: 'pajuste', required: true, label: '%Ajuste', align: 'right', field: 'pajuste', format: val => parseFloat(val).toFixed(2) },
        { name: 'importeAjuste', required: true, label: 'Imp.Ajuste', align: 'right', field: 'importeAjuste', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'dividendos', required: true, label: 'Dividendos', align: 'right', field: 'dividendos', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'InversionAnual', required: true, label: 'Inver.Anual', align: 'right', field: 'InversionAnual', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'InversionMensual', required: true, label: 'Inver.Mensual', align: 'right', field: 'InversionMensual', format: val => this.$numeral(parseFloat(val)).format('0,0.00') }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  methods: {
    updateRecord (record) {
      return this.$axios.put(`activos/bd_activos.php/cActivosInversion/${record.id}`, JSON.stringify(record))
        .then(response => {
          // console.log(response.data)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getActivosInversion (objFilter) {
      // grid por tipos de activos inversion
      this.$axios.get('activos/bd_activos.php/cActivosInversion/', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getPanelDatos (objFilter) { // lo duplico de dashboardPanelDatos, me hace falta para los totales del grid
      // cards resumen de rentabilidad y patrimonio actual
      this.$axios.get('movimientos/bd_movimientos.php/findcpanelDatos/', { params: objFilter })
        .then(response => {
          this.registrosPanelDatos = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    }
  },
  mounted () {
    this.getActivosInversion(this.value) // carga grid de activos Inversion
    this.getPanelDatos(this.value) // carga panel de datos, me hace falta para totales grid
  }
}
</script>
<style lang="sass">
  .dashboardGridPlanif-header-table
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
