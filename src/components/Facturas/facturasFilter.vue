  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-input outlined clearable label="Nro Factura" stack-label v-model="filterR.nroFactura" />
      <q-select
        outlined
        clearable
        label="Cliente/Prov."
        stack-label
        v-model="filterR.idCliente"
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
      <q-input outlined clearable label="Fecha Desde" stack-label :value="formatDate(filterR.fechainicial)" @input="val => filterR.fechainicial=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy>
                <wgDate v-model="filterR.fechainicial" />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Hasta" stack-label :value="formatDate(filterR.fechafinal)" @input="val => filterR.fechafinal=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy>
                <wgDate v-model="filterR.fechafinal" />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-select
        outlined
        clearable
        label="Tipo Factura"
        stack-label
        v-model="filterR.tipoFactura"
        :options="listaTiposFactura"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-select
        label="Estado Factura"
        stack-label
        outlined
        clearable
        v-model="filterR.estadoFactura"
        :options="listaEstadosFactura"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-select
        outlined
        clearable
        label="Activo"
        stack-label
        v-model="filterR.idActivo"
        :options="listaActivosFilter"
        option-value="id"
        option-label="nombre"
        emit-value
        map-options
        @filter="filterActivos"
        use-input
        hide-selected
        fill-input
        input-debounce="0"
      />
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Buscar" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('hide')"/><!-- lo captura accionesMain -->
      </q-card-actions>
  </q-form>
  </q-card>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
import wgDate from 'components/General/wgDate.vue'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      filterR: {},
      listaEntidadesFilter: [],
      listaActivosFilter: []
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaMeses', 'listaEstadosFactura', 'listaTiposFactura']),
    ...mapState('entidades', ['listaEntidades']),
    ...mapState('activos', ['listaActivos'])
  },
  methods: {
    filterEntidades (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaEntidadesFilter = this.listaEntidades.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    filterActivos (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaActivosFilter = this.listaActivos.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    getRecords () {
      this.$emit('getRecords', this.filterR) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    }
  },
  components: {
    wgDate: wgDate
  },
  mounted () {
    this.filterR = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
  },
  destroyed () {
    // guardamos valor en tabs por si despus queremos recuperarlo
    this.$emit('input', this.filterR)
  }
}
</script>
