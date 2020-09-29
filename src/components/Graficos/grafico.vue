<template>
  <div>
   <div>
     <apexchart width="500" :options="chartOptions" :series="series"></apexchart>
     <div>
       <button @click="updateChart">Update!</button>
    </div>
   </div>
   <div class="example">
    <apexchart ref="donut" width="350" type="donut" :options="chartOptionsdonut" :series="seriesdonut"></apexchart>
    <div>
       <button @click="updateChart">Update!</button>
    </div>
  </div>
  </div>
</template>
<script>
// doc in: https://github.com/apexcharts/vue-apexcharts , https://apexcharts.com/
export default {
  data: function () {
    return {
      series: [{
        name: 'PRODUCT A',
        data: [44, 55, 41, 67, 22, 43]
      }, {
        name: 'PRODUCT B',
        data: [13, 23, 20, 8, 13, 27]
      }, {
        name: 'PRODUCT C',
        data: [11, 17, 15, 15, 21, 14]
      }, {
        name: 'PRODUCT D',
        data: [21, 7, 25, 13, 22, 8]
      }],
      chartOptions: {
        chart: {
          type: 'bar',
          height: 350,
          stacked: true,
          toolbar: {
            show: true
          },
          zoom: {
            enabled: true
          }
        },
        responsive: [{
          breakpoint: 480,
          options: {
            legend: {
              position: 'bottom',
              offsetX: -10,
              offsetY: 0
            }
          }
        }],
        plotOptions: {
          bar: {
            horizontal: false
          }
        },
        xaxis: {
          type: 'datetime',
          categories: ['01/01/2011 GMT', '01/02/2011 GMT', '01/03/2011 GMT', '01/04/2011 GMT',
            '01/05/2011 GMT', '01/06/2011 GMT'
          ]
        },
        legend: {
          position: 'right',
          offsetY: 40
        },
        fill: {
          opacity: 1
        }
      },
      chartOptionsdonut: {
        labels: ['Blue', 'Green', 'Yellow', 'Red'],
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
        }
      },
      seriesdonut: [11, 32, 45, 32]
    }
  },
  methods: {
    updateChart () {
      const max = 90
      const min = 20
      const newData = this.series[0].data.map(() => {
        return Math.floor(Math.random() * (max - min + 1)) + min
      })

      const colors = ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0']

      // Make sure to update the whole options config and not just a single property to allow the Vue watch catch the change.
      this.chartOptions = {
        colors: [colors[Math.floor(Math.random() * colors.length)]]
      }
      // In the same way, update the series option
      this.series = [{
        data: newData
      }]
    }
  }
}
</script>
