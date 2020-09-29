<template>
  <apexchart ref="donut" height="300px" type="donut" :options="chartOptionsdonut" :series="seriesdonut"></apexchart>
</template>
<script>
// doc in: https://github.com/apexcharts/vue-apexcharts , https://apexcharts.com/
import { numeralInstance } from 'boot/numeral.js'
export default {
  props: ['value'],
  data: function () {
    return {
      chartOptionsdonut: {
        labels: [],
        legend: {
          show: false
        },
        plotOptions: {
          pie: {
            donut: {
              size: '40%'
            },
            dataLabels: {
              offset: 20,
              minAngleToShowLabel: 10
            }
          }
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return numeralInstance(parseFloat(val)).format('0,0')
            }
          }
        },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 400,
          animateGradually: {
            enabled: true,
            delay: 350
          },
          dynamicAnimation: {
            enabled: true,
            speed: 550
          }
        },
        dataLabels: {
          dropShadow: {
            enabled: false
          },
          formatter: function (val, opt) {
            return opt.w.globals.labels[opt.seriesIndex] + ':  ' + numeralInstance(parseFloat(val)).format('0.00') + '%'
          },
          style: {
            fontSize: '9px',
            fontFamily: 'Helvetica, Arial, sans-serif',
            fontWeight: 'normal',
            colors: ['dark']
          }
        }
      },
      seriesdonut: []
    }
  },
  methods: {
  },
  mounted () {
    // this.value // [{serie: "ALTERN.R FIJA", etiquetavalor: "03/2020", valor: "733464.0000"},...]
    this.chartOptionsdonut = {
      labels: Array.from(this.value, row => row.serie)
    }
    this.seriesdonut = Array.from(this.value, row => parseFloat(row.valor))
  }
}
</script>
