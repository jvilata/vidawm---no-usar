  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-select
        :readonly="this.user.pers.userRol !== '1'"
        outlined
        clearable
        label="Persona"
        stack-label
        v-model="filterR.idpersonal"
        :options="listaPersonal"
        option-value="id"
        option-label="nombre"
        emit-value
        map-options
      />
      <q-select
        outlined
        clearable
        label="Tipo Jornada"
        stack-label
        v-model="filterR.tipoJornada"
        :options="listaTiposJornada"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-select
        label="Ultimo fichaje"
        stack-label
        outlined
        clearable
        v-model="filterR.ultimo"
        :options="listaSINO"
        option-value="id"
        option-label="desc"
        emit-value
        map-options
      />
      <q-input outlined clearable label="Fecha Desde" stack-label :value="formatDate(filterR.fechaD)" @input="val =>filterR.fechaD=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechaD">
                <wgDate v-model="filterR.fechaD" @input="filterR.ultimo=null;$refs.fechaD.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Hasta" stack-label :value="formatDate(filterR.fechaH)" @input="val =>filterR.fechaH=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechaH">
                <wgDate v-model="filterR.fechaH" @input="filterR.ultimo=null;$refs.fechaH.hide()"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
       <q-select
        label="Estado"
        stack-label
        outlined
        clearable
        v-model="filterR.estado"
        :options="listaEstadosJornada"
        option-value="codElemento"
        option-label="codElemento"
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
      filterR: {
        ultimo: ''
      }
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaSINO', 'listaTiposJornada', 'listaEstadosJornada']),
    ...mapState('personal', ['listaPersonal']),
    ...mapState('login', ['user'])
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
    if (!this.filterR.fechaD) {
      this.filterR.ultimo = null
      this.filterR.fechaD = date.formatDate(new Date(), 'YYYY-MM-01')
      this.filterR.fechaH = date.formatDate(date.subtractFromDate(date.addToDate(date.adjustDate(new Date(), { date: 1 }), { month: 1 }), { days: 1 }), 'YYYY-MM-DD')
    }
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
