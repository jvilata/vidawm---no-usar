<template>
  <div>
  <q-btn dense label="+/- Columnas" color="primary" @click="ocultar=!ocultar"/>
  <q-item class="row center" >
    <!-- GRID. en row-key ponemos la columna del json que sea la id unica de la fila -->
    <q-table
      class="col dashboardGridPlanif-header-table"
      dense
      :pagination.sync="pagination"
      :rows-per-page-options="[0]"
      :virtual-scroll-sticky-size-start="48"
      row-key="id"
      :data="registrosSeleccionados"
      :columns="columns"
      table-style="max-height: 70vh; max-width: 93vw"
      hide-bottom
    >

      <template v-slot:header="props">
        <!-- CABECERA DE LA TABLA -->
        <q-tr :props="props">
          <q-th
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div v-if="!ocultar || ocultar && !['aInvertir', 'distribuido', 'comprometido', 'comprometidotot', 'realcomprometido', 'pprevisto', 'pajuste', 'importeAjuste'].includes(col.name.substring(0,col.name.length-2))">
              {{ col.label }}
            </div>
          </q-th>
        </q-tr>
      </template>

      <template v-slot:body="props">
        <q-tr :props="props" :key="`m_${props.row.id}`" @mouseover="rowId=`m_${props.row.id}`">
          <q-td
            v-for="col in props.cols"
            :key="col.name"
            :props="props"
          >
            <div :style="col.style"
              v-if="!ocultar || ocultar && !['aInvertir', 'distribuido', 'comprometido', 'comprometidotot', 'realcomprometido', 'pprevisto', 'pajuste', 'importeAjuste'].includes(col.name.substring(0,col.name.length-2))">
              {{ col.value }}
            </div>
          </q-td>
        </q-tr>
      </template>

      <template v-slot:bottom-row>
        <!-- BOTTOM-ROW DE LA TABLA -->
        <q-tr >
          <q-th
            v-for="col in columns"
            :key="col.name"
            :align="col.align"
          >
            <div v-if="!['tipoActivo'].includes(col.name) && (!ocultar || ocultar && !['aInvertir', 'distribuido', 'comprometido', 'comprometidotot', 'realcomprometido', 'pprevisto', 'pajuste', 'importeAjuste'].includes(col.name.substring(0,col.name.length-2)))">
              {{ registrosSeleccionados.reduce((a, b) => a + (parseFloat(b[col.name]) || 0), 0) | numeralFormat }}
            </div>
          </q-th>
        </q-tr>
      </template>

    </q-table>
  </q-item>
  </div>
</template>

