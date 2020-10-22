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
                <q-icon name="vpn_key" class="primary" style="font-size: 1.5rem;" @click="cambiarPwd(props.row)"/>
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
            <q-popup-edit v-if="!['id'].includes(col.name)"
                v-model="props.row[col.name]"
                max-height="600px"
                buttons
                @save="updateRecord(props.row)">
                <!-- aqui definimos las ediciones especificas para cada columna -->
                <q-select v-if="['userRol'].includes(col.name)"
                  v-model="props.row[col.name]"
                  :options="listaRoles"
                  option-value="codElemento"
                  option-label="valor1"
                  emit-value
                  map-options
                />
                <q-input v-if="!['userRol'].includes(col.name)" v-model="props.row[col.name]"
                  autofocus counter
                  @keyup.enter.stop
                  style="width: 400px;"
                />
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
import { headerFormData } from 'boot/axios.js'
export default {
  props: ['value', 'idActivo'], // en 'value' tenemos la tabla de datos del grid, en idActivo en caso de que vengamos de ActivosFormMain
  data () {
    return {
      expanded: false,
      rowId: '',
      columns: [
        { name: 'id', align: 'left', label: 'Id', field: 'id', sortable: true },
        { name: 'email', align: 'left', label: 'Email', field: 'email', sortable: true },
        { name: 'username', align: 'left', label: 'User name', field: 'username', sortable: true },
        { name: 'codEmpresa', align: 'left', label: 'Empresas', field: 'codEmpresa', sortable: true },
        { name: 'userRol', align: 'left', label: 'User Rol', field: 'userRol', sortable: true },
        { name: 'idPersonal', align: 'left', label: 'idPersonal', field: 'idPersonal', sortable: true }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  computed: {
    ...mapState('login', ['user']),
    ...mapState('tablasAux', ['listaRoles'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    cambiarPwd (precord) {
      this.$q.dialog({
        title: 'Atención',
        message: 'Introduzca nueva clave',
        prompt: {
          model: '',
          type: 'text' // optional
        },
        cancel: true,
        persistent: true
      }).onOk(data => {
        var record = {
          action: 'reset',
          email: precord.email,
          password: data
        }
        var formData = new FormData()
        for (var key in record) {
          formData.append(key, record[key])
        }
        return this.$axios.post('users/users.php', formData, headerFormData)
          .then(result => {
            this.$q.dialog({
              message: 'Se ha actualizado password'
            })
          })
          .catch(error => {
            this.$q.dialog({
              message: error.message
            })
          })
      })
    },
    updateRecord (record) {
      return this.$axios.put(`users/bd_users.php/findUsersFilter/${record.id}`, JSON.stringify(record))
        .then(response => {
          // console.log(response.data)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    addRecord () {
      var record = {
        action: 'registrar',
        codEmpresa: this.user.codEmpresa,
        email: 'nuevo@nuevo.com',
        username: 'nuevo',
        password: 'nuevo'
      }
      var formData = new FormData()
      for (var key in record) {
        formData.append(key, record[key])
      }
      return this.$axios.post('users/users.php/', formData, headerFormData)
        .then(response => {
          this.$emit('getRecords')
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
        return this.$axios.delete(`users/bd_users.php/findUsersFilter/${id}`, JSON.stringify({ id: id }))
          .then(result => {
            this.$q.dialog({
              message: 'Se ha borrado registro'
            })
            this.$emit('getRecords')
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
