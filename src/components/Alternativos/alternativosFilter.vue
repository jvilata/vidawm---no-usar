  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-select
        class="col-xs-12 col-sm-12"
        outlined
        clearable
        label="Activo"
        stack-label
        v-model="filterR.idActivo"
        :options="listaActivosFilter"
        option-value="id"
        option-label="nombre"
        multiple
        use-chips
        emit-value
        @filter="filterActivos"
        map-options
      />
      <q-select
        outlined
        clearable
        label="Tipo Activo"
        stack-label
        v-model="filterR.tipoActivo"
        :options="listaTiposActivo"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-select
        outlined
        clearable
        label="Tipo Producto"
        stack-label
        v-model="filterR.tipoProducto"
        :options="listaTiposProducto"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
        multiple
        use-chips
      />
      <q-select
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
      <!--q-input outlined clearable label="Nombre" stack-label v-model="filterR.nombre" /-->
      <q-input outlined clearable label="Año Desde" stack-label v-model="filterR.anyoDesde" />
      <!-- en q-select multiple los valores estan en un array, como en nuestro caso estan en un string separados por comas
          hay que crear un array con los valores en el columns format (split) y aqui volver a convertir el array a string (join) -->
      <q-select
        label="Estado Activo"
        stack-label
        outlined
        clearable
        v-model="filterR.estadoActivo"
        multiple
        :options="listaEstadosActivo"
        option-value="codElemento"
        option-label="valor1"
        emit-value
        map-options
        use-chips
      />
      <q-select
        label="Computa"
        stack-label
        outlined
        clearable
        v-model="filterR.computa"
        :options="listaSINO"
        option-value="id"
        option-label="desc"
        emit-value
        map-options
      />
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Buscar" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('hide')"/><!-- lo captura accionesMain -->
      </q-card-actions>
  </q-form>
  </q-card>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      filterR: {
        idActivo: []
      },
      listaEntidadesFilter: [],
      listaActivosFilter: []
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaTipoAcc', 'listaTiposActivo', 'listaMeses', 'listaTiposProducto', 'listaEstadosActivo', 'listaTipoOperacion']),
    ...mapState('entidades', ['listaEntidades']),
    ...mapState('activos', ['listaActivos']),
    ...mapState('login', ['user']) // importo state.user desde store-login
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
    getRecords () {
      this.$emit('getRecords', this.filterR) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    }
  },
  mounted () {
    this.filterR = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
    this.loadEntidades()
    if (this.listaActivos.length <= 0) this.loadActivos(this.user.codEmpresa) // carga store listaActivos
  },
  destroyed () {
    // guardamos valor en tabs por si despus queremos recuperarlo
    this.$emit('input', this.filterR)
  }
}
</script>
