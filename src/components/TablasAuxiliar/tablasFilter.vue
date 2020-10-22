  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Filtrar por</div>
    </q-card-section>
    <q-form @submit="getRecords" class="q-gutter-y-xs">
      <q-input outlined clearable label="ID" v-model="filterR.id" stack-label />
      <q-input outlined clearable label="Cod. Tabla" v-model="filterR.codTabla" stack-label />
      <q-input outlined clearable label="Cod.Elemento" v-model="filterR.codElemento" stack-label />
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Buscar" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('hide')"/><!-- lo captura accionesMain -->
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      filterR: {}
    }
  },
  methods: {
    getRecords () {
      this.$emit('getRecords', this.filterR) // lo captura accionesMain
    }
  },
  mounted () {
    this.filterR = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
  }
}
</script>
