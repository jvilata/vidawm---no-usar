  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-input outlined clearable label="Nro.Nota" stack-label v-model="filterR.nroNota" />
      <q-select
        outlined
        clearable
        label="Persona"
        stack-label
        v-model="filterR.idPersonal"
        :options="listaPersonal"
        option-value="id"
        option-label="nombre"
        emit-value
        map-options
      />
      <q-input outlined clearable label="Fecha Desde" stack-label :value="formatDate(filterR.fechainicial)" @input="val => filterR.fechainicial=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechainicial">
                <wgDate v-model="filterR.fechainicial" @input="$refs.fechainicial.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Hasta" stack-label :value="formatDate(filterR.fechafinal)" @input="val => filterR.fechafinal=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechafinal">
                <wgDate v-model="filterR.fechafinal" @input="$refs.fechafinal.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
       <q-select
        label="Estado Nota"
        stack-label
        outlined
        clearable
        v-model="filterR.estadoNota"
        :options="listaEstadosFactura"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
        map-options
      />
      <q-select
        label="Cobrada"
        stack-label
        outlined
        clearable
        v-model="filterR.cobrada"
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
import { mapState } from 'vuex'
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
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaEstadosFactura']),
    ...mapState('personal', ['listaPersonal'])
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
  },
  components: {
    wgDate: wgDate
  }
}
</script>
