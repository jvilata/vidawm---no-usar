<template>
  <div>
    <div class="q-ml-md q-mr-md row">
      <q-select class="col"
        outlined
        clearable
        label="Tipo Activo"
        stack-label
        v-model="filterR.tipoActivo"
        :options="listaTiposActivo"
        option-value="codElemento"
        option-label="codElemento"
        multiple
        use-chips
        emit-value
      />
      <q-select class="col"
        outlined
        clearable
        label="Tipo Producto"
        stack-label
        v-model="filterR.tipoProducto"
        :options="listaTiposProducto"
        option-value="codElemento"
        option-label="codElemento"
        multiple
        use-chips
        emit-value
      />
      <q-btn class="col-1" label="Mostrar" color="primary" @click="getResumenPatrimonio"/>
    </div>
    <div class="row">
      <div class="col-12 col-md" >
        <q-item class="q-ma-md q-pa-xs bg-indigo-1 text-grey-8">
          <q-item-section align="center">
            <div class="text-h6">Análisis de Patrimonio</div>
          </q-item-section>
        </q-item>
        <q-item >
          <q-item-section align="center">
            <dashboardResumenPatrimonio v-model="registrosResumenPatrimonio" :key="refreshRec"/>
          </q-item-section>
        </q-item>
      </div>
    </div>
  </div>
</template>

<script>
import dashboardResumenPatrimonio from 'components/Dashboard/dashboardResumenPatrimonio.vue'
import { mapState } from 'vuex'
export default {
  props: ['value', 'keyValue'], // en 'value' tenemos la tabla de datos del grid
  data () {
    return {
      refreshRec: 0,
      filterR: {},
      registrosResumenPatrimonio: []
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaTiposActivo', 'listaTiposProducto'])
  },
  methods: {
    getResumenPatrimonio () {
      var objFilter = Object.assign({}, this.filterR)
      objFilter.mes = this.value.mes
      objFilter.codEmpresa = this.value.codEmpresa
      objFilter.tipoActivo = (objFilter.tipoActivo && objFilter.tipoActivo !== null ? objFilter.tipoActivo.join() : null) // paso de array a concatenacion de strings (join)
      // objFilter.estadoActivo = (objFilter.estadoActivo && objFilter.estadoActivo !== null ? objFilter.estadoActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.tipoProducto = (objFilter.tipoProducto && objFilter.tipoProducto !== null ? objFilter.tipoProducto.join() : null) // paso de array a concatenacion de strings (join)

      // donut resumen patrimonio
      this.$axios.get('movimientos/bd_movimientos.php/findanalisisPatrimonio/', { params: objFilter })
        .then(response => {
          this.registrosResumenPatrimonio = response.data
          this.refreshRec++ // para que refresque el componente
          console.log('si')
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    // this.getResumenPatrimonio(this.value) // carga datos donut resumen patrim
  },
  components: {
    dashboardResumenPatrimonio: dashboardResumenPatrimonio
  }
}
</script>
