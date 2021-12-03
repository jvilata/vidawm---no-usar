<template>
  <div>
  <div class="row">
    <div class="col-12 col-md" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Resumen de Patrimonio</div>
        </q-item-section>
      </q-item>
      <q-item >
        <q-item-section align="center">
          <dashboardResumenPatrimonio v-model="registrosResumenPatrimonio" :key="refreshRec"/>
        </q-item-section>
      </q-item>
    </div>
    <div class="col-12 col-md" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Resumen de Patrimonio</div>
        </q-item-section>
      </q-item>
      <q-item >
        <q-item-section align="center">
          <dashboardResumenPatrimonio v-model="registrosResumenPatrimonio2" :key="refreshRec1"/>
        </q-item-section>
      </q-item>
    </div>
  </div>
  <div class="row">
    <div class="col-12 col-md" >
      <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
        <q-item-section align="center">
          <div class="text-h6">Resumen de Patrimonio (Moneda)</div>
        </q-item-section>
      </q-item>
      <q-item >
        <q-item-section align="center">
          <dashboardResumenPatrimonio v-model="registrosResumenPatrimonioM" :key="refreshRec2"/>
        </q-item-section>
      </q-item>
    </div>
  </div>
</div>
</template>

<script>
import dashboardResumenPatrimonio from 'components/Dashboard/dashboardResumenPatrimonio.vue'
export default {
  props: ['value', 'keyValue'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      refreshRec: 0,
      refreshRec1: 0,
      refreshRec2: 0,
      registrosResumenPatrimonio: [],
      registrosResumenPatrimonio2: [],
      registrosResumenPatrimonioM: []
    }
  },
  methods: {
    getResumenPatrimonio (objFilter) {
      // donut resumen patrimonio
      this.$axios.get('movimientos/bd_movimientos.php/findcresumenPatrimonio/', { params: objFilter })
        .then(response => {
          this.registrosResumenPatrimonio = response.data
          this.refreshRec = this.keyValue + 1 // para que refresque el componente
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getResumenPatrimonio2 (objFilter) {
      // donut resumen patrimonio
      this.$axios.get('movimientos/bd_movimientos.php/findcresumenPatrimonio2/', { params: objFilter })
        .then(response => {
          this.registrosResumenPatrimonio2 = response.data
          this.refreshRec1 = this.keyValue + 1 // para que refresque el componente
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getResumenPatrimonioM (objFilter) {
      // donut resumen patrimonio moendas
      this.$axios.get('movimientos/bd_movimientos.php/findcresumenPatrimonioM/', { params: objFilter })
        .then(response => {
          this.registrosResumenPatrimonioM = response.data
          this.refreshRec2 = this.refreshRec2 + 1 // para que refresque el componente
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    this.getResumenPatrimonio(this.value) // carga datos donut resumen patrim
    this.getResumenPatrimonio2(this.value) // carga datos donut resumen patrim2
    this.getResumenPatrimonioM(this.value) // carga datos donut resumen patrimM
  },
  components: {
    dashboardResumenPatrimonio: dashboardResumenPatrimonio
  }
}
</script>
