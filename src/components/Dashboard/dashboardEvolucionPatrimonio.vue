<template>
  <div class="row">
    <div class="col" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Evolución de Patrimonio</div>
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
export default {
  props: ['value'],
  data: function () {
    return {
      registrosEvolucionPatrimonio: [],
      chartOptions: {
        labels: [],
        stacked: false,
        legend: {
          show: true,
          position: 'top'
        },
        dataLabels: {
          enabled: true,
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
        },
        annotations: {
          yaxis: [{
            y: 0,
            y2: 10000,
            borderColor: 'red',
            fillColor: 'red',
            opacity: 1
          }]
        }
      },
      series: [] // { name:'nom serie', type: 'line', data: [] }
    }
  },
  methods: {
    getEvolucionPatrimonio (objFilter) {
      // donut resumen patrimonio
      this.$axios.get('movimientos/bd_movimientos.php/findcevolucionPatrimonio/', { params: objFilter })
        .then(response => {
          this.registrosEvolucionPatrimonio = response.data
          this.cargarDatosGraficos()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    cargarDatosGraficos () {
      // this.registrosEvolucionPatrimonio // [{serie: "ALTERN.R FIJA", etiquetavalor: "03/2020", valor: "733464.0000"},...]
      this.registrosEvolucionPatrimonio.sort(function (a, b) { // ordeno el array por etiquetavalor
        return a.etiquetavalor.localeCompare(b.etiquetavalor)
      })
      var etiqAnt = ''
      var arr = []
      this.registrosEvolucionPatrimonio.forEach(row => {
        if (row.etiquetavalor !== etiqAnt) {
          arr.push(row.etiquetavalor) // la cambiar de etiqueta guardo la anterior
          etiqAnt = row.etiquetavalor
        }
      })
      this.chartOptions = {
        labels: arr // es un array de strings
      }

      this.registrosEvolucionPatrimonio.sort(function (a, b) { // ordeno el array por serie
        return a.serie.localeCompare(b.serie)
      })
      var serieAnt = ''
      var obj = {}
      this.registrosEvolucionPatrimonio.forEach(row => {
        if (row.serie !== serieAnt) { // this.serie es un array de objetos {name:'serie', type:'line', data:[1,3,4,..]}
          obj = {
            name: row.serie,
            type: 'line',
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
    this.getEvolucionPatrimonio(this.value) // carga datos  evoluc patrim
  }
}
</script>