<script>
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      rowId: '',
      ocultar: true,
      registrosSeleccionados: [],
      proyCompRFija: [],
      proyCompCapRiesgo: [],
      proyCompInmuebles: [],
      proyCompParticipacion: [],
      registrosPanelDatos: [],
      totRealCompY0: 0,
      totPatrimonioY0: 0,
      totRealCompY1: 0,
      totPatrimonioY1: 0,
      totRealCompY2: 0,
      totPatrimonioY2: 0,
      totRealCompY3: 0,
      totPatrimonioY3: 0,
      totRealCompY4: 0,
      totPatrimonioY4: 0,
      totRealCompY5: 0,
      totPatrimonioY5: 0,
      columns: [
        { name: 'tipoActivo', required: true, label: 'Tipo Activo', align: 'right', field: 'tipoActivo' },
        { name: 'pTeorico', required: true, label: '%Part.Ideal', align: 'right', field: 'pTeorico' },
        { name: 'dividendos', required: true, label: 'Dividendos', align: 'right', field: 'dividendos', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'patrimonio', required: true, label: 'patrimonioY-1', align: 'right', field: 'patrimonio', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'preal', required: true, label: '%RealY-1', align: 'right', field: 'preal', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometidoY0', required: true, label: 'comprometidoY0', align: 'right', field: 'comprometidoY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'distribuidoY0', required: true, label: 'distribuidoY0', align: 'right', field: 'distribuidoY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometidototY0', required: true, label: 'comprometidototY0', align: 'right', field: 'comprometidototY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'realcomprometidoY0', required: true, label: 'realcomprometidoY0', align: 'right', field: 'realcomprometidoY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pprevistoY0', required: true, label: '%PrevistoY0', align: 'right', field: 'pprevistoY0', format: val => parseFloat(val).toFixed(2) },
        { name: 'pajusteY0', required: true, label: '%AjusteY0', align: 'right', field: 'pajusteY0', format: val => parseFloat(val).toFixed(2) },
        { name: 'importeAjusteY0', required: true, label: 'Imp.Ajuste0', align: 'right', field: 'importeAjusteY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'aInvertirY0', required: true, label: 'aInvertirY0', align: 'right', field: 'aInvertirY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'patrimonioY0', required: true, label: 'patrimonioY0', align: 'right', field: 'patrimonioY0', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'prealY0', required: true, label: '%RealY0', align: 'right', field: 'prealY0', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometidoY1', required: true, label: 'comprometidoY1', align: 'right', field: 'comprometidoY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'distribuidoY1', required: true, label: 'distribuidoY1', align: 'right', field: 'distribuidoY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometidototY1', required: true, label: 'comprometidototY1', align: 'right', field: 'comprometidototY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'realcomprometidoY1', required: true, label: 'realcomprometidoY1', align: 'right', field: 'realcomprometidoY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pprevistoY1', required: true, label: '%PrevistoY1', align: 'right', field: 'pprevistoY1', format: val => parseFloat(val).toFixed(2) },
        { name: 'pajusteY1', required: true, label: '%AjusteY1', align: 'right', field: 'pajusteY1', format: val => parseFloat(val).toFixed(2) },
        { name: 'importeAjusteY1', required: true, label: 'Imp.AjusteY1', align: 'right', field: 'importeAjusteY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'aInvertirY1', required: true, label: 'aInvertirY1', align: 'right', field: 'aInvertirY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'patrimonioY1', required: true, label: 'patrimonioY1', align: 'right', field: 'patrimonioY1', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'prealY1', required: true, label: '%RealY1', align: 'right', field: 'prealY1', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometidoY2', required: true, label: 'comprometidoY2', align: 'right', field: 'comprometidoY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'distribuidoY2', required: true, label: 'distribuidoY2', align: 'right', field: 'distribuidoY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometidototY2', required: true, label: 'comprometidototY2', align: 'right', field: 'comprometidototY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'realcomprometidoY2', required: true, label: 'realcomprometidoY2', align: 'right', field: 'realcomprometidoY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pprevistoY2', required: true, label: '%PrevistoY2', align: 'right', field: 'pprevistoY2', format: val => parseFloat(val).toFixed(2) },
        { name: 'pajusteY2', required: true, label: '%AjusteY2', align: 'right', field: 'pajusteY2', format: val => parseFloat(val).toFixed(2) },
        { name: 'importeAjusteY2', required: true, label: 'Imp.AjusteY2', align: 'right', field: 'importeAjusteY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'aInvertirY2', required: true, label: 'aInvertirY2', align: 'right', field: 'aInvertirY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'patrimonioY2', required: true, label: 'patrimonioY2', align: 'right', field: 'patrimonioY2', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'prealY2', required: true, label: '%RealY2', align: 'right', field: 'prealY2', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometidoY3', required: true, label: 'comprometidoY3', align: 'right', field: 'comprometidoY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'distribuidoY3', required: true, label: 'distribuidoY3', align: 'right', field: 'distribuidoY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometidototY3', required: true, label: 'comprometidototY3', align: 'right', field: 'comprometidototY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'realcomprometidoY3', required: true, label: 'realcomprometidoY3', align: 'right', field: 'realcomprometidoY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pprevistoY3', required: true, label: '%PrevistoY3', align: 'right', field: 'pprevistoY3', format: val => parseFloat(val).toFixed(2) },
        { name: 'pajusteY3', required: true, label: '%AjusteY3', align: 'right', field: 'pajusteY3', format: val => parseFloat(val).toFixed(2) },
        { name: 'importeAjusteY3', required: true, label: 'Imp.AjusteY3', align: 'right', field: 'importeAjusteY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'aInvertirY3', required: true, label: 'aInvertirY3', align: 'right', field: 'aInvertirY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'patrimonioY3', required: true, label: 'patrimonioY3', align: 'right', field: 'patrimonioY3', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'prealY3', required: true, label: '%RealY3', align: 'right', field: 'prealY3', format: val => parseFloat(val).toFixed(2) },
        { name: 'comprometidoY4', required: true, label: 'comprometidoY4', align: 'right', field: 'comprometidoY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'distribuidoY4', required: true, label: 'distribuidoY4', align: 'right', field: 'distribuidoY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'comprometidototY4', required: true, label: 'comprometidototY4', align: 'right', field: 'comprometidototY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'realcomprometidoY4', required: true, label: 'realcomprometidoY4', align: 'right', field: 'realcomprometidoY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'pprevistoY4', required: true, label: '%PrevistoY4', align: 'right', field: 'pprevistoY4', format: val => parseFloat(val).toFixed(2) },
        { name: 'pajusteY4', required: true, label: '%AjusteY4', align: 'right', field: 'pajusteY4', format: val => parseFloat(val).toFixed(2) },
        { name: 'importeAjusteY4', required: true, label: 'Imp.AjusteY4', align: 'right', field: 'importeAjusteY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'aInvertirY4', required: true, label: 'aInvertirY4', align: 'right', field: 'aInvertirY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'patrimonioY4', required: true, label: 'patrimonioY4', align: 'right', field: 'patrimonioY4', format: val => this.$numeral(parseFloat(val)).format('0,0.00') },
        { name: 'prealY4', required: true, label: '%RealY4', align: 'right', field: 'prealY4', format: val => parseFloat(val).toFixed(2) }
      ],
      pagination: { rowsPerPage: 0 }
    }
  },
  methods: {
    acumulaTotales (ejercicioY0, element, rfija) {
      element.comprometidototY0 += parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
      element.comprometidototY1 += parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
      element.comprometidototY2 += parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
      element.comprometidototY3 += parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
      element.comprometidototY4 += parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
      element.comprometidototY5 += parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
      if (parseInt(rfija.ejercicio) === (parseInt(ejercicioY0))) {
        element.comprometidoY0 = parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
        element.distribuidoY0 = parseFloat(rfija.distribucion)
        element.comprometidototY0 -= 0
      } else if (parseInt(rfija.ejercicio) === (parseInt(ejercicioY0) + 1)) {
        element.comprometidoY1 = parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
        element.distribuidoY1 = parseFloat(rfija.distribucion)
        element.comprometidototY1 -= (element.comprometidoY0)
      } else if (parseInt(rfija.ejercicio) === (parseInt(ejercicioY0) + 2)) {
        element.comprometidoY2 = parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
        element.distribuidoY2 = parseFloat(rfija.distribucion)
        element.comprometidototY2 -= (element.comprometidoY0 + element.comprometidoY1)
      } else if (parseInt(rfija.ejercicio) === (parseInt(ejercicioY0) + 3)) {
        element.comprometidoY3 = parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
        element.distribuidoY3 = parseFloat(rfija.distribucion)
        element.comprometidototY3 -= (element.comprometidoY0 + element.comprometidoY1 + element.comprometidoY2)
      } else if (parseInt(rfija.ejercicio) === (parseInt(ejercicioY0) + 4)) {
        element.comprometidoY4 = parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
        element.distribuidoY4 = parseFloat(rfija.distribucion)
        element.comprometidototY4 -= (element.comprometidoY0 + element.comprometidoY1 + element.comprometidoY2 + element.comprometidoY3)
      } else if (parseInt(rfija.ejercicio) === (parseInt(ejercicioY0) + 5)) {
        element.comprometidoY5 = parseFloat(rfija.comprometido) + parseFloat(rfija.compra)
        element.distribuidoY5 = parseFloat(rfija.distribucion)
        element.comprometidototY5 -= (element.comprometidoY0 + element.comprometidoY1 + element.comprometidoY2 + element.comprometidoY3 + element.comprometidoY4)
      }
    },
    getActivosInversion (objFilter) {
      var ejercicioY0 = objFilter.mes.substring(3)
      var obj1 = {}
      Object.assign(obj1, objFilter)
      obj1.mes = '01' + objFilter.mes.substring(2)
      // grid por tipos de activos inversion
      this.$axios.get('activos/bd_activos.php/cActivosInversion/', { params: obj1 })
        .then(response => {
          this.registrosSeleccionados = response.data
          Object.assign(obj1, {
            anyoDesde: ejercicioY0,
            estadoActivo: '1,4',
            computa: 1,
            tipoActivo: 'ALTERN.R FIJA'
          })
          this.$axios.get('https://vidawm.com/privado/php/movimientos/bd_alternativos.php/findcProyeccionAlternativos', { params: obj1 })
            .then(response => {
              this.proyCompRFija = response.data
              obj1.tipoActivo = 'CAP.RIESGO'
              this.$axios.get('https://vidawm.com/privado/php/movimientos/bd_alternativos.php/findcProyeccionAlternativos', { params: obj1 })
                .then(response => {
                  this.proyCompCapRiesgo = response.data
                  obj1.tipoActivo = 'INM.EN CONSTR'
                  this.$axios.get('https://vidawm.com/privado/php/movimientos/bd_alternativos.php/findcProyeccionAlternativos', { params: obj1 })
                    .then(response => {
                      this.proyCompInmuebles = response.data
                      obj1.tipoActivo = 'PARTICIPACION'
                      this.$axios.get('https://vidawm.com/privado/php/movimientos/bd_alternativos.php/findcProyeccionAlternativos', { params: obj1 })
                        .then(response => {
                          this.proyCompParticipacion = response.data
                          // PONER CODIGO AQUI -----------------
                          this.registrosSeleccionados.forEach(element => {
                            element.comprometidototY0 = 0
                            element.comprometidototY1 = 0
                            element.comprometidototY2 = 0
                            element.comprometidototY3 = 0
                            element.comprometidototY4 = 0
                            element.comprometidototY5 = 0
                            if (element.tipoActivo === 'ALTERN.R FIJA') {
                              this.proyCompRFija.forEach(rfija => {
                                this.acumulaTotales(ejercicioY0, element, rfija)
                              })
                            } else if (element.tipoActivo === 'CAP.RIESGO') {
                              this.proyCompCapRiesgo.forEach(rfija => {
                                this.acumulaTotales(ejercicioY0, element, rfija)
                              })
                            } else if (element.tipoActivo === 'INM.EN CONSTR') {
                              this.proyCompInmuebles.forEach(rfija => {
                                this.acumulaTotales(ejercicioY0, element, rfija)
                              })
                            } else if (element.tipoActivo === 'PARTICIPACION') {
                              this.proyCompParticipacion.forEach(rfija => {
                                this.acumulaTotales(ejercicioY0, element, rfija)
                              })
                            }
                            element.realcomprometidoY0 = parseFloat(element.patrimonio) + element.comprometidototY0
                            this.totRealCompY0 += element.realcomprometidoY0
                          })
                          // ejercicio Y0
                          const cCompAltFija = 0.20 // 20% anual durante 5 años
                          const cCompAltCRiesgo = 0.20
                          const cDistAltRFija = 0.25 // % distribucion del 0.80 restante anterior a R.Fija
                          const cDistAltRVble = 0.55 // % distribucion del 0.80 restante anterior a R.Vble
                          var altFija = 0
                          var altCapRiesgo = 0
                          this.registrosSeleccionados.forEach(element => {
                            element.pprevistoY0 = element.realcomprometidoY0 * 100 / this.totRealCompY0
                            element.pajusteY0 = parseFloat(element.pTeorico) - element.pprevistoY0
                            element.importeAjusteY0 = this.totRealCompY0 * element.pajusteY0 / 100
                            if (['ALTERN.R FIJA'].includes(element.tipoActivo)) {
                              altFija = parseFloat(element.dividendos) + element.importeAjusteY0
                              element.aInvertirY0 = cCompAltFija * altFija
                              element.patrimonioY0 = parseFloat(element.patrimonio) + (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                cCompAltFija * cDistAltRFija
                              element.comprometidototY1 = element.comprometidototY0 - (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                (1 - cCompAltFija) * altFija
                            } else if (['CAP.RIESGO'].includes(element.tipoActivo)) {
                              altCapRiesgo = parseFloat(element.dividendos) + element.importeAjusteY0
                              element.aInvertirY0 = cCompAltCRiesgo * altCapRiesgo
                              element.patrimonioY0 = parseFloat(element.patrimonio) + (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                cCompAltCRiesgo * altCapRiesgo
                              element.comprometidototY1 = element.comprometidototY0 - (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                (1 - cCompAltCRiesgo) * altCapRiesgo
                            } else if (['RENTA FIJA'].includes(element.tipoActivo)) {
                              element.aInvertirY0 = cDistAltRFija * (altFija + altCapRiesgo)
                              element.patrimonioY0 = parseFloat(element.patrimonio) + (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                cDistAltRFija * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY0
                            } else if (['RENTA VBLE'].includes(element.tipoActivo)) {
                              element.aInvertirY0 = cDistAltRVble * (altFija + altCapRiesgo)
                              element.patrimonioY0 = parseFloat(element.patrimonio) + (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                cDistAltRVble * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY0
                            } else {
                              element.aInvertirY0 = 0
                              element.patrimonioY0 = parseFloat(element.patrimonio) + (element.comprometidoY0 === undefined ? 0 : element.comprometidoY0) +
                                parseFloat(element.dividendos) + element.importeAjusteY0
                            }
                            this.totPatrimonioY0 += element.patrimonioY0
                            element.realcomprometidoY1 = element.patrimonioY0 + element.comprometidototY1
                            this.totRealCompY1 += element.realcomprometidoY1
                          })

                          // ejercicio Y1
                          altFija = 0
                          altCapRiesgo = 0
                          this.registrosSeleccionados.forEach(element => {
                            element.pprevistoY1 = element.realcomprometidoY1 * 100 / this.totRealCompY1
                            element.pajusteY1 = parseFloat(element.pTeorico) - element.pprevistoY1
                            element.importeAjusteY1 = this.totRealCompY1 * element.pajusteY1 / 100
                            if (['ALTERN.R FIJA'].includes(element.tipoActivo)) {
                              element.comprometidoY1 = element.comprometidoY1 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0)
                              altFija = parseFloat(element.dividendos) + element.importeAjusteY1
                              element.aInvertirY1 = cCompAltFija * altFija
                              element.patrimonioY1 = parseFloat(element.patrimonioY0) + (element.comprometidoY1 === undefined ? 0 : element.comprometidoY1) +
                                cCompAltFija * altFija
                              element.comprometidototY2 = element.comprometidototY1 - element.comprometidoY1 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY1) // 0.8 * (div + ajuste)
                            } else if (['CAP.RIESGO'].includes(element.tipoActivo)) {
                              element.comprometidoY1 = element.comprometidoY1 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0)
                              altCapRiesgo = parseFloat(element.dividendos) + element.importeAjusteY1
                              element.aInvertirY1 = cCompAltCRiesgo * altCapRiesgo
                              element.patrimonioY1 = parseFloat(element.patrimonioY0) + (element.comprometidoY1 === undefined ? 0 : element.comprometidoY1) +
                                cCompAltFija * altCapRiesgo
                              element.comprometidototY2 = element.comprometidototY1 - element.comprometidoY1 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY1) // 0.8 * (div + ajuste)
                            } else if (['RENTA FIJA'].includes(element.tipoActivo)) {
                              element.aInvertirY1 = cDistAltRFija * (altFija + altCapRiesgo)
                              element.patrimonioY1 = parseFloat(element.patrimonioY0) + (element.comprometidoY1 === undefined ? 0 : element.comprometidoY1) +
                                cDistAltRFija * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY1
                            } else if (['RENTA VBLE'].includes(element.tipoActivo)) {
                              element.aInvertirY1 = cDistAltRVble * (altFija + altCapRiesgo)
                              element.patrimonioY1 = parseFloat(element.patrimonioY0) + (element.comprometidoY1 === undefined ? 0 : element.comprometidoY1) +
                                cDistAltRVble * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY1
                            } else {
                              element.aInvertirY1 = 0
                              element.patrimonioY1 = parseFloat(element.patrimonioY0) + (element.comprometidoY1 === undefined ? 0 : element.comprometidoY1) +
                                parseFloat(element.dividendos) + element.importeAjusteY1
                            }
                            this.totPatrimonioY1 += element.patrimonioY1
                            element.realcomprometidoY2 = element.patrimonioY1 + element.comprometidototY2
                            this.totRealCompY2 += element.realcomprometidoY2
                          })

                          // ejercicio Y2
                          altFija = 0
                          altCapRiesgo = 0
                          this.registrosSeleccionados.forEach(element => {
                            element.pprevistoY2 = element.realcomprometidoY2 * 100 / this.totRealCompY2
                            element.pajusteY2 = parseFloat(element.pTeorico) - element.pprevistoY2
                            element.importeAjusteY2 = this.totRealCompY2 * element.pajusteY2 / 100
                            if (['ALTERN.R FIJA'].includes(element.tipoActivo)) {
                              element.comprometidoY2 = element.comprometidoY2 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY1)
                              altFija = parseFloat(element.dividendos) + element.importeAjusteY2
                              element.aInvertirY2 = cCompAltFija * altFija
                              element.patrimonioY2 = parseFloat(element.patrimonioY1) +
                                (element.comprometidoY2 === undefined ? 0 : element.comprometidoY2) +
                                cCompAltFija * altFija
                              element.comprometidototY3 = element.comprometidototY2 - element.comprometidoY2 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY2) // 0.8 * (div + ajuste)
                            } else if (['CAP.RIESGO'].includes(element.tipoActivo)) {
                              element.comprometidoY2 = element.comprometidoY2 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY1)
                              altCapRiesgo = parseFloat(element.dividendos) + element.importeAjusteY2
                              element.aInvertirY2 = cCompAltCRiesgo * altCapRiesgo
                              element.patrimonioY2 = parseFloat(element.patrimonioY1) +
                                (element.comprometidoY2 === undefined ? 0 : element.comprometidoY2) +
                                cCompAltFija * altCapRiesgo
                              element.comprometidototY3 = element.comprometidototY2 - element.comprometidoY2 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY2) // 0.8 * (div + ajuste)
                            } else if (['RENTA FIJA'].includes(element.tipoActivo)) {
                              element.aInvertirY2 = cDistAltRFija * (altFija + altCapRiesgo)
                              element.patrimonioY2 = parseFloat(element.patrimonioY1) +
                                (element.comprometidoY2 === undefined ? 0 : element.comprometidoY2) +
                                cDistAltRFija * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY2
                            } else if (['RENTA VBLE'].includes(element.tipoActivo)) {
                              element.aInvertirY2 = cDistAltRVble * (altFija + altCapRiesgo)
                              element.patrimonioY2 = parseFloat(element.patrimonioY1) +
                                (element.comprometidoY2 === undefined ? 0 : element.comprometidoY2) +
                                cDistAltRVble * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY2
                            } else {
                              element.aInvertirY2 = 0
                              element.patrimonioY2 = parseFloat(element.patrimonioY1) +
                                (element.comprometidoY2 === undefined ? 0 : element.comprometidoY2) +
                                parseFloat(element.dividendos) + element.importeAjusteY2
                            }
                            this.totPatrimonioY2 += element.patrimonioY2
                            element.realcomprometidoY3 = element.patrimonioY2 + element.comprometidototY3
                            this.totRealCompY3 += element.realcomprometidoY3
                          })

                          // ejercicio Y3
                          altFija = 0
                          altCapRiesgo = 0
                          this.registrosSeleccionados.forEach(element => {
                            element.pprevistoY3 = element.realcomprometidoY3 * 100 / this.totRealCompY3
                            element.pajusteY3 = parseFloat(element.pTeorico) - element.pprevistoY3
                            element.importeAjusteY3 = this.totRealCompY3 * element.pajusteY3 / 100
                            if (['ALTERN.R FIJA'].includes(element.tipoActivo)) {
                              element.comprometidoY3 = element.comprometidoY3 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY1) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY2)
                              altFija = parseFloat(element.dividendos) + element.importeAjusteY3
                              element.aInvertirY3 = cCompAltFija * altFija
                              element.patrimonioY3 = parseFloat(element.patrimonioY2) +
                                (element.comprometidoY3 === undefined ? 0 : element.comprometidoY3) +
                                cCompAltFija * altFija
                              element.comprometidototY4 = element.comprometidototY3 - element.comprometidoY3 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY3) // 0.8 * (div + ajuste)
                            } else if (['CAP.RIESGO'].includes(element.tipoActivo)) {
                              element.comprometidoY3 = element.comprometidoY3 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY1) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY2)
                              altCapRiesgo = parseFloat(element.dividendos) + element.importeAjusteY3
                              element.aInvertirY3 = cCompAltCRiesgo * altCapRiesgo
                              element.patrimonioY3 = parseFloat(element.patrimonioY2) +
                                (element.comprometidoY3 === undefined ? 0 : element.comprometidoY3) +
                                cCompAltFija * altCapRiesgo
                              element.comprometidototY4 = element.comprometidototY3 - element.comprometidoY3 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY3) // 0.8 * (div + ajuste)
                            } else if (['RENTA FIJA'].includes(element.tipoActivo)) {
                              element.aInvertirY3 = cDistAltRFija * (altFija + altCapRiesgo)
                              element.patrimonioY3 = parseFloat(element.patrimonioY2) +
                                (element.comprometidoY3 === undefined ? 0 : element.comprometidoY3) +
                                cDistAltRFija * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY3
                            } else if (['RENTA VBLE'].includes(element.tipoActivo)) {
                              element.aInvertirY3 = cDistAltRVble * (altFija + altCapRiesgo)
                              element.patrimonioY3 = parseFloat(element.patrimonioY2) +
                                (element.comprometidoY3 === undefined ? 0 : element.comprometidoY3) +
                                cDistAltRVble * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY3
                            } else {
                              element.aInvertirY3 = 0
                              element.patrimonioY3 = parseFloat(element.patrimonioY2) +
                                (element.comprometidoY3 === undefined ? 0 : element.comprometidoY3) +
                                parseFloat(element.dividendos) + element.importeAjusteY3
                            }
                            this.totPatrimonioY3 += element.patrimonioY3
                            element.realcomprometidoY4 = element.patrimonioY3 + element.comprometidototY4
                            this.totRealCompY4 += element.realcomprometidoY4
                          })

                          // ejercicio Y4
                          altFija = 0
                          altCapRiesgo = 0
                          this.registrosSeleccionados.forEach(element => {
                            element.pprevistoY4 = element.realcomprometidoY4 * 100 / this.totRealCompY4
                            element.pajusteY4 = parseFloat(element.pTeorico) - element.pprevistoY4
                            element.importeAjusteY4 = this.totRealCompY4 * element.pajusteY4 / 100
                            if (['ALTERN.R FIJA'].includes(element.tipoActivo)) {
                              element.comprometidoY4 = element.comprometidoY4 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY1) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY2) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY3)
                              altFija = parseFloat(element.dividendos) + element.importeAjusteY4
                              element.aInvertirY4 = cCompAltFija * altFija
                              element.patrimonioY4 = parseFloat(element.patrimonioY3) +
                                (element.comprometidoY4 === undefined ? 0 : element.comprometidoY4) +
                                cCompAltFija * altFija
                              element.comprometidototY5 = element.comprometidototY4 - element.comprometidoY4 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY4) // 0.8 * (div + ajuste)
                            } else if (['CAP.RIESGO'].includes(element.tipoActivo)) {
                              element.comprometidoY4 = element.comprometidoY4 +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY0) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY1) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY2) +
                                cCompAltFija * (parseFloat(element.dividendos) + element.importeAjusteY3)
                              altCapRiesgo = parseFloat(element.dividendos) + element.importeAjusteY4
                              element.aInvertirY4 = cCompAltCRiesgo * altCapRiesgo
                              element.patrimonioY4 = parseFloat(element.patrimonioY3) +
                                (element.comprometidoY4 === undefined ? 0 : element.comprometidoY4) +
                                cCompAltFija * altCapRiesgo
                              element.comprometidototY5 = element.comprometidototY4 - element.comprometidoY4 +
                                (1 - cCompAltFija) * (parseFloat(element.dividendos) + element.importeAjusteY4) // 0.8 * (div + ajuste)
                            } else if (['RENTA FIJA'].includes(element.tipoActivo)) {
                              element.aInvertirY4 = cDistAltRFija * (altFija + altCapRiesgo)
                              element.patrimonioY4 = parseFloat(element.patrimonioY3) +
                                (element.comprometidoY4 === undefined ? 0 : element.comprometidoY4) +
                                cDistAltRFija * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY4
                            } else if (['RENTA VBLE'].includes(element.tipoActivo)) {
                              element.aInvertirY4 = cDistAltRVble * (altFija + altCapRiesgo)
                              element.patrimonioY4 = parseFloat(element.patrimonioY3) +
                                (element.comprometidoY4 === undefined ? 0 : element.comprometidoY4) +
                                cDistAltRVble * (altFija + altCapRiesgo) +
                                parseFloat(element.dividendos) + element.importeAjusteY4
                            } else {
                              element.aInvertirY4 = 0
                              element.patrimonioY4 = parseFloat(element.patrimonioY3) +
                                (element.comprometidoY4 === undefined ? 0 : element.comprometidoY4) +
                                parseFloat(element.dividendos) + element.importeAjusteY4
                            }
                            this.totPatrimonioY4 += element.patrimonioY4
                            element.realcomprometidoY5 = element.patrimonioY4 + element.comprometidototY5
                            this.totRealCompY5 += element.realcomprometidoY5
                          })

                          this.registrosSeleccionados.forEach(element => {
                            element.prealY0 = element.patrimonioY0 * 100 / this.totPatrimonioY0
                            element.prealY1 = element.patrimonioY1 * 100 / this.totPatrimonioY1
                            element.prealY2 = element.patrimonioY2 * 100 / this.totPatrimonioY2
                            element.prealY3 = element.patrimonioY3 * 100 / this.totPatrimonioY3
                            element.prealY4 = element.patrimonioY4 * 100 / this.totPatrimonioY4
                          })
                          var tmp = this.registrosSeleccionados
                          this.registrosSeleccionados = []
                          this.registrosSeleccionados = tmp
                        })
                    })
                })
            })
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getPanelDatos (objFilter) { // lo duplico de dashboardPanelDatos, me hace falta para los totales del grid
      var obj1 = {}
      Object.assign(obj1, objFilter)
      obj1.mes = '01' + objFilter.mes.substring(2)
      // cards resumen de rentabilidad y patrimonio actual
      this.$axios.get('movimientos/bd_movimientos.php/findcpanelDatos/', { params: obj1 })
        .then(response => {
          this.registrosPanelDatos = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    mostrarDatosPieTabla () {
      return this.registrosSeleccionados.length + ' Filas'
    }
  },
  mounted () {
    this.getActivosInversion(this.value) // carga grid de activos Inversion
    this.getPanelDatos(this.value) // carga panel de datos, me hace falta para totales grid
  }
}
</script>
<style lang="sass">
  .dashboardGridPlanif-header-table
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
