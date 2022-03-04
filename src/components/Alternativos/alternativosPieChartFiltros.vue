<template>
  <div>
    <div class="q-ml-md q-mr-md row">
      <q-select class="col"
        outlined
        clearable
        label="Tipo Producto"
        stack-label
        v-model="filterR.tipoProducto1"
        :options="listaResumenTiposProducto"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-btn class="col-1" icon="sync" color="primary" @click="getResumenPatrimonio"/>
    </div>
    <div class="row">
      <div class="col" >
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
      registrosResumenPatrimonio: [],
      listaEntidadesFilter: [],
      listaActivosFilter: []
    }
  },
  computed: {
    ...mapState('login', ['user']), // importo state.user desde store-login
    ...mapState('tablasAux', ['listaTiposActivo', 'listaTiposProducto', 'listaResumenTiposProducto'])
  },
  methods: {
    getResumenPatrimonio () {
      var objFilter = Object.assign({}, this.filterR)
      objFilter.idActivo = ''
      this.value.forEach(r => {
        if (r.seleccionado === '1') {
          if (objFilter.idActivo.length > 0) objFilter.idActivo += ','
          objFilter.idActivo += r.id
        }
      })
      // objFilter.tipoProducto = (objFilter.tipoProducto && objFilter.tipoProducto !== null ? objFilter.tipoProducto.join() : null) // paso de array a concatenacion de strings (join)
      // desglosar TipoProducto
      objFilter.tipoProducto = objFilter.tipoProducto1.substring(0, objFilter.tipoProducto1.indexOf('.') + 1)

      // donut resumen patrimonio
      this.$axios.get('activos/bd_activos.php/findAnalisisFondos3/', { params: objFilter })
        .then(response => {
          this.registrosResumenPatrimonio = response.data
          this.refreshRec++ // para que refresque el componente
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
  },
  components: {
    dashboardResumenPatrimonio: dashboardResumenPatrimonio
  }
}
</script>
