  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-input outlined clearable label="Id" stack-label v-model="filterR.id" />
      <q-input outlined clearable label="Nombre" stack-label v-model="filterR.nombre" />
      <q-input outlined clearable label="Persona Contacto" stack-label v-model="filterR.personaContacto" />
      <q-input outlined clearable label="Cargo" stack-label v-model="filterR.cargo" />
      <q-input outlined clearable label="Telefono" stack-label v-model="filterR.telefono" />
      <q-input outlined clearable label="Email" stack-label v-model="filterR.email" />
      <q-select outlined clearable
            stack-label
            label="Tipo Entidad"
            v-model="filterR.tipoEntidad"
            :options="listaTipoEntidad"
            option-value="codElemento"
            option-label="codElemento"
            emit-value
      />
      <q-input outlined clearable label="Pais" stack-label v-model="filterR.pais" />
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Buscar" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('hide')"/><!-- lo captura accionesMain -->
      </q-card-actions>
  </q-form>
  </q-card>
</template>

<script>
import { date } from 'quasar'
import { mapState } from 'vuex'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      filterR: {},
      listaEntidadesFilter: []
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaTipoEntidad'])
  },
  methods: {
    getRecords () {
      this.$emit('getRecords', this.filterR) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    }
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
