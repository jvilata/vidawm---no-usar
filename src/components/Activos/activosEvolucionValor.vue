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
    getEvolucionPatrimonio () {
      var objFilter = { codEmpresa: this.user.codEmpresa, id: this.value.id }
      this.$axios.get('movimientos/bd_movimientos.php/findcvaloracionesActivo/', { params: objFilter })
        .then(response => {
          this.registrosEvolucionPatrimonio = response.data
          this.cargarDatosGrafico()
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    cargarDatosGrafico () {
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

      var objValor = {
        name: 'VALOR',
        type: 'line',
        data: []
      }
      var objCompras = {
        name: 'COMPRAS',
        type: 'column',
        data: []
      }
      var objVentas = {
        name: 'VENTAS',
        type: 'column',
        data: []
      }
      this.series.push(objValor)
      this.series.push(objCompras)
      this.series.push(objVentas)
      this.registrosEvolucionPatrimonio.forEach(row => {
        objValor.data.push(row.valor)
        objCompras.data.push(row.valor1)
        objVentas.data.push(row.valor2)
      })
    }
  },
  mounted () {
    this.getEvolucionPatrimonio() // carga datos  evoluc patrim
  }
}
</script>
