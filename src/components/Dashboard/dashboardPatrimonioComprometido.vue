<template>
  <div class="row">
    <div class="col" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Patrimonio Comprometido</div>
        </q-item-section>
      </q-item>
      <q-item >
        <q-item-section align="center">
          <apexchart ref="evol1" height= "400px" width="1000px" type="bar" :options="chartOptions" :series="series"></apexchart>
        </q-item-section>
      </q-item>
    </div>
  </div>
</template>
<script>
// doc in: https://github.com/apexcharts/vue-apexcharts , https://apexcharts.com/
import { numeralInstance } from 'boot/numeral.js'
import { Loading } from 'quasar'
export default {
  props: ['value'],
  data: function () {
    return {
      registrosPatrimonioComprometido: [],
      chartOptions: {
        chart: {
          stacked: true,
          events: {
            updated: function (chartContext, config) {
              Loading.hide()
            }
          }
        },
        plotOptions: {
          bar: {
            horizontal: false
          }
        },
        dataLabels: {
          enabled: true,
          formatter: function (val, opt) {
            return numeralInstance(parseFloat(val) / 1000).format('0,0') + 'k'
          },
          style: {
            fontSize: '8px',
            fontFamily: 'Helvetica, Arial, sans-serif',
            fontWeight: 'normal',
            colors: ['dark']
          }
        },
        xaxis: {
          categories: []
        },
        legend: {
          show: false
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
  methods: {
    getPatrimonioComprometido (objFilter) {
      // donut resumen patrimonio
      Loading.show()
      this.$axios.get('movimientos/bd_movimientos.php/findcevolucionPatrimonioComprometido/', { params: objFilter })
        .then(response => {
          this.registrosPatrimonioComprometido = response.data
          this.cargarDatosGrafico()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
          Loading.hide()
        })
    },
    cargarDatosGrafico () {
      // this.value // [{serie: "ALANTRA - VEHICULO D", etiquetavalor: "03/2020", valor: "10000.0000"},...]
      this.registrosPatrimonioComprometido.sort(function (a, b) { // ordeno el array por etiquetavalor
        return (a.etiquetavalor !== null ? (a.etiquetavalor.substr(3, 4) + a.etiquetavalor.substr(0, 2)).localeCompare(b.etiquetavalor.substr(3, 4) + b.etiquetavalor.substr(0, 2)) : 0)
      })
      var etiqAnt = ''
      var arr = []
      this.registrosPatrimonioComprometido.forEach(row => {
        if (row.etiquetavalor !== etiqAnt) {
          arr.push((row.etiquetavalor === null ? '' : row.etiquetavalor)) // la cambiar de etiqueta guardo la anterior
          etiqAnt = row.etiquetavalor
        }
      })
      this.chartOptions = {
        // labels: arr // es un array de strings
        xaxis: {
          categories: arr
        }
      }

      this.registrosPatrimonioComprometido.sort(function (a, b) { // ordeno el array por serie + etiquetavalor (fecha 01/2020, 032/2020...)
        var serieA = a.serie + (a.etiquetavalor !== null ? (a.etiquetavalor.substr(3, 4) + a.etiquetavalor.substr(0, 2)) : 0)
        var serieB = b.serie + (b.etiquetavalor !== null ? (b.etiquetavalor.substr(3, 4) + b.etiquetavalor.substr(0, 2)) : 0)
        return serieA.localeCompare(serieB)
      })
      this.registrosPatrimonioComprometido.forEach(rowSer => {
        // para cada Serie (fondo) carga cada una de las fechas (categorias) o 0 si no existe valor
        var dataSer = []
        arr.forEach(rowCat => { // para cada fecha (categoria)
          if (rowCat === rowSer.etiquetavalor) { // tengo valor para esta serie - categoria
            dataSer.push(rowSer.valor)
          } else {
            dataSer.push(0) // si no hay valor pongo cero
          }
        })
        var obj = {
          name: rowSer.serie,
          // type: 'column',
          data: dataSer
        }
        this.series.push(obj)
      })
    }
  },
  mounted () {
    this.getPatrimonioComprometido(this.value)
  }
}
</script>
