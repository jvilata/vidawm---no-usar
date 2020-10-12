  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div style="height: 100vh">
      <!-- formulario tabla de resultados de busqueda -->
      <q-card>
        <q-card-section   class="q-pa-xs">
            <q-item class="q-pa-xs bg-indigo-1 text-grey-8">
              <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
              <q-item-section avatar>
                <q-icon name="edit" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-h6">
                  {{ value.nombre }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-btn
                @click="$emit('close')"
                flat
                round
                dense
                icon="close"/>
              </q-item-section>
            </q-item>
      </q-card-section>
    </q-card>
      <accionesGrid
        v-model="registrosSeleccionados"
        :idActivo="value.id"
        />
    </div>
</template>

<script>
import { mapState } from 'vuex'
import accionesGrid from 'components/Acciones/accionesGrid.vue'
export default {
  props: ['value'],
  data () {
    return {
      registrosSeleccionados: []
    }
  },
  computed: {
    ...mapState('login', ['user']) // importo state.user desde store-login
  },
  methods: {
    getRecords () {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      var objFilter = { tipoObjeto: 'A', idObjeto: this.value.id }
      return this.$axios.get('acciones/bd_acciones.php/findAccionesFilter', { params: objFilter })
        .then(response => {
          this.registrosSeleccionados = response.data
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    this.getRecords()
  },
  components: {
    accionesGrid: accionesGrid
  }
}
</script>
