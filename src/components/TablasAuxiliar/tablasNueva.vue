<!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Nueva Tabla Auxiliar</div>
    </q-card-section>
    <q-form @submit="addRecord" class="q-gutter-y-xs">
      <q-input outlined clearable label="Cod. Tabla" v-model="record.codTabla" stack-label />
      <q-input outlined clearable label="Cod.Elemento" v-model="record.codElemento" stack-label />
      <q-input outlined clearable label="Valor" v-model="record.valor1" stack-label />
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Añadir" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('hide')"/><!-- lo captura accionesMain -->
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
import { mapActions } from 'vuex'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      record: {
        codEmpresa: '01',
        codTabla: '',
        codElemento: '',
        valor1: ''
      }
    }
  },
  methods: {
    ...mapActions('tablasAux', ['addTablaAux']),
    addRecord () {
      this.addTablaAux(this.record)
        .then((result) => {
          this.record.id = result.data.id
          this.$emit('hide', this.record)
        })
        .catch(error => {
          this.$q.dialog({
            message: error.message
          })
        })
    }
  }
}
</script>
