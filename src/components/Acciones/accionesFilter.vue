  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>

    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-select
        outlined
        clearable
        label="Quién crea"
        stack-label
        v-model="filterR.idUserQuien"
        :options="listaUsers"
        option-value="email"
        option-label="email"
        emit-value
      />
      <q-input outlined clearable label="Descripción" stack-label v-model="filterR.descripcion" />
      <q-input outlined clearable label="Fecha" stack-label :value="formatDate(filterR.fecha)" @input="val => filterR.fecha=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy>
                <wgDate v-model="filterR.fecha" />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Próx Desde" stack-label :value="formatDate(filterR.fechaProxDesde)" @input="val => filterR.fechaProxDesde=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy>
                <wgDate v-model="filterR.fechaProxDesde" />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Próx Hasta" stack-label :value="formatDate(filterR.fechaProxHasta)"  @input="val => filterR.fechaProxHasta=val" >
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy>
                <wgDate v-model="filterR.fechaProxHasta" />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <!-- en q-select multiple los valores estan en un array, como en nuestro caso estan en un string separados por comas
          hay que crear un array con los valores en el columns format (split) y aqui volver a convertir el array a string (join) -->
      <q-select
        label="Quién realiza"
        stack-label
        outlined
        clearable
        v-model="filterR.idUserQuienProx"
        multiple
        :options="listaUsers"
        option-value="email"
        option-label="email"
        emit-value
        use-chips
      />
      <q-select
        label="Tipo Acción"
        stack-label
        outlined
        clearable
        v-model="filterR.tipoAccion"
        :options="listaTipoAcc"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
        map-options
      />
      <q-select
        label="Realizada"
        stack-label
        outlined
        clearable
        v-model="filterR.realizada"
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
    ...mapState('tablasAux', ['listaSINO', 'listaUsers', 'listaTipoAcc'])
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
