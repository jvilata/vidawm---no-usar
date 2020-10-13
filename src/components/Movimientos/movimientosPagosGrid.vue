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
              <q-menu>
                <q-list dense>
                  <q-item
                    v-for="(opcion, index) in listaOpciones"
                    :key="index"
                    clickable
                    v-close-popup
                    @click.native="ejecutarOpcion(opcion.function)"
                    >
                    <q-item-section avatar>
                      <q-icon :name="opcion.icon" />
                    </q-item-section>
                    <q-item-section>{{opcion.title}}</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-btn>
            Generar
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
            <span v-bind:style="setPadding(props.row)">
              <q-icon  style="cursor: pointer;"
              :name="iconName(props.row)" color="secondary"  >
              </q-icon>
              {{props.row.generar === '0' ? '0 - NO GENERAR' : '1 - GENERAR'}}
            </span>
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
              <q-popup-edit v-if="props.row.children.length===0 && !['nombre','nroFactura', 'verDoc', 'user','ts'].includes(col.name)"
                v-model="props.row[col.name]"
                max-height="600px"
                buttons
                @save="updateRecord(props.row)">
                <!-- aqui definimos las ediciones especificas para cada columna -->
                <q-input v-model="props.row[col.name]" v-if="!['generar', 'tipoOperacion','fecha','descripcion'].includes(col.name)"/>
                <q-select v-if="['generar'].includes(col.name)"
                  v-model="props.row[col.name]"
                  :options="listaSINO"
                  option-value="id"
                  option-label="desc"
                  emit-value
                  map-options
                />
                <q-input v-model="props.row[col.name]" v-if="['descripcion'].includes(col.name)"
                  type="textarea"
                  autofocus counter
                  @keyup.enter.stop
                  style="width: 400px;"/>
                <q-select v-if="['tipoOperacion'].includes(col.name)"
                  label="Tipo Operación"
                  v-model="props.row[col.name]"
                  :options="listaTipoOperacion"
                  option-value="codElemento"
                  option-label="codElemento"
                  emit-value
                  map-options
                />
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
import { openBlobFile } from 'components/General/cordova.js'
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
        { name: 'nombre', align: 'left', label: 'Nombre', field: 'nombre', sortable: true },
        { name: 'generar', align: 'left', label: 'Generar', field: 'generar', sortable: true },
        { name: 'nroFactura', align: 'left', label: 'Nro.Fact/Ref', field: 'nroFactura', sortable: true },
        { name: 'tipoOperacion', align: 'left', label: 'Tipo', field: 'tipoOperacion', sortable: true },
        { name: 'fecha', align: 'left', label: 'Fecha', field: 'fecha', sortable: true, format: val => (val === null || val === undefined ? '' : date.formatDate(date.extractDate(val, 'YYYY-MM-DD'), 'DD-MM-YYYY')) },
        { name: 'importe', align: 'left', label: 'Importe', field: 'importe', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'descripcion', align: 'left', label: 'Descripcion', field: 'descripcion', sortable: true, style: 'width: 300px; whiteSpace: normal' },
        { name: 'fechaGeneracion', align: 'left', label: 'F.Pag/Cob', field: 'fechaGeneracion', sortable: true, format: val => (val !== null && val !== undefined ? date.formatDate(date.extractDate(val, 'YYYY-MM-DD HH:mm:ss'), 'DD-MM-YYYY HH:mm:ss') : '') },
        { name: 'archivoDrive', align: 'left', label: 'Archivo Drive', field: 'archivoDrive', sortable: true, style: 'width: 300px; whiteSpace: normal' },
        { name: 'verDoc', align: 'left', label: 'Ver Doc.', field: 'verDoc' },
        { name: 'user', align: 'left', label: 'user', field: 'user', sortable: true },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts', sortable: true }
      ],
      pagination: { rowsPerPage: 0 },
      selectedRowID: {},
      isExpanded: true,
      itemId: null,
      listaOpciones: [
        { name: 'marcarTodos', title: 'Marcar todos', icon: 'add', function: 'marcarTodos' },
        { name: 'borrarMarcas', title: 'Borrar marcas', icon: 'remove', function: 'borrarMarcas' },
        { name: 'generarFichero', title: 'Generar Fichero', icon: 'picture_as_pdf', function: 'generarFichero' },
        { name: 'importarNominas', title: 'Importar Nóminas', icon: 'publish', function: 'importarNominas' },
        { name: 'enviarMails', title: 'Enviar Mails', icon: 'mail_outline', function: 'enviarMails' }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user']),
    ...mapState('tablasAux', ['listaTipoOperacion', 'listaSINO']),
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
    deleteRecord (id) {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Borrar esta fila ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(() => {
        return this.$axios.delete(`movimientos/bd_movimientos.php/movimientos/${id}`, JSON.stringify({ id: id }))
          .then(response => {
            var index = this.registrosSeleccionados.findIndex(function (record) { // busco elemento del array con este id
              if (record.id === id) return true
            })
            this.registrosSeleccionados.splice(index, 1) // lo elimino del array
          })
          .catch(error => {
            this.$q.dialog({ title: 'Error', message: error })
          })
      })
    },
    updateRecord (record) {
      var updated = {
        id: record.id,
        generar: record.generar,
        importe: record.importe,
        tipoOperacion: record.tipoOperacion,
        descripcion: record.descripcion,
        fecha: record.fecha,
        fechaGeneracion: record.fechaGeneracion,
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      return this.$axios.put(`movimientos/bd_movimientos.php/movimientos/${updated.id}`, JSON.stringify(updated))
        .then(response => {
          this.$emit('generarArbol')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    },
    ejecutarOpcion (opcion) {
      this[opcion](this.selectedRowID)
    },
    verDocumento (record) {
      if (record.archivoDrive !== '') {
        var strUrl = 'https://vidawealth-my.sharepoint.com/personal/jvilata_vidawm_com/Documents/' +
          this.user.nomEmpresa + '/PERSONAL/' + record.nombre + '/' + record.archivoDrive
        if (window.cordova === undefined) { // desktop
          openURL(strUrl)
        } else { // estamos en un disp movil
          window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
        }
      }
    },
    marcarTodos (record1) {
      var str1 = ''
      this.value.forEach(record => {
        if (record.children.length > 0) {
          record.children.forEach(recFinal => {
            if (str1.length > 0) str1 += ','
            str1 += recFinal.id
          })
        }
      })
      this.$axios.get('movimientos/bd_movimientos.php/generarMarcasPago', { params: { ids: str1, codEmpresa: this.user.codEmpresa } })
        .then(response => {
          this.$emit('generarArbol')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    borrarMarcas (record1) {
      this.$axios.get('movimientos/bd_movimientos.php/borrarMarcasPago', { params: { codEmpresa: this.user.codEmpresa } })
        .then(response => {
          this.$emit('generarArbol')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    generarFichero (record1) {
      var paramRecord = {
        codEmpresa: this.user.codEmpresa,
        enviarMail: 0,
        nompdf: this.value[0].tipoOperacion // tipoOperacion
      }
      var formData = new FormData()
      for (var key in paramRecord) {
        formData.append(key, paramRecord[key])
      }
      this.$axios.post('movimientos/pagos.php', formData, { responseType: 'blob' })
        .then(function (response) {
          var nomFile = 'remesa_' + date.formatDate(new Date(), 'YYYYMMDDHHmmss') + '.xml'
          if (window.cordova === undefined) { // desktop
            const url = window.URL.createObjectURL(new Blob([response.data], { type: response.data.type }))
            const link = document.createElement('a')
            link.href = url
            link.download = nomFile
            // link.target = '_blank'
            document.body.appendChild(link)
            // window.open('', 'view') // abre nueva ventana para que no sustituya a la actual
            link.click()
            document.body.removeChild(link)
          } else { // estamos en un disp movil            console.log('hola3')
            const blobPdf = response.data // new Blob([response.data], { type: response.data.type })
            openBlobFile(nomFile, blobPdf, response.data.type)
          }
        }).catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    enviarMultiplesMails (intentos, callback, indice, rows) {
      if (this.visibleSendMail === false) { // ya no se está mostrando
        callback(indice, rows)
      } else { // si se esta mostrando, tenemos que esperar
        setTimeout(() => {
          intentos++
          this.enviarMultiplesMails(intentos, callback, indice, rows)
        }, 1000)
      }
    },
    callbackMail (indice, rows) {
      var record = rows[indice]
      var email = record.email
      if (email === '') email = 'jvilata@edicom.es'
      var strobjeto = 'factura'
      if (this.value[0].tipoOperacion === 'NOMINA') strobjeto = 'nómina'
      var tmpAsunto = this.user.nomEmpresa + ' ha ordenado el pago de su ' + strobjeto + ' de importe ' + this.$numeral(parseFloat(record.importe)).format('0,0.00') + '€'
      var str = 'Hola,<br>En esta fecha, ' + tmpAsunto + ' y referencia ' + record.descripcion + ' en su cuenta ' + record.cuentaCorriente + '<br>Atentamente<br>VILATA DARDER HOLDING SL<br>' +
          '<img src="http://vidawm.com/img/VIDA_color.jpg"  width="100">'

      this.recordSendMail = {
        destino: email,
        destinoCopia: 'jvilata@edicom.es',
        asunto: (this.value[0].tipoOperacion === 'NOMINA' ? record.nombre + ', ' : '') + tmpAsunto,
        texto: str,
        url: (this.value[0].tipoOperacion === 'NOMINA' ? 'onedrive/downloadNomina.php?empresa=' + this.user.nomEmpresa + '&nombrePDF=' + record.archivoDrive + '&carpeta=' + record.nombre
          : 'lib/sendmail.php?accion=send')
      }
      this.visibleSendMail = true
      indice = indice + 1
      if (indice < rows.length) this.enviarMultiplesMails(0, this.callbackMail, indice, rows)
    },
    enviarMails (record1) {
      this.value.forEach(recordP => { // busco el nodo padre donde estan los marcados para generar
        if (recordP.generar === '1') {
          this.enviarMultiplesMails(0, this.callbackMail, 0, recordP.children)
        }
      })
    },
    importarNominas (record1) {
      // recorrer carpeta facturas y cargar en facturas estadoFactura PENDIENTE
      var host = this.$axios.defaults.baseURL // 'https://vidawm.com/privado/php/'
      var strUrl = host + 'onedrive/recorrerCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' +
          this.user.nomEmpresa + '&tipo=PERSONAL&carpeta=PERSONAL&estado='
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
