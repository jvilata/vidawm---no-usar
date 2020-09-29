  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="max-width: 800px;" class="q-pr-xs q-gutter-xs">
    <q-form @submit="saveForm" @keyup.esc="$emit('close')">
      <q-card-section class="row bg-primary text-white">
        <div class="text-h6">{{title}}</div>
        <q-space />
        <q-btn
            @click="$emit('close')"
            flat
            round
            dense
            icon="close"/>
      </q-card-section>
      <q-card-section>
        <div class="row">
          <q-select
            class="col-xs-12 col-sm-12"
            outlined
            clearable
            label="Activo"
            stack-label
            v-model="recordToSubmit.idActivo"
            :options="listaActivosFilter"
            option-value="id"
            option-label="nombre"
            emit-value
            map-options
            @filter="filterActivos"
            use-input
            hide-selected
            fill-input
            input-debounce="0"
          />
          <q-input class="col-xs-12 col-sm-12" outlined v-model="recordToSubmit.descripcion" label="Descripción"
                  type="textarea"
                  counter
                  @keyup.enter.stop />
        </div>
        <div class="row">
          <q-input class="col-xs-6 col-sm-3" outlined label="Unidades" stack-label v-model="recordToSubmit.unidades" debounce="1000" @input="calcularDatosLinea" />
          <q-input class="col-xs-6 col-sm-3" outlined label="Precio" stack-label v-model="recordToSubmit.precio" debounce="1000" @input="calcularDatosLinea"/>
          <q-input class="col-xs-6 col-sm-3" outlined label="%Dto" stack-label v-model="recordToSubmit.pdescuento" debounce="1000" @input="calcularDatosLinea" />
          <q-input class="col-xs-6 col-sm-3" outlined label="Descuento" stack-label v-model="recordToSubmit.descuento" readonly  />
        </div>
        <div class="row">
          <q-input class="col-xs-6 col-sm-3" outlined stack-label v-model="recordToSubmit.neto" readonly label="Neto" debounce="1000"/>
          <q-input class="col-xs-6 col-sm-3" outlined stack-label v-model="recordToSubmit.piva" label="%Iva" debounce="1000" @input="calcularDatosLinea"/>
          <q-input class="col-xs-6 col-sm-3" outlined stack-label v-model="recordToSubmit.totalIva" readonly label="Total Iva" debounce="1000"/>
          <q-input class="col-xs-6 col-sm-3" outlined stack-label v-model="recordToSubmit.totalLinea" readonly label="Total Linea"/>
        </div>
      </q-card-section>
      <q-card-actions align=right>
        <q-btn type="submit" label="Save" color="primary"/>
        <q-btn @click="$emit('close')" label="Cancel" color="negative"/>
      </q-card-actions>
    </q-form>
  </q-card>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      title: 'Detalle línea',
      recordToSubmit: {},
      listaActivosFilter: []
    }
  },
  computed: {
    ...mapState('activos', ['listaActivos'])
  },
  methods: {
    filterActivos (val, update, abort) {
      update(() => {
        const needle = val.toLowerCase()
        this.listaActivosFilter = this.listaActivos.filter(v => v.nombre.toLowerCase().indexOf(needle) > -1)
      })
    },
    saveForm () {
      delete this.recordToSubmit.descuento // campo calculado
      this.$emit('saveRecord', this.recordToSubmit) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    },
    calcularDatosLinea () {
      var obj = {
        unidades: parseFloat(this.recordToSubmit.unidades),
        precio: parseFloat(this.recordToSubmit.precio),
        pdescuento: parseFloat(this.recordToSubmit.pdescuento),
        piva: parseFloat(this.recordToSubmit.piva)
      }
      obj.descuento = Math.round((obj.unidades * obj.precio * obj.pdescuento / 100.0) * 100.0) / 100
      obj.neto = Math.round((obj.unidades * obj.precio - obj.descuento) * 100.0) / 100 // base de iva
      obj.totalIva = Math.round((obj.neto * obj.piva / 100.0) * 100.0) / 100
      obj.totalLinea = Math.round((obj.neto + obj.totalIva) * 100.0) / 100
      Object.assign(this.recordToSubmit, obj)
    }
  },
  mounted () {
    this.recordToSubmit = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
    this.calcularDatosLinea()
  },
  destroyed () {
  }
}
</script>
