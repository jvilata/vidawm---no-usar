<template>
  <div style="height: calc(100vh - 300px);" class="q-pa-md row items-start q-gutter-md" >
    <q-card class="col-6 col-md my-card">
      <q-card-section align="center">
        <div class="text-subtitle-2 text-grey-8 text-weight-light">Patrim. Neto Enero</div>
        <div class="text-h5 text-green-7 text-weight-light">{{ parseFloat(registrosPanelDatos.patrimonioNetoInicial) | numeralFormat('0,0') }}€</div>
      </q-card-section>
    </q-card>
    <q-card class="col-6 col-md my-card bg-green-1">
      <q-card-section align="center">
        <div class="text-subtitle-2 text-grey-8 text-weight-bold">Patrim.Actual Neto</div>
        <div class="text-h5 text-green-7 text-weight-bold">{{ parseFloat(registrosPanelDatos.patrimonioNetoMes) | numeralFormat('0,0') }}€</div>
      </q-card-section>
    </q-card>
    <q-card class="col-6 col-md my-card">
      <q-card-section align="center">
        <div class="text-subtitle-2 text-grey-8 text-weight-light">%Rent.Anual Esperada</div>
        <div class="text-h5 text-green-7 text-weight-light">{{ parseFloat(registrosPanelDatos.rentabilidadEsperadaInicial) | numeralFormat('0.00') }}%</div>
      </q-card-section>
    </q-card>
    <q-card class="col-6 col-md my-card bg-green-1">
      <q-card-section align="center">
        <div class="text-subtitle-2 text-grey-8 text-weight-bold">%Rentab.Actual</div>
        <div class="text-h5 text-green-7 text-weight-bold">{{ parseFloat(registrosPanelDatos.rentabilidadReal) | numeralFormat('0.00') }}%</div>
      </q-card-section>
    </q-card>
    <q-card class="col-6 col-md my-card">
      <q-card-section align="center">
        <div class="text-subtitle-2 text-grey-8 text-weight-light">Patrim.Actual Total</div>
        <div class="text-h5 text-green-7 text-weight-light">{{ parseFloat(registrosPanelDatos.patrimonioBrutoMes) | numeralFormat('0,0')  }}€</div>
      </q-card-section>
    </q-card>
  </div>

</template>

<script>
// import numeral from 'numeral'
export default {
  props: ['value'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      registrosPanelDatos: []
    }
  },
  methods: {
    getPanelDatos (objFilter) {
      // cards resumen de rentabilidad y patrimonio actual
      this.$axios.get('movimientos/bd_movimientos.php/findcpanelDatos/', { params: objFilter })
        .then(response => {
          this.registrosPanelDatos = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    this.getPanelDatos(this.value) // carga datos de panel datos
  }
}
</script>
