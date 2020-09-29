  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 600px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Enviar Mail</div>
    </q-card-section>

    <q-form @submit="enviarMail" class="q-gutter-y-xs">
      <q-input outlined clearable label="Destino" stack-label v-model="filterR.destino" />
      <q-input outlined clearable label="CC" stack-label v-model="filterR.destinoCopia" />
      <q-input outlined clearable label="Asunto" stack-label v-model="filterR.asunto" />
      <q-input outlined clearable label="Texto" stack-label v-model="filterR.texto"
        type="textarea"
        autofocus counter
        @keyup.enter.stop />
      <!--q-editor label="Texto" stack-label v-model="filterR.texto" /-->
      <q-card-actions align="right">
        <q-btn  flat type="submit" label="Enviar" color="primary"/>
        <q-btn  flat label="Cancel" color="primary" @click="$emit('close')"/><!-- lo captura accionesMain -->
      </q-card-actions>
  </q-form>
  </q-card>
</template>

<script>
import { openURL } from 'quasar'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      filterR: {}
    }
  },
  methods: {
    enviarMail () {
      var host = this.$axios.defaults.baseURL // 'https://vidawm.com/privado/php/'
      var strUrl = host + this.filterR.url + '&destino=' + this.filterR.destino + '&destinoCopia=' + this.filterR.destinoCopia +
          '&asunto=' + this.filterR.asunto + '&texto=' + this.filterR.texto
      if (window.cordova === undefined) { // desktop
        openURL(strUrl)
      } else { // dispositivo movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
      this.$emit('close')
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
