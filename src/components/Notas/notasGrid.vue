<template>
  <q-item class="row" >
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="pagosGrid-header-table"
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
                    <q-item-section avatar v-if="opcion.children && opcion.children.length>0">
                      <q-icon name="keyboard_arrow_right" />
                    </q-item-section>
                      <q-menu v-if="opcion.children && opcion.children.length>0" anchor="top right" self="top left">
                        <q-list dense>
                          <q-item
                            v-for="(opcion1, index1) in opcion.children"
                            :key="index1"
                            clickable
                            v-close-popup
                            @click.native="ejecutarOpcion(opcion1)"
                            >
                            <q-item-section avatar>
                              <q-icon :name="opcion1.icon" />
                            </q-item-section>
                            <q-item-section>{{opcion1.title}}</q-item-section>
                          </q-item>
                        </q-list>
                    </q-menu>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
          </q-th>
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
        <q-tr :props="props"
          :key="`m_${props.row.id}`"
          @mouseover="rowId=`m_${props.row.id}`"
          :class="[(props.row.id !== selectedRowID.id) ? '':'bg-green-1']"
           @click="selectedRow(props.row)"
        >
          <q-td @click="toggle(props.row)">
            <div style='width: 120px;'>
            <span v-bind:style="setPadding(props.row)">
              <q-icon  style="cursor: pointer;"
              :name="iconName(props.row)" color="secondary"  >
              </q-icon>
              {{props.row.generar}}
            </span>
            </div>
          </q-td>
          <q-td>
            <!-- columna de acciones: editar, borrar, etc -->
            <div style="max-width: 20px">
            <!--edit icon . Decomentamos si necesitamos accion especifica de edicion -->
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="deleteRecord(props.row.id)"
              round
              dense
              size="sm"
              color="red"
              icon="delete"/>
            <q-btn flat v-if="rowId===`m_${props.row.id}`"
              @click.stop="generarPago(props.row)"
              round
              dense
              size="sm"
              color="primary"
              icon="brightness_5"/>
            </div>
          </q-td>

          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div :style="col.style" v-if="!['verDoc'].includes(col.name)">
              {{ col.value }}
            </div>
            <div :style="col.style" v-if="['verDoc'].includes(col.name) && props.row['archivoDrive'] !== undefined">
              <q-btn icon="open_in_browser" color="primary" style='width:50px' @click="verDocumento(props.row)"/>
            </div>
              <q-popup-edit v-if="props.row.children && props.row.children.length===0 && !['estadoNota', 'nummov', 'verDoc', 'user','ts'].includes(col.name)"
                v-model="props.row[col.name]"
                max-height="600px"
                buttons
                @save="updateRecord(props.row)">
                <!-- aqui definimos las ediciones especificas para cada columna -->
                <q-input v-model="props.row[col.name]" v-if="!['idPersonal', 'fecha','comentarios'].includes(col.name)"/>
                <q-select v-if="['idPersonal'].includes(col.name)"
                  outlined
                  clearable
                  v-model="props.row[col.name]"
                  :options="listaPersonal"
                  option-value="id"
                  option-label="nombre"
                  emit-value
                  map-options
                />
                <q-input v-model="props.row[col.name]" v-if="['comentarios'].includes(col.name)"
                  type="textarea"
                  autofocus counter
                  @keyup.enter.stop
                  style="width: 400px;"/>
                <wgDate v-if="['fecha'].includes(col.name)"
                  :value="formatDate(props.row[col.name])"
                  @input="value => props.row[col.name]=value" />
              </q-popup-edit>
          </q-td>
        </q-tr>
      </template>

    </q-table>
    <q-dialog v-model="visibleSendMail"  >
      <sendMail :value="recordSendMail" @close="visibleSendMail=false"/>
    </q-dialog>
  </q-item>
</template>

