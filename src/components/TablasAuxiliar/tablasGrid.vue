<template>
  <q-item class="row">
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="accionesGrid-header-table"
      virtual-scroll
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="value"
      :columns="columns"
      table-style="max-height: 70vh; max-width: 93vw"
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
          <q-th>
            <q-btn icon="more_vert"  class="q-ma-xs" color="primary" dense>
              <q-menu ref="menu1">
                <q-list dense>
                  <q-item key="new1" clickable v-close-popup @click.native="addRecord" >
                    <q-item-section avatar>
                      <q-icon name="add" />
                    </q-item-section>
                    <q-item-section>Añadir Registro</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
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
              <div style="max-width: 35px" v-if="rowId === `m_${props.row.id}`">
                <q-icon name="delete" class="text-red" style="font-size: 1.5rem;" @click="deleteRecord(props.row.id)"/>
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
            <q-popup-edit
              v-if="['codTabla','codElemento', 'valor1'].includes(col.name)"
              v-model="props.row[col.name]"
              buttons
              @save="updateRecord(props.row)">
              <q-input type="text" v-model="props.row[col.name]" dense autofocus />
            </q-popup-edit>
          </q-td>
        </q-tr>
      </template>
   <template v-slot:no-data>
        <div class="absolute-bottom text-center q-mb-sm">
          <q-btn
            @click="addRecord()"
            round
            dense
            color="indigo-5"
            size="20px"
            icon="add">
            <q-tooltip>Añadir Tabla Auxiliar</q-tooltip>
          </q-btn>
        </div>
        <div>
          Pulse + para añadir
        </div>
      </template>
      <template v-slot:bottom>
        <div class="absolute-bottom text-center q-mb-sm">
          <q-btn
            @click="addRecord()"
            round
            dense
            color="indigo-5"
            size="20px"
            icon="add">
            <q-tooltip>Añadir Tabla Auxiliar</q-tooltip>
          </q-btn>
        </div>
        <div>
          {{ `${value.length ? value.length + ' Filas' : 'No hay registros, pulse + para añadir clientes'}` }}
        </div>
      </template>
    </q-table>
      <q-dialog v-model="expanded"  >
        <!-- formulario con campos de filtro -->
        <tablasNueva
          @hide="(val) => { expanded = !expanded; value.push(val) }"
        />
      </q-dialog>
  </q-item>
</template>

<script>
import { mapActions } from 'vuex'
export default {
  props: ['value', 'idActivo'], // en 'value' tenemos la tabla de datos del grid, en idActivo en caso de que vengamos de ActivosFormMain
  data () {
    return {
      expanded: false,
      rowId: '',
      columns: [
        { name: 'id', align: 'left', label: 'id', field: 'id', sortable: true },
        { name: 'codTabla', align: 'left', label: 'codTabla', field: 'codTabla', sortable: true },
        { name: 'codElemento', align: 'left', label: 'codElemento', field: 'codElemento', sortable: true },
        { name: 'valor1', align: 'left', label: 'valor', field: 'valor1', sortable: true }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    ...mapActions('tablasAux', ['borrarTablaAux', 'addTablaAux', 'actualizarTablaAux']),
    addRecord () {
      this.expanded = !this.expanded
    },
    updateRecord (record) {
      this.actualizarTablaAux(record)
        .then((response) => {
          console.log(response.data)
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
        this.borrarTablaAux(id)
          .then(result => {
            this.$q.dialog({
              message: 'Se ha borrado la Tabla Auxiliar.'
            })
            var index = this.value.findIndex(function (record) { // busco elemento del array con este id
              if (record.id === id) return true
            })
            this.value.splice(index, 1) // lo elimino del array
          })
          .catch(error => {
            this.$q.dialog({
              message: error.message
            })
          })
      })
    }, // param : id, codTabla, codElemento, valor1
    mostrarDatosPieTabla () {
      return this.value.length + ' Filas'
    }
  },
  components: {
    tablasNueva: require('components/TablasAuxiliar/tablasNueva.vue').default
  }
}
</script>
<style lang="sass">
  .accionesGrid-header-table
    .q-table__top,
    .q-table__bottom,
    thead tr:first-child th
      /* bg color is important for th; just specify one */
      background-color: $blue-grey-1

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
