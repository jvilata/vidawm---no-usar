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
      <q-select
        class="col"
        outlined
        clearable
        label="Activo"
        stack-label
        v-model="filterR.idActivo"
        :options="listaActivosFilter"
        option-value="id"
        option-label="nombre"
        multiple
        emit-value
        map-options
        @filter="filterActivos"
        use-chips
      />
      <q-select class="col"
        outlined
        clearable
        label="Gestor/Arrend"
        stack-label
        v-model="filterR.idEntidad"
        :options="listaEntidadesFilter"
        option-value="id"
        option-label="nombre"
        emit-value
        map-options
        @filter="filterEntidades"
        use-input
        hide-selected
        fill-input
        input-debounce="0"
      />
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
import { mapState, mapActions } from 'vuex'
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
    ...mapState('tablasAux', ['listaTiposActivo', 'listaTiposProducto', 'listaResumenTiposProducto']),
    ...mapState('entidades', ['listaEntidades']),
    ...mapState('activos', ['listaActivos'])
  },
  methods: {
    ...mapActions('entidades', ['loadEntidades']),
    ...mapActions('activos', ['loadActivos']),
    filterActivos (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaActivosFilter = this.listaActivos.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterEntidades (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaEntidadesFilter = this.listaEntidades.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    getResumenPatrimonio () {
      var objFilter = Object.assign({}, this.filterR)
      objFilter.mes = this.value.mes
      objFilter.codEmpresa = this.value.codEmpresa
      objFilter.idActivo = (objFilter.idActivo && objFilter.idActivo !== null ? objFilter.idActivo.join() : null) // paso de array a concatenacion de strings (join)
      objFilter.tipoActivo = (objFilter.tipoActivo && objFilter.tipoActivo !== null ? objFilter.tipoActivo.join() : null) // paso de array a concatenacion de strings (join)
      // desglosar TipoProducto
      objFilter.tipoProducto = objFilter.tipoProducto1.substring(0, objFilter.tipoProducto1.indexOf('.') + 1)
      // objFilter.tipoProducto = (objFilter.tipoProducto && objFilter.tipoProducto !== null ? objFilter.tipoProducto.join() : null) // paso de array a concatenacion de strings (join)

      // donut resumen patrimonio
      this.$axios.get('movimientos/bd_movimientos.php/findanalisisPatrimonio/', { params: objFilter })
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
    if (this.listaEntidades.length <= 0) this.loadEntidades() // carga store listaEntidades
    if (this.listaActivos.length <= 0) this.loadActivos(this.user.codEmpresa) // carga store listaActivos
  },
  components: {
    dashboardResumenPatrimonio: dashboardResumenPatrimonio
  }
}
</script>
