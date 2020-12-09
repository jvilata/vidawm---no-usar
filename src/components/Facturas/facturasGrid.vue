<template>
  <div>
    <q-card flat v-if="value.nombre">
      <q-card-section   class="q-pa-xs">
            <q-item class="q-pa-xs bg-indigo-1 text-grey-8">
              <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
              <q-item-section avatar>
                <q-icon name="edit" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-h6">
                  {{ value.nombre }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn
                @click="$emit('close')"
                flat
                round
                dense
                icon="close"/>
              </q-item-section>
            </q-item>
      </q-card-section>
    </q-card>
  <q-item class="row">
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="facturasGrid-header-table"
      virtual-scroll
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="registrosSeleccionados"
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
                    <q-item-section avatar v-if="opcion.children.length>0">
                      <q-icon name="keyboard_arrow_right" />
                    </q-item-section>
                      <q-menu v-if="opcion.children.length>0" anchor="top right" self="top left">
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
            <div style="max-width: 45px">
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
              round
              dense
              size="sm"
              icon="more_vert">
              <q-tooltip anchor="top middle">Más Opciones</q-tooltip>
              <q-menu>
                <q-list dense style="min-width: 100px">
                  <q-item clickable @click="imprimir(props.row)">
                    <q-item-section avatar>
                      <q-icon name="print" />
                    </q-item-section>
                    <q-item-section>Imprimir</q-item-section>
                  </q-item>
                  <q-item clickable @click="duplicar(props.row)">
                    <q-item-section avatar>
                      <q-icon name="file_copy" />
                    </q-item-section>
                    <q-item-section>Duplicar</q-item-section>
                  </q-item>
                  <q-separator />
                  <q-item clickable @click="enviarEmail(props.row)">
                    <q-item-section avatar>
                      <q-icon name="email" />
                    </q-item-section>
                    <q-item-section>Enviar por email</q-item-section>
                  </q-item>
                  <q-item clickable @click="generarPago(props.row)">
                    <q-item-section avatar>
                      <q-icon name="brightness_5" />
                    </q-item-section>
                    <q-item-section>Generar Pago</q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
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
            size="20px"
            icon="add">
            <q-tooltip>Añadir Registro</q-tooltip>
          </q-btn>
        </div>
        <div>
          {{ value.length }} Filas
        </div>
      </template>

    </q-table>

    <q-dialog v-model="visibleSendMail"  >
      <sendMail :value="recordSendMail" @close="visibleSendMail=false"/>
    </q-dialog>
  </q-item>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date, openURL } from 'quasar'
import { openBlobFile } from 'components/General/cordova.js'
import sendMail from 'components/SendMail/sendMail.vue'

