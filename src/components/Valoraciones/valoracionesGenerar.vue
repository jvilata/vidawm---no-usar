  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Generar Valoraciones</div>
    </q-card-section>

    <q-form @submit="generarValoraciones" class="q-gutter-y-xs">
      <q-select
        :rules="[val => !!val || 'Campo obligatorio']"
        outlined
        label="Mes desde que se copia"
        stack-label
        v-model="filterR.mes"
        :options="listaMeses"
        option-value="mes"
        option-label="mes"
        emit-value
      />
      <q-input outlined clearable :rules="[val => !!val || 'Campo obligatorio']" label="Fecha que se genera" stack-label :value="formatDate(filterR.fecha)" @input="val => filterR.fecha=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fecha">
                <wgDate v-model="filterR.fecha" @input="$refs.fecha.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
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
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Generar" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('close')"/><!-- lo captura accionesMain -->
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
      filterR: {},
      listaEntidadesFilter: []
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaMeses']),
    ...mapState('entidades', ['listaEntidades'])
  },
  methods: {
    ...mapActions('entidades', ['loadEntidades']),
    filterEntidades (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaEntidadesFilter = this.listaEntidades.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    generarValoraciones () {
      this.$emit('generarValoraciones', this.filterR) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    }
  },
  mounted () {
    this.filterR = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
    if (this.listaEntidades.length === 0) this.loadEntidades()
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
