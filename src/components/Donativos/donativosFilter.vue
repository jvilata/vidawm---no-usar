  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-input outlined clearable label="Id" stack-label v-model="filterR.id" />
      <q-select
        outlined
        clearable
        label="Entidad"
        stack-label
        v-model="filterR.idEntidad"
        :options="listaEntidades"
        option-value="id"
        option-label="nombre"
        emit-value
        map-options
      />
      <q-input outlined clearable label="Fecha" stack-label :value="formatDate(filterR.fecha)" @input="val => filterR.fecha=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fecha">
                <wgDate v-model="filterR.fecha" @input="$refs.fecha.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Desde" stack-label :value="formatDate(filterR.fechaGeneracionGT)" @input="val => filterR.fechaGeneracionGT=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechaGeneracionGT">
                <wgDate v-model="filterR.fechaGeneracionGT" @input="$refs.fechaGeneracionGT.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Hasta" stack-label :value="formatDate(filterR.fechaGeneracionLT)" @input="val => filterR.fechaGeneracionLT=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechaGeneracionLT">
                <wgDate v-model="filterR.fechaGeneracionLT" @input="$refs.fechaGeneracionLT.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
       <q-select
        label="Tipo Operación"
        stack-label
        outlined
        clearable
        v-model="filterR.tipoOperacion"
        :options="listaTipoOperacion"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
        map-options
      />
      <q-select
        label="Generar"
        stack-label
        outlined
        clearable
        v-model="filterR.generar"
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
import wgDate from 'components/General/wgDate.vue'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      filterR: {}
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaTipoAcc', 'listaTiposActivo', 'listaMeses', 'listaTiposProducto', 'listaEstadosActivo', 'listaTipoOperacion']),
    ...mapState('entidades', ['listaEntidades'])
  },
  methods: {
    ...mapActions('entidades', ['loadEntidades']),
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
  },
  destroyed () {
    // guardamos valor en tabs por si despus queremos recuperarlo
    this.$emit('input', this.filterR)
  },
  components: {
    wgDate: wgDate
  }
}
</script>
