  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div style="height: 100vh">
      <!-- formulario tabla de resultados de busqueda -->
      <accionesGrid
        v-model="registrosSeleccionados"
        :idEntidad="value.id"
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
      var objFilter = { tipoObjeto: 'E', idObjeto: this.value.id }
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
