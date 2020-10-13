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
      table-style="max-height: 66vh; max-width: 93vw"
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
            <!-- columna de acciones: editar, borrar, etc -->
            <div style="max-width: 10px">
            <!-- edit icon . Decomentamos si necesitamos accion especifica de edicion ->
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="editRecord(props.row, props.row.id)"
              round
              dense
              size="sm"
              color="primary"
              icon="edit"/> -->
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="deleteRecord(props.row.id)"
              round
              dense
              size="sm"
              color="red"
              icon="delete">
              <q-tooltip>Borrar</q-tooltip>
            </q-btn>
            </div>
          </q-td>

          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div :style="col.style" v-if="!['automatismo'].includes(col.name)">
              {{ col.value }}
            </div>
            <div :style="col.style" v-if="['automatismo'].includes(col.name)">
              <q-btn :label="props.row['tipoAccion']" color="primary" style='width:100px' @click="generarAccion(props.row)"/>
            </div>
              <q-popup-edit v-if="!['id','automatismo', 'nomObjeto', 'user','ts'].includes(col.name)"
                v-model="props.row[col.name]"
                max-height="600px"
                buttons
                @save="updateRecord(props.row)">
                <!-- aqui definimos las ediciones especificas para cada columna -->
                  <q-select v-if="['idUserQuien'].includes(col.name)"
                  :value="col.value"
                  @input="(value) => props.row[col.name]=value"
                  :options="listaUsers"
                  option-value="email"
                  option-label="email"
                  emit-value
                  stack-label
                />
                <q-input v-if="['descripcion'].includes(col.name)" v-model="props.row[col.name]"
                  type="textarea"
                  autofocus counter
                  @keyup.enter.stop
                  style="width: 400px;"
                />
                <wgDate v-if="['fecha'].includes(col.name)"
                  v-model="props.row[col.name]" />
                <wgDate v-if="['fechaProx'].includes(col.name)"
                  v-model="props.row[col.name]"/>
                <!-- en q-select multiple los valores estan en un array, como en nuestro caso estan en un string separados por comas
                    hay que crear un array con los valores en el columns format (split) y aqui volver a convertir el array a string (join) -->
                <q-select v-if="['idUserQuienProx'].includes(col.name)"
                  :value="col.value"
                  @input="(value) => props.row[col.name]=value.join()"
                  multiple
                  :options="listaUsers"
                  option-value="email"
                  option-label="email"
                  emit-value
                  use-chips
                  stack-label
                />
                <q-select v-if="['tipoAccion'].includes(col.name)"
                  v-model="props.row[col.name]"
                  :options="listaTipoAcc"
                  option-value="codElemento"
                  option-label="codElemento"
                  emit-value
                  map-options
                />
                <q-select v-if="['realizada'].includes(col.name)"
                  v-model="props.row[col.name]"
                  :options="listaSINO"
                  option-value="id"
                  option-label="desc"
                  emit-value
                  map-options
                />
                <q-input v-if="['tipoObjeto', 'idObjeto'].includes(col.name)" v-model="props.row[col.name]"
                  autofocus counter
                  @keyup.enter.stop
                  style="width: 400px;"
                />
              </q-popup-edit>
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
            <q-tooltip>Añadir Registro</q-tooltip>
          </q-btn>
        </div>
        <div>
          {{ value.length }} Filas
        </div>
      </template>
      <template v-slot:no-data>
        <div class="absolute-bottom q-mb-sm" style="left: 45vw">
          <q-btn
            @click.stop="addRecord"
            round
            dense
            color="primary"
            size="20px"
            icon="add">
            <q-tooltip>Añadir Registro</q-tooltip>
          </q-btn>
        </div>
      </template>

    </q-table>
  </q-item>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