export default {
  props: ['value', 'fromFacturasMain'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      registrosSeleccionados: [],
      recordSendMail: {},
      visibleSendMail: false,
      columns: [
        { name: 'nomEntidad', align: 'left', label: 'Nombre', field: 'nomEntidad', sortable: true, style: 'width: 130px; whiteSpace: normal' },
        { name: 'fecha', align: 'left', label: 'Fecha', field: 'fecha', sortable: true, format: val => date.formatDate(date.extractDate(val, 'YYYY-MM-DD HH:mm:ss'), 'DD-MM-YYYY') },
        { name: 'nroFactura', align: 'left', label: 'NºFactura', field: 'nroFactura', sortable: true },
        { name: 'base', align: 'left', label: 'Base', field: 'base', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'totalIva', align: 'left', label: 'Total Iva', field: 'totalIva', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'totalFactura', align: 'left', label: 'Total Factura', field: 'totalFactura', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'por_retencion', align: 'left', label: '%Ret', field: 'por_retencion', sortable: true },
        { name: 'retencion', align: 'left', label: 'Retencion', field: 'retencion', sortable: true, format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        {
          name: 'nummov',
          align: 'left',
          label: 'Cobr/Pag',
          field: 'nummov',
          sortable: true,
          format: val => (val > 0 ? 'SI' : '')
        },
        { name: 'tipoFactura', align: 'right', label: 'Tipo', field: 'tipoFactura', sortable: true },
        { name: 'estadoFactura', align: 'left', label: 'Estado Factura', field: 'estadoFactura', sortable: true },
        { name: 'archivoDrive', align: 'left', label: 'archivoDrive', field: 'archivoDrive', sortable: true, style: 'width: 130px; whiteSpace: normal' },
        { name: 'id', label: 'Id', align: 'left', field: 'id', sortable: true },
        { name: 'idCliente', align: 'left', label: 'idEntidad', field: 'idCliente', sortable: true },
        { name: 'user', align: 'left', label: 'user', field: 'user', sortable: true },
        { name: 'ts', align: 'left', label: 'ts', field: 'ts', sortable: true }
      ],
      pagination: { rowsPerPage: 0 },
      listaOpciones: [
        {
          name: 'accionesDrive',
          title: 'Acciones Drive',
          icon: 'cloud',
          function: '',
          children: [
            { name: 'cargarFacturas', title: 'Cargar Facturas', icon: 'backup', function: 'cargarFacturas', children: [] },
            { name: 'cargarFacturas', title: 'Enviar Facturas', icon: 'email', function: 'enviarFacturas', children: [] }
          ]
        }
      ]
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO']),
    ...mapState('entidades', ['entidadSelf', 'entidadAsesor']),
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    getRecords () {
      var objFilter = {}
      if (this.fromFacturasMain === undefined) {
        Object.assign(objFilter, { codEmpresa: this.user.codEmpresa, tipoObjeto: (this.value.tipoForm === 'ENTIDADES' ? 'E' : 'A'), idObjeto: this.value.id })
      } else Object.assign(objFilter, this.value) // viene de facturasMain
      return this.$axios.get('facturas/bd_facturas.php/findFacturasFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    addRecord () {
      var record = {
        codEmpresa: this.user.codEmpresa,
        tipoFactura: 'EMITIDA',
        idCliente: 0,
        archivoDrive: '',
        estadoFactura: 'PENDIENTE',
        comentarios: '',
        carpeta: '',
        nroFactura: '-1',
        fecha: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss'),
        base: 0,
        por_retencion: 0,
        retencion: 0,
        totalIva: 0,
        totalFactura: 0,
        user: this.user.user.email,
        ts: date.formatDate(new Date(), 'YYYY-MM-DD HH:mm:ss')
      }
      return this.$axios.post('facturas/bd_facturas.php/findFacturasFilter/', record)
        .then(response => {
          record.id = response.data.id
          return this.$axios.get(`facturas/bd_facturas.php/findFacturasFilter/${record.id}`)
            .then(response => {
              record = response.data[0]
              this.registrosSeleccionados.push(record)
              this.editRecord(record, record.id)
            })
            .catch(error => {
              this.$q.dialog({ title: 'Error', message: error })
            })
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
        return this.$axios.delete(`facturas/bd_facturas.php/findFacturasFilter/${id}`, JSON.stringify({ id: id }))
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
    editRecord (rowChanges, id) { // no lo uso aqui pero lod ejo como demo
      this.addTab(['facturasFormMain', 'Factura-' + rowChanges.id, rowChanges, rowChanges.id])
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    },
    ejecutarOpcion (opcion) {
      if (opcion.children.length === 0) {
        this[opcion.function](this.selectedRowID)
        this.$refs.menu1.hide()
      }
    },
    imprimir (selected) {
      var paramRecord = {
        id: selected.id,
        aDisco: 0
      }
      var formData = new FormData()
      for (var key in paramRecord) {
        formData.append(key, paramRecord[key])
      }
      this.$axios.post('facturas/pdf_invoice.php/', formData, { responseType: 'blob' })
        .then(function (response) {
          if (window.cordova === undefined) { // desktop
            const url = window.URL.createObjectURL(new Blob([response.data], { type: response.data.type }))
            const link = document.createElement('a')
            link.href = url
            link.target = '_blank'
            document.body.appendChild(link)
            // window.open('', 'view') // abre nueva ventana para que no sustituya a la actual
            link.click()
          } else { // estamos en un disp movil            console.log('hola3')
            const blobPdf = response.data // new Blob([response.data], { type: response.data.type })
            openBlobFile(selected.archivoDrive, blobPdf, response.data.type)
          }
        }).catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    duplicar (selected) {
      this.$axios.get('facturas/bd_facturas.php/copiarFactura', { params: { id: selected.id } })
        .then(response => {
          this.getRecords()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    enviarEmail (selected) {
      this.recordSendMail = {
        destino: (selected.emailEntidad === '' ? this.entidadSelf.email : selected.emailEntidad),
        destinoCopia: this.entidadSelf.email, // jvilata@
        asunto: 'Factura de ' + this.user.nomEmpresa + ' número: ' + selected.nroFactura,
        texto: 'Hola,<br>Le adjuntamos factura ' + selected.nroFactura + ' por los servicios prestados de la empresa:' +
          this.user.nomEmpresa + '<br>Atentamente,<br>' + this.entidadSelf.nombre + '<br>' +
          (this.entidadSelf.logo !== '' ? '<img src="http://vidawm.com/img/' + this.entidadSelf.logo + '"  width="100">' : ''),
        url: 'onedrive/downloadFactura.php?empresa=' + this.user.nomEmpresa + '&nombrePDF=' + selected.archivoDrive + '&carpeta=' + selected.carpeta
      }
      this.visibleSendMail = true
    },
    generarPago (selected) {
      this.$axios.get('facturas/bd_facturas.php/generarPagoCobroFactura', { params: { id: selected.id } })
        .then(response => {
          this.$q.dialog({ title: 'Confirmar', message: 'Se ha generado movimiento' })
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    cargarFacturas () {
      var host = this.$axios.defaults.baseURL // 'https://vidawm.com/privado/php/'
      var strUrl = host + 'onedrive/recorrerCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' +
          this.user.nomEmpresa + '&tipo=FACTURAS&carpeta=FACTURAS&estado='
      if (window.cordova === undefined) { // desktop
        /* const link = document.createElement('a')
        link.href = host + 'onedrive/recorrerCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' +
          this.user.nomEmpresa + '&tipo=FACTURAS&carpeta=FACTURAS&estado='
        link.target = '_blank'
        document.body.appendChild(link)
        link.click() */
        openURL(strUrl)
      } else { // dispositivo movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
    },
    enviarFacturas () {
      this.recordSendMail = {
        destino: this.entidadAsesor.email, // 'rus@prifiscal.es'
        destinoCopia: this.entidadSelf.email, // 'jvilata@edicom.es',
        asunto: 'Te adjunto facturas de ' + this.user.nomEmpresa,
        texto: 'Hola,<br>Le adjuntamos facturas de la empresa:' + this.user.nomEmpresa + ' en este enlace de OnDrive:%enlace%' +
          '<br>Atentamente,<br>' + this.entidadSelf.nombre + '<br>' +
          (this.entidadSelf.logo !== '' ? '<img src="http://vidawm.com/img/' + this.entidadSelf.logo + '"  width="100">' : ''),
        url: 'onedrive/moverElementosCarpeta.php?codEmpresa=' + this.user.codEmpresa + '&empresa=' + this.user.nomEmpresa +
          '&tipo=FACTURAS&carpeta=FACTURAS&estado='
      }
      this.visibleSendMail = true
    }
  },
  components: {
    sendMail: sendMail
  },
  mounted () {
    this.getRecords()
  }
}
</script>
<style lang="sass">
  .facturasGrid-header-table
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
