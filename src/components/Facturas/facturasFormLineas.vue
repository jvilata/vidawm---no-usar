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
              @click.stop="editRecord(props.row, props.row.id)"
              round
              dense
              size="sm"
              color="primary"
              icon="edit"/>
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

    <q-dialog v-model="mostrarDialog">
      <facturasFormLinDetalle @close="mostrarDialog=false"
        v-model="registroEditado"
        @saveRecord="saveRecord"/>
    </q-dialog>
  </q-item>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
import facturasFormLinDetalle from 'components/Facturas/facturasFormLinDetalle'
import wgDate from 'components/General/wgDate.vue'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      mostrarDialog: false,
      registrosSeleccionados: [],
      registroEditado: {},
      columns: [
        { name: 'idActivo', label: 'idActivo', align: 'center', field: 'idActivo', sortable: true },
        { name: 'descripcion', align: 'left', label: 'Descripción', field: 'descripcion', sortable: true, style: 'width: 200px; whiteSpace: normal' },
        { name: 'unidades', align: 'left', label: 'unidades', field: 'unidades', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'precio', align: 'left', label: 'precio', field: 'precio', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pdescuento', align: 'left', label: '%Dto', field: 'pdescuento', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'descuento', align: 'left', label: 'Descuento', field: 'descuento', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'neto', align: 'left', label: 'Neto', field: 'neto', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'piva', align: 'left', label: '%Iva', field: 'piva', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'totalIva', align: 'left', label: 'Total Iva', field: 'totalIva', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'totalLinea', align: 'left', label: 'Total Linea', field: 'totalLinea', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'id', label: 'Id', align: 'left', field: 'id', sortable: true },
        { name: 'user', align: 'left', label: 'user', field: 'user', sortable: true },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts', sortable: true }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO']),
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    getRecords () {
      var objFilter = { idcabFactura: this.value.id }
      return this.$axios.get('facturas/bd_facturas.php/findLinFacturasFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    addRecord () {
      var record = {
        idcabFactura: this.value.id,
        descripcion: 'Mes de ' + date.formatDate(new Date(), 'MMMM YYYY', { months: wgDate.data().myLocale.months }), // en español
        unidades: 1,
        precio: 0,
        pdescuento: 0,
        neto: 0,
        piva: 21,
        totalIva: 0,
        totalLinea: 0,
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      return this.$axios.post('facturas/bd_facturas.php/findLinFacturasFilter/', record)
        .then(response => {
          record.id = response.data.id
          this.registrosSeleccionados.push(record)
          this.editRecord(record, record.id)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
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
        return this.$axios.delete(`facturas/bd_facturas.php/findLinFacturasFilter/${id}`, JSON.stringify({ id: id }))
          .then(response => {
            var index = this.registrosSeleccionados.findIndex(function (record) { // busco elemento del array con este id
              if (record.id === id) return true
            })
            this.registrosSeleccionados.splice(index, 1) // lo elimino del array
            this.calcularTotalesLineas()
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    calcularTotalesLineas () {
      // calcula totales y pasalos a facturasFormMain
      var obj = { base: 0, totalIva: 0 }
      this.registrosSeleccionados.forEach(row => { obj.base += parseFloat(row.neto); obj.totalIva += parseFloat(row.totalIva) })
      this.$emit('calculaTotalesFac', obj)
    },
    updateRecord (recordToSubmit) {
      Object.assign(recordToSubmit, { user: this.user.user.email, ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss') })
      return this.$axios.put(`facturas/bd_facturas.php/findLinFacturasFilter/${recordToSubmit.id}`, recordToSubmit)
        .then(response => {
          this.calcularTotalesLineas()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    saveRecord (record) {
      this.mostrarDialog = false
      var index = this.registrosSeleccionados.findIndex(function (sel) { // busco elemento del array con este id
        if (sel.id === record.id) return true
      })
      Object.assign(this.registrosSeleccionados[index], record)
      this.updateRecord(record)
    },
    editRecord (rowChanges, id) {
      Object.assign(this.registroEditado, rowChanges)
      this.mostrarDialog = true
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    }
  },
  mounted () {
    this.getRecords()
  },
  components: {
    facturasFormLinDetalle: facturasFormLinDetalle
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