import wgDate from 'components/General/wgDate.vue'
import { newPostWindow } from 'components/General/libGeneral.js'
export default {
  props: ['value', 'idActivo', 'idEntidad'], // en 'value' tenemos la tabla de datos del grid, en idActivo en caso de que vengamos de ActivosFormMain
  data () {
    return {
      rowId: '',
      columns: [
        // { name: 'id', label: 'Id', align: 'left', field: 'id', sortable: true },
        { name: 'descripcion', align: 'left', label: 'Descripcion', field: 'descripcion', sortable: true, style: 'width: 300px; whiteSpace: normal' },
        { name: 'fechaProx', align: 'left', label: 'fechaProx', field: 'fechaProx', sortable: true, format: val => date.formatDate(date.extractDate(val, 'YYYY-MM-DD HH:mm:ss'), 'DD-MM-YYYY') },
        { name: 'idUserQuienProx', align: 'left', label: 'idUserQuienProx', field: 'idUserQuienProx', sortable: true, format: val => (val ? val.split(',') : null), style: 'width: 150px; whiteSpace: normal' },
        { name: 'tipoAccion', align: 'left', label: 'tipoAccion', field: 'tipoAccion', sortable: true },
        {
          name: 'realizada',
          align: 'left',
          label: 'realizada',
          field: 'realizada',
          sortable: true,
          format: val => {
            var obj = this.listaSINO.find(x => x.id === val) // mapea el valor 0 , 1 en la listaSINO a string SI , NO
            return (obj !== undefined ? obj.desc : val)
          }
        },
        { name: 'automatismo', label: 'Automatismo', field: 'automatismo', align: 'center' },
        { label: 'Tipo Obj', field: 'tipoObjeto', name: 'tipoObjeto' },
        { label: 'Id Obj.', field: 'idObjeto', name: 'idObjeto' },
        { label: 'Nombre', field: 'nomObjeto', name: 'nomObjeto' },
        { name: 'idUserQuien', required: true, label: 'Quien', align: 'left', field: row => row.idUserQuien, format: val => `${val}`, sortable: true },
        { name: 'fecha', align: 'left', label: 'Fecha', field: 'fecha', sortable: true, format: val => date.formatDate(date.extractDate(val, 'YYYY-MM-DD HH:mm:ss'), 'DD-MM-YYYY') },
        { name: 'user', align: 'left', label: 'user', field: 'user', sortable: true },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts', sortable: true }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaTipoAcc']),
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    updateRecord (record) {
      delete record.nomObjeto // campo calculado
      return this.$axios.put(`acciones/bd_acciones.php/findAccionesFilter/${record.id}`, JSON.stringify(record))
        .then(response => {
          // console.log(response.data)
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    addRecord () {
      var record = {
        idUserQuien: this.user.user.email,
        fecha: date.formatDate(new Date(), 'YYYY-MM-DD 00:00:00'),
        descripcion: 'Nueva acción',
        fechaProx: date.formatDate(new Date() + 7, 'YYYY-MM-DD 00:00:00'),
        idUserQuienProx: this.user.user.email,
        realizada: '0',
        tipoAccion: 'SEGUIMIENTO',
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      if (this.idActivo !== undefined) { // caso de venir desde activosFormMain o de entidades
        record.tipoObjeto = 'A'
        record.idObjeto = this.idActivo
      }
      if (this.idEntidad !== undefined) { // caso de venir desde activosFormMain o de entidades
        record.tipoObjeto = 'E'
        record.idObjeto = this.idEntidad
      }
      return this.$axios.post('acciones/bd_acciones.php/findAccionesFilter/', JSON.stringify(record))
        .then(response => {
          record.id = response.data.id
          this.value.push(record)
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
        return this.$axios.delete(`acciones/bd_acciones.php/findAccionesFilter/${id}`, JSON.stringify({ id: id }))
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
    mostrarDatosPieTabla () {
      return this.value.length + ' Filas'
    },
    generarAccion (record) {
      if (record.tipoAccion === 'FACTURAR') this.generarFactura(record)
      else if (record.tipoAccion === 'LIQUIDAR') this.generarLiquidacion(record)
      else if (record.tipoAccion === 'G.NOMINAS') this.generarNominas(record)
      else this.$q.dialog({ title: 'Aviso', message: record.tipoAccion })
    },
    generarFactura (record) {
      newPostWindow('facturas/bd_facturas.php/generarFactura', 'idObjeto', record.idObjeto, 'tipoObjeto', record.tipoObjeto)
    },
    generarLiquidacion (record) {
      newPostWindow('facturas/pdf_liquidacion.php/generaMovimientoPago', 'idObjeto', record.idObjeto, 'tipoObjeto', record.tipoObjeto)
    },
    generarNominas (record) {
      newPostWindow('movimientos/bd_movimientos.php/generarNominas', 'idObjeto', record.idObjeto, 'tipoObjeto', record.tipoObjeto)
    }
  },
  components: {
    wgDate: wgDate
  }
}
</script>
<style lang="sass">
  .accionesGrid-header-table
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
