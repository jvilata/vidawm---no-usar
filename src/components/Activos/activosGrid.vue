<template>
  <q-item class="row">
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="activosGrid-header-table"
      virtual-scroll
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="value"
      :columns="columns"
      table-style="max-height: 66vh; max-width: 93vw"
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
          <q-th >
            <q-btn icon="more_vert"  class="q-ma-xs" color="primary" dense>
              <q-menu ref="menu1">
                <q-list dense>
                  <q-item key="new1" clickable v-close-popup @click.native="addRecord" >
                    <q-item-section avatar>
                      <q-icon name="add" />
                    </q-item-section>
                    <q-item-section>Añadir Registro</q-item-section>
                  </q-item>
                  <q-item key="gene1" clickable v-close-popup @click.native="generarRentab" >
                    <q-item-section avatar>
                      <q-icon name="brightness_5" />
                    </q-item-section>
                    <q-item-section>Generar Rentabilidades ejercicio</q-item-section>
                  </q-item>
                  <q-item key="gene1" clickable v-close-popup @click.native="recalcularImportesMoneda" >
                    <q-item-section avatar>
                      <q-icon name="published_with_changes" />
                    </q-item-section>
                    <q-item-section>Recalcular importes moneda</q-item-section>
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
            <!-- columna de acciones: editar, borrar, etc -->
            <div style="max-width: 50px">
            <!--edit icon . Decomentamos si necesitamos accion especifica de edicion -->
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="editRecord(props.row, props.row.id)"
              round
              dense
              size="sm"
              color="primary"
              icon="edit">
              <q-tooltip>Editar</q-tooltip>
            </q-btn>
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="deleteRecord(props.row.id)"
              round
              dense
              size="sm"
              color="red"
              icon="delete">
              <q-tooltip>Borrar</q-tooltip>
            </q-btn>
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="duplicarActivo(props.row, props.row.id)"
              round
              dense
              size="sm"
              icon="file_copy">
              <q-tooltip>Duplicar</q-tooltip>
            </q-btn>
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

      <template v-slot:bottom>
        <div class="absolute-bottom q-mb-sm" style="left: 45vw">
          <q-btn
            @click.stop="addRecord"
            round
            dense
            color="primary"
            size="20px"
            icon="add">
            <q-tooltip>Añadir</q-tooltip>
          </q-btn>
        </div>
        <div>
          {{ value.length }} Filas
        </div>
      </template>

    </q-table>
  </q-item>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
import { headerFormData } from 'boot/axios.js'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      columns: [
        { name: 'nombre', align: 'left', label: 'Nombre', field: 'nombre', sortable: true, style: 'width: 300px; whiteSpace: normal' },
        { name: 'nombreEntidad', align: 'left', label: 'Gestor/Arrend', field: 'nombreEntidad', sortable: true, style: 'width: 130px; whiteSpace: normal' },
        { name: 'tipoActivo', label: 'tipoActivo', align: 'left', field: 'tipoActivo', sortable: true },
        { name: 'descEstadoActivo', align: 'left', label: 'Estado', field: 'descEstadoActivo', sortable: true },
        {
          name: 'computa',
          align: 'left',
          label: 'Computa',
          field: 'computa',
          sortable: true,
          format: val => {
            var obj = this.listaSINO.find(x => x.id === val) // mapea el valor 0 , 1 en la listaSINO a string SI , NO
            return (obj !== undefined ? obj.desc : val)
          }
        },
        { name: 'rentabEsp', align: 'left', label: '%Rent.Y', field: 'rentabEsp', sortable: true },
        { name: 'rentabAnt', align: 'left', label: '%Rent.Y-1', field: 'rentabAnt', sortable: true },
        { name: 'rentabReal', align: 'left', label: 'TIR Esper.', field: 'rentabReal', sortable: true },
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
    addRecord () {
      var record = {
        codEmpresa: this.user.codEmpresa,
        tipoActivo: 'CAJA',
        nombre: 'Nuevo activo',
        computa: '1',
        estadoActivo: '2',
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      var formData = new FormData()
      for (var key in record) {
        formData.append(key, record[key])
      }
      return this.$axios.post('activos/bd_activos.php/guardarBD', formData, headerFormData)
        .then(response => {
          record.id = response.data.id
          this.value.push(record)
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
        return this.$axios.delete(`activos/bd_activos.php/findActivosFilter/${id}`, JSON.stringify({ id: id }))
          .then(response => {
            var index = this.value.findIndex(function (record) { // busco elemento del array con este id
              if (record.id === id) return true
            })
            this.value.splice(index, 1) // lo elimino del array
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    editRecord (rowChanges, id) { // no lo uso aqui pero lod ejo como demo
      this.addTab(['activosFormMain', 'Activo-' + rowChanges.id, rowChanges, rowChanges.id])
    },
    duplicarActivo (record, id) {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Duplicar este activo ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(() => {
        Object.assign(record, {
          nombre: 'Copy - ' + record.nombre,
          user: this.user.user.email,
          ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
        })
        record.id = -1 // para que inserte nuevo
        var formData = new FormData()
        for (var key in record) {
          formData.append(key, record[key])
        }
        return this.$axios.post('activos/bd_activos.php/guardarBD', formData, headerFormData)
          .then(response => {
            record.id = response.data.id
            this.value.push(record)
            var param1 = {
              id: id,
              idnew: record.id
            }
            this.$axios.get('activos/bd_activos.php/duplicarActivo', { params: param1 })
              .then(response => {
                this.editRecord(record, record.id)
              })
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    generarRentab () {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Ejercicio a generar ?',
        prompt: {
          model: date.formatDate(new Date(), 'YYYY'),
          type: 'text' // optional
        },
        cancel: true,
        persistent: true
      }).onOk(data => {
        var record = {
          codEmpresa: this.user.codEmpresa,
          ejercicio: data
        }
        return this.$axios.get('activos/bd_act_rentabEspAnual.php/duplicarRentabAnual', { params: record })
          .then(response => {
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    recalcularImportesMoneda () {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Desea recalcular importes ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(data => {
        var record = {
          codEmpresa: this.user.codEmpresa
        }
        return this.$axios.get('movimientos/bd_movimientos.php/recalcularImportesMoneda', { params: record })
          .then(response => {
            this.$q.dialog({ title: 'Aviso', message: 'Importes actualizados' })
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    mostrarDatosPieTabla () {
      return this.value.length + ' Filas'
    }
  }
}
</script>
<style lang="sass">
  .activosGrid-header-table
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
