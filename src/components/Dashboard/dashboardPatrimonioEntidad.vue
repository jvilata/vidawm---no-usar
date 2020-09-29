<template>
  <div class="row">
    <div class="col" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Patrimonio por Entidad</div>
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
      chartOptions: {
        labels: [],
        chart: {
          stacked: false,
          events: {
            updated: function (chartContext, config) {
              Loading.hide()
            }
          }
        },
        legend: {
          show: true,
          position: 'top'
        },
        xaxis: {
          labels: {
            rotate: -90,
            style: {
              fontSize: '8px',
              fontFamily: 'Helvetica, Arial, sans-serif',
              fontWeight: 'normal',
              colors: ['dark']
            }
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
    getPatrimonioEntidad (objFilter) {
      Loading.show()
      // donut resumen patrimonio
      this.$axios.get('movimientos/bd_movimientos.php/findcevolucionPatrimonioEntidad/', { params: objFilter })
        .then(response => {
          this.registrosPatrimonioEntidad = response.data
          this.cargarDatosGrafico()
        })
        .catch(error => {
          Loading.hide()
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    cargarDatosGrafico () {
      // this.value // [{serie: "Euros", etiquetavalor: "CREDIT SUISSE", valor: "24253466.0000"},...]
      /* this.value.sort(function (a, b) { // ordeno el array por etiquetavalor
        return (a.etiquetavalor !== null ? a.etiquetavalor.localeCompare(b.etiquetavalor) : 0)
      }) */
      var etiqAnt = ''
      var arr = []
      this.registrosPatrimonioEntidad.forEach(row => {
        if (row.etiquetavalor !== etiqAnt) {
          arr.push((row.etiquetavalor === null ? '' : row.etiquetavalor)) // la cambiar de etiqueta guardo la anterior
          etiqAnt = row.etiquetavalor
        }
      })
      this.chartOptions = {
        labels: arr // es un array de strings
      }

      this.registrosPatrimonioEntidad.sort(function (a, b) { // ordeno el array por serie
        return a.serie.localeCompare(b.serie)
      })
      var serieAnt = ''
      var obj = {}
      this.registrosPatrimonioEntidad.forEach(row => {
        if (row.serie !== serieAnt) { // this.serie es un array de objetos {name:'serie', type:'line', data:[1,3,4,..]}
          obj = {
            name: row.serie,
            type: 'column',
            data: []
          }
          this.series.push(obj) // la cambiar de serie guardo la anterior
          serieAnt = row.serie
        }
        obj.data.push(row.valor)
      })
    }
  },
  mounted () {
    this.getPatrimonioEntidad(this.value) // carga datos  patrim entidad
  }
}
</script>
