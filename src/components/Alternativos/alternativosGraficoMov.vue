<template>

  <div class="row">
    <div class="col" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Evolución Valor</div>
        </q-item-section>
      </q-item>
      <q-item >
        <q-item-section align="center">
          <apexchart ref="evol1" height= "400px" width="1000px" type="line" :options="chartOptions" :series="series"></apexchart>
        </q-item-section>
      </q-item>
    </div>
  </div>
</template>
<script>
// doc in: https://github.com/apexcharts/vue-apexcharts , https://apexcharts.com/
import { numeralInstance } from 'boot/numeral.js'
import { mapState } from 'vuex'
export default {
  props: ['value', 'filter'],
  data: function () {
    return {
      registrosProyeccionAlternativos: [],
      chartOptions: {
        labels: [],
        colors: ['#21BA45', '#FF4560', '#008FFB'],
        chart: {
          stacked: true
        },
        legend: {
          show: true,
          position: 'top'
        },
        stroke: {
          width: 1,
          colors: ['#21BA45', '#FF4560', '#008FFB']
        },
        dataLabels: {
          enabled: true,
          enabledOnSeries: [0, 1, 2],
          formatter: function (val, opt) {
            return numeralInstance(parseFloat(val) / 1000).format('0,0') + 'k'
          },
          style: {
            fontSize: '10px',
            fontFamily: 'Helvetica, Arial, sans-serif',
            fontWeight: 'normal'
            // colors: ['dark']
          }
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return numeralInstance(parseFloat(val)).format('0,0')
            }
          }
        }
      },
      series: [] // { name:'nom serie', type: 'line', data: [] }
    }
  },
  computed: {
    ...mapState('login', ['user'])
  },
  methods: {
    getProyeccionAlternativos (filter) {
      var objFilter = Object.assign({}, filter)
      objFilter.idActivo = (objFilter.idActivo && objFilter.idActivo !== null ? objFilter.idActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.tipoActivo = (objFilter.tipoActivo && objFilter.tipoActivo !== null ? objFilter.tipoActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.estadoActivo = (objFilter.estadoActivo && objFilter.estadoActivo !== null ? objFilter.estadoActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.tipoProducto = (objFilter.tipoProducto && objFilter.tipoProducto !== null ? objFilter.tipoProducto.join() : null) // paso de array a concatenacion de strings (join)
      return this.$axios.get('movimientos/bd_alternativos.php/findcProyeccionAlternativos', { params: objFilter })
        .then(response => {
          this.registrosProyeccionAlternativos = response.data
          this.cargarDatosGrafico()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    cargarDatosGrafico () {
      var etiqAnt = ''
      var arr = []
      this.registrosProyeccionAlternativos.forEach(row => {
        if (row.ejercicio !== etiqAnt) {
          arr.push(row.ejercicio) // la cambiar de etiqueta guardo la anterior
          etiqAnt = row.ejercicio
        }
      })
      this.chartOptions = {
        labels: arr // es un array de strings
      }

      var objValor = {
        name: 'VALOR',
        type: 'line',
        data: []
      }
      var objCompras = {
        name: 'LLAMADAS CAP.',
        type: 'column',
        data: []
      }
      var objVentas = {
        name: 'DISTRIBUCIONES',
        type: 'column',
        data: []
      }
      this.series.push(objValor)
      this.series.push(objCompras)
      this.series.push(objVentas)
      var valAnt = 0.0
      this.registrosProyeccionAlternativos.forEach(row => {
        row.valoracion = parseFloat(row.valoracion)
        if (row.valoracion === 0) row.valoracion = valAnt
        row.comprometido = parseFloat(row.comprometido) + parseFloat(row.compra)
        row.distribucion = parseFloat(row.distribucion) + parseFloat(row.venta)
        objValor.data.push(row.valoracion)
        objCompras.data.push(-row.comprometido)
        objVentas.data.push(row.distribucion)
        valAnt = row.valoracion
      })
    }
  },
  mounted () {
    if (this.filter.codEmpresa !== undefined) this.getProyeccionAlternativos(this.filter) // carga datos  evoluc patrim
  }
}
</script>
