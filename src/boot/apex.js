/***************
 *  APEX-CHARTS
 * *
 * * hay que instalar el paquetes apex-chart
 * npm install --save apexcharts
 * npm install --save vue-apexcharts
 *
 */

import Vue from 'vue'
import VueApexCharts from 'vue-apexcharts'

Vue.use(VueApexCharts)

Vue.component('apexchart', VueApexCharts)
