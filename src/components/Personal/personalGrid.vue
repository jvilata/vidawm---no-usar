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
                <q-btn flat v-if="rowId===`m_${props.row.id}`"
                  @click.stop="editRecord(props.row, props.row.id)"
                  round
                  dense
                  size="sm"
                  color="primary"
                  icon="edit">
                  <q-tooltip>Editar</q-tooltip>
                </q-btn>
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
            <q-tooltip>Añadir</q-tooltip>
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
            <q-tooltip>Añadir</q-tooltip>
          </q-btn>
        </div>
        <div>
          {{ `${value.length ? value.length + ' Filas' : 'No hay registros, pulse + para añadir'}` }}
        </div>
      </template>
    </q-table>
  </q-item>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
export default {
  props: ['value', 'idActivo'], // en 'value' tenemos la tabla de datos del grid, en idActivo en caso de que vengamos de ActivosFormMain
  data () {
    return {
      expanded: false,
      rowId: '',
      columns: [
        { name: 'id', align: 'left', label: 'id', field: 'id', sortable: true },
        { name: 'nombre', align: 'left', label: 'Nombre', field: 'nombre', sortable: true },
        { name: 'nombreAbreviado', align: 'left', label: 'nombre Abreviado', field: 'nombreAbreviado', sortable: true },
        { name: 'email', align: 'left', label: 'email', field: 'email', sortable: true },
        { name: 'telefono', align: 'left', label: 'telefono', field: 'telefono', sortable: true }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  computed: {
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    addRecord () {
      var record = {
        codEmpresa: this.user.codEmpresa,
        nombre: 'Nueva persona',
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      /* var formData = new FormData()
      for (var key in record) {
        formData.append(key, record[key])
      } */
      return this.$axios.post('personal/bd_personal.php/findPersonalFilter', record, { withCredentials: true }) // formData, headerFormData)
        .then(response => {
          record.id = response.data.id
          this.value.push(record)
          this.editRecord(record, record.id)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    editRecord (rowChanges, id) { // no lo uso aqui pero lod ejo como demo
      this.addTab(['personalFormMain', 'Persona -' + rowChanges.id, rowChanges, rowChanges.id])
    },
    deleteRecord (id) {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Borrar esta fila ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(() => {
        return this.$axios.delete(`personal/bd_personal.php/findPersonalFilter/${id}`, JSON.stringify({ id: id }))
          .then(result => {
            this.$q.dialog({
              message: 'Se ha borrado registro'
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