<script>
import { mapState } from 'vuex'
import { date, openURL } from 'quasar'
import wgDate from 'components/General/wgDate.vue'
import sendMail from 'components/SendMail/sendMail.vue'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      visibleSendMail: false,
      recordSendMail: {},
      registrosSeleccionados: [],
      rowId: '',
      columns: [
        { name: 'nroNota', align: 'left', label: 'NroNota', field: 'nroNota', sortable: true },
        { name: 'idPersonal', align: 'left', label: 'Id.Persona', field: 'idPersonal', sortable: true, format: val => { var per = this.listaPersonal.find(emp => emp.id === val); if (per === undefined) { return '' } else { return per.nombre } } },
        { name: 'fecha', align: 'left', label: 'Fecha', field: 'fecha', sortable: true, format: val => (val === null || val === undefined ? '' : date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY')) },
        { name: 'totalNota', align: 'left', label: 'Total Nota', field: 'totalNota', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'archivoDrive', align: 'left', label: 'Archivo Drive', field: 'archivoDrive', sortable: true, style: 'width: 300px; whiteSpace: normal' },
        { name: 'verDoc', align: 'left', label: 'Ver Doc.', field: 'verDoc' },
        { name: 'nummov', align: 'left', label: 'Cobr/Pag', field: 'nummov', sortable: true },
        { name: 'estadoNota', align: 'left', label: 'Estado Nota', field: 'estadoNota', sortable: true },
        { name: 'comentarios', align: 'left', label: 'Descripción', field: 'comentarios', sortable: true, style: 'width: 300px; whiteSpace: normal' },
        { name: 'carpeta', align: 'left', label: 'Carpeta', field: 'carpeta', sortable: true },
        { name: 'user', align: 'left', label: 'user', field: 'user', sortable: true },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts', sortable: true }
      ],
      pagination: { rowsPerPage: 0 },
      selectedRowID: {},
      isExpanded: true,
      itemId: null,
      listaOpciones: [
        { name: 'addRecord', title: 'Crear Nuevo', icon: 'add', function: 'addRecord', children: [] },
        {
          name: 'accionesOneDrive',
          title: 'Acciones OneDrive',
          icon: 'cloud',
          function: 'accionesOneDrive',
          children: [
            { name: 'cargarNotas', title: 'Cargar Notas', icon: 'backup', function: 'cargarNotas', children: [] },
            { name: 'enviarNotas', title: 'Enviar Notas', icon: 'email', function: 'enviarNotas', children: [] }
          ]
        }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user']),
    ...mapState('personal', ['listaPersonal']),
    ...mapState('entidades', ['entidadSelf', 'entidadAsesor']),
    // campo de tabla en arbol
    arrayTreeObj () {
      const vm = this
      var newObj = []
      if (vm.value !== undefined) vm.recursive(vm.value, newObj, 0, vm.itemId, vm.isExpanded)
      return newObj
    }
  },
  methods: {
    formatDate (pdate) {
      return date.formatDate(date.extractDate(pdate, 'YYYY-MM-DD'), 'YYYY-MM-DD HH:mm:ss')
    },
    getRecords () {
      this.registrosSeleccionados = this.value
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    },
    ejecutarOpcion (opcion) {
      if (opcion.children && opcion.children.length === 0) {
        this[opcion.function](this.selectedRowID)
        this.$refs.menu1.hide()
      }
    },
    verDocumento (record) {
      if (record.archivoDrive !== '') {
        var strUrl = 'https://vidawealth-my.sharepoint.com/personal/jvilata_vidawm_com/Documents/' +
          this.user.nomEmpresa + '/NOTAS/' + record.carpeta + '/' + record.archivoDrive
        if (window.cordova === undefined) { // desktop
          openURL(strUrl)
        } else { // estamos en un disp movil
          window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
        }
      }
    },
    addRecord () {
      var record = {
        nroNota: -1,
        codEmpresa: this.user.codEmpresa,
        idPersonal: this.user.pers.id,
        archivoDrive: '',
        carpeta: '',
        comentarios: '',
        estadoNota: 'PENDIENTE',
        totalNota: '0',
        fecha: date.formatDate(new Date(), 'YYYY-MM-DD'),
        fechafinal: date.formatDate(new Date(), 'YYYY-MM-DD'),
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      return this.$axios.post('notas/bd_notas.php/findNotasFilter', record)
        .then(response => {
          this.$emit('generarArbol')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    updateRecord (record) {
      var updated = Object.assign({}, record)
      Object.assign(updated, {
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      })
      return this.$axios.put(`notas/bd_notas.php/findNotasFilter/${updated.id}`, JSON.stringify(updated))
        .then(response => {
          this.$emit('generarArbol')
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
        return this.$axios.delete(`notas/bd_notas.php/findNotasFilter/${id}`, JSON.stringify({ id: id }))
          .then(response => {
            this.$emit('generarArbol')
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    generarPago (record) {
      var params = {
        codEmpresa: this.user.codEmpresa,
        nroNota: record.nroNota,
        idPersonal: record.idPersonal
      }
      this.$axios.get('notas/bd_notas.php/generarPagoCobroNota', { params: params })
        .then(response => {
          this.$q.dialog({ title: 'Aviso', message: 'Cobro generado' })
          this.$emit('generarArbol')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    enviarNotas () {
      this.recordSendMail = {
        destino: this.entidadAsesor.email,
        destinoCopia: this.entidadSelf.email,
        asunto: 'Te adjunto notas de gasto de ' + this.user.nomEmpresa,
        texto: 'Hola,<br>Le adjuntamos notas de gasto de la empresa:' + this.user.nomEmpresa + ' en este enlace de OnDrive:%enlace%' +
          '<br>Atentamente,<br>' + this.entidadSelf.nombre + '<br>' +
          (this.entidadSelf.logo !== '' ? '<img src="http://vidawm.com/img/' + this.entidadSelf.logo + '"  width="100">' : ''),
        url: 'onedrive/moverElementosCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' + this.user.nomEmpresa +
          '&tipo=NOTAS&carpeta=NOTAS&estado='
      }
      this.visibleSendMail = true
    },
    cargarNotas () {
      // recorrer carpeta notas y cargar en bd estado PENDIENTE
      var host = this.$axios.defaults.baseURL // 'https://vidawm.com/privado/php/'
      var strUrl = host + 'onedrive/recorrerCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' +
          this.user.nomEmpresa + '&tipo=NOTAS&carpeta=NOTAS&estado='
      if (window.cordova === undefined) { // desktop
        openURL(strUrl)
      } else { // dispositivo movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
    },
    // metodos de tabla en arbol
    customSort (rows, sortBy, descending) {
      const data = [...rows]
      if (sortBy) {
        data.sort((a, b) => {
          const x = descending ? b : a
          const y = descending ? a : b
          if (['nombre'].includes(sortBy)) {
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
    setPadding (item) {
      return `padding-left: ${item.level * 30}px;`
    },
    selectedRow (item) {
      if (this.selectedRowID !== null) {
        this.$set(this.selectedRowID, 'id', item.id)
      }
    }
  },
  mounted () {
    this.registrosSeleccionados = this.value
  },
  components: {
    wgDate: wgDate,
    sendMail: sendMail
  }
}
</script>
<style lang="sass">
  .pagosGrid-header-table
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
