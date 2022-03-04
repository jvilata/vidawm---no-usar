<template>
  <q-item class="row">
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="facturasFormLineas-header-table"
      dense
      virtual-scroll
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="registrosSeleccionados"
      :columns="columns"
      table-style="max-height: 70vh; max-width: 93vw"
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
          <q-th>
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
        <q-tr :props="props" :key="`m_${props.row.id}`" @mouseover="rowId=`m_${props.row.id}`">
          <q-td>
            <!-- columna de acciones: editar, borrar, etc -->
            <div style="max-width: 30px">
            <!--edit icon . Decomentamos si necesitamos accion especifica de edicion -->
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="deleteRecord(props.row.id)"
              round
              dense
              size="sm"
              color="red"
              icon="delete"/>
            </div>
          </q-td>

          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div :style="col.style">
              {{ col.value }}
            </div>
            <q-popup-edit v-if="!['user','ts'].includes(col.name)"
                v-model="props.row[col.name]"
                max-height="600px"
                buttons
                @save="updateRecord(props.row)">
                <!-- aqui definimos las ediciones especificas para cada columna -->
                <q-input v-if="col.name !== 'status'" v-model="props.row[col.name]"/>
                <q-select v-if="col.name === 'status'"
                  class="col-xs-12 col-sm-6"
                  label="Satus"
                  stack-label
                  outlined
                  clearable
                  v-model="props.row[col.name]"
                  :options="listaStatusAlt"
                  option-value="codElemento"
                  option-label="codElemento"
                  emit-value
              />
            </q-popup-edit>
          </q-td>
        </q-tr>
      </template>

      <template v-slot:no-data>
        <div class="absolute-bottom q-mb-sm" style="left: 45vw">
          <q-btn
            @click.stop="addRecord"
            round
            dense
            color="primary"
            size="15px"
            icon="add"/>
        </div>
        <div>
          No hay registros, pulse el botón + para añadir
        </div>
      </template>
      <template v-slot:bottom-row>
        <!-- BOTTOM-ROW DE LA TABLA -->
        <q-tr >
          <q-th>
             <div style="max-width: 35px"></div>
          </q-th>
          <q-th
            v-for="col in columns"
            :key="col.name"
            :align="col.align"
          >
            <div v-if="['fundName'].includes(col.name)">Media solo vendidos / desinvirtiendo</div>
            <div v-if="['grossMultiple'].includes(col.name)">{{ recordToSubmit.avgSoldGrossMultiple }}x</div>
            <div v-if="['grossIrr'].includes(col.name)">{{ recordToSubmit.avgSoldGrossIrr }}%</div>
            <div v-if="['netMultiple'].includes(col.name)">{{ recordToSubmit.avgSoldNetMultiple }}x</div>
            <div v-if="['netIrr'].includes(col.name)">{{ recordToSubmit.avgSoldNetIrr }}%</div>
            <div v-if="['annualCashYield'].includes(col.name)">{{ recordToSubmit.avgSoldAnnualCashYield }}%</div>
          </q-th>
        </q-tr>
        <q-tr >
          <q-th>
             <div style="max-width: 35px"></div>
          </q-th>
          <q-th
            v-for="col in columns"
            :key="col.name"
            :align="col.align"
          >
            <div v-if="['fundName'].includes(col.name)">Media total</div>
            <div v-if="['grossMultiple'].includes(col.name)">{{ recordToSubmit.totGrossMultiple }}x</div>
            <div v-if="['grossIrr'].includes(col.name)">{{ recordToSubmit.totGrossIrr }}%</div>
            <div v-if="['netMultiple'].includes(col.name)">{{ recordToSubmit.totNetMultiple }}x</div>
            <div v-if="['netIrr'].includes(col.name)">{{ recordToSubmit.totNetIrr }}%</div>
          </q-th>
        </q-tr>
      </template>
      <template v-slot:bottom>
        <div class="absolute-bottom q-mb-sm" style="left: 45vw">
          <q-btn
            @click.stop="addRecord"
            round
            dense
            color="primary"
            size="15px"
            icon="add"/>
        </div>
        <div>
          {{ registrosSeleccionados.length }} Filas
        </div>
      </template>
    </q-table>

  </q-item>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      mostrarDialog: false,
      registrosSeleccionados: [],
      registroEditado: {},
      recordToSubmit: {
        totGrossMultiple: 0,
        totGrossIrr: 0,
        totNetMultiple: 0,
        totNetIrr: 0,
        avgSoldGrossMultiple: 0,
        avgSoldGrossIrr: 0,
        avgSoldNetMultiple: 0,
        avgSoldNetIrr: 0,
        avgSoldAnnualCashYield: 0
      },
      columns: [
        { name: 'fundName', align: 'left', label: 'Fund Name', field: 'fundName', sortable: true, style: 'width: 200px; whiteSpace: normal' },
        { name: 'vintage', align: 'left', label: 'Vintage', field: 'vintage', sortable: true },
        { name: 'size', align: 'left', label: 'Size', field: 'size', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'status', align: 'left', label: 'Status', field: 'status', sortable: true },
        { name: 'pCommitted', align: 'left', label: '%Committed', field: 'pCommitted', sortable: true },
        { name: 'grossMultiple', align: 'left', label: 'Gross Mult', field: 'grossMultiple', sortable: true },
        { name: 'grossIrr', align: 'left', label: 'Gross IRR', field: 'grossIrr', sortable: true },
        { name: 'netMultiple', align: 'left', label: 'Net Multiple', field: 'netMultiple', sortable: true },
        { name: 'netIrr', align: 'left', label: 'Net IRR', field: 'netIrr', sortable: true },
        { name: 'annualCashYield', label: 'Annual Cash Yeld', align: 'left', field: 'annualCashYield', sortable: true },
        { name: 'dpi', label: 'DPI', align: 'left', field: 'dpi', sortable: true },
        { name: 'user', align: 'left', label: 'user', field: 'user', sortable: true },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts', sortable: true }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaStatusAlt']),
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    calcTotales (lineas) {
      var numl = 0
      var numlt = 0
      this.recordToSubmit = {
        totGrossMultiple: 0,
        totGrossIrr: 0,
        totNetMultiple: 0,
        totNetIrr: 0,
        avgSoldGrossMultiple: 0,
        avgSoldGrossIrr: 0,
        avgSoldNetMultiple: 0,
        avgSoldNetIrr: 0,
        avgSoldAnnualCashYield: 0
      }
      lineas.forEach(r => {
        numlt++
        this.recordToSubmit.totGrossMultiple += parseFloat(r.grossMultiple)
        this.recordToSubmit.totGrossIrr += parseFloat(r.grossIrr)
        this.recordToSubmit.totNetMultiple += parseFloat(r.netMultiple)
        this.recordToSubmit.totNetIrr += parseFloat(r.netIrr)
        if (r.status === 'Sold' || r.status === 'Divesting') { // 7.2.22 quito || r.status === 'Tenure' || r.status === 'Divested'
          numl++
          this.recordToSubmit.avgSoldGrossMultiple += parseFloat(r.grossMultiple)
          this.recordToSubmit.avgSoldGrossIrr += parseFloat(r.grossIrr)
          this.recordToSubmit.avgSoldNetMultiple += parseFloat(r.netMultiple)
          this.recordToSubmit.avgSoldNetIrr += parseFloat(r.netIrr)
          this.recordToSubmit.avgSoldAnnualCashYield += parseFloat(r.annualCashYield)
        }
      })
      if (numlt > 0) {
        this.recordToSubmit.totGrossMultiple = Math.round(this.recordToSubmit.totGrossMultiple * 100 / numlt) / 100
        this.recordToSubmit.totGrossIrr = Math.round(this.recordToSubmit.totGrossIrr * 100 / numlt) / 100
        this.recordToSubmit.totNetMultiple = Math.round(this.recordToSubmit.totNetMultiple * 100 / numlt) / 100
        this.recordToSubmit.totNetIrr = Math.round(this.recordToSubmit.totNetIrr * 100 / numlt) / 100
      }
      if (numl > 0) {
        this.recordToSubmit.avgSoldGrossMultiple = Math.round(this.recordToSubmit.avgSoldGrossMultiple * 100.0 / numl) / 100
        this.recordToSubmit.avgSoldGrossIrr = Math.round(this.recordToSubmit.avgSoldGrossIrr * 100 / numl) / 100
        this.recordToSubmit.avgSoldNetMultiple = Math.round(this.recordToSubmit.avgSoldNetMultiple * 100 / numl) / 100
        this.recordToSubmit.avgSoldNetIrr = Math.round(this.recordToSubmit.avgSoldNetIrr * 100 / numl) / 100
        this.recordToSubmit.avgSoldAnnualCashYield = Math.round(this.recordToSubmit.avgSoldAnnualCashYield * 100 / numl) / 100
      }
    },
    getRecords () {
      // se reutiliza el grid de movimientos para el form de activos y de facturas
      var objFilter = { idActivo: this.value.id }
      return this.$axios.get('activos/bd_act_altdatos.php/findAct_trackrecordFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
          this.calcTotales(this.registrosSeleccionados)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error.message })
        })
    },
    addRecord () {
      // se reutiliza el grid de movimientos para el form de activos y de facturas
      var record = {
        idActivo: this.value.id,
        fundName: this.value.nombre,
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      return this.$axios.post('activos/bd_act_altdatos.php/act_trackrecord', JSON.stringify(record))
        .then(response => {
          record.id = response.data.id
          this.registrosSeleccionados.push(record)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error.message })
        })
    },
    deleteRecord (id) {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Borrar esta fila ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(() => {
        return this.$axios.delete(`activos/bd_act_altdatos.php/act_trackrecord/${id}`, JSON.stringify({ id: id }))
          .then(response => {
            var index = this.registrosSeleccionados.findIndex(function (record) { // busco elemento del array con este id
              if (record.id === id) return true
            })
            this.registrosSeleccionados.splice(index, 1) // lo elimino del array
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error.message })
          })
      })
    },
    updateRecord (record) {
      return this.$axios.put(`activos/bd_act_altdatos.php/act_trackrecord/${record.id}`, JSON.stringify(record))
        .then(response => {
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error.message })
        })
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    }
  },
  mounted () {
    this.getRecords()
  }
}
</script>
<style lang="sass">
  .facturasFormLineas-header-table
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

    /* this is when the loading indicator appears */
    &.q-table--loading thead tr:last-child th
      /* height of all previous header rows */
      top: 48px
</style>
