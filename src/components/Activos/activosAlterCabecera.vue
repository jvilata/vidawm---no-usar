  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card class="q-pt-none q-pl-xs q-pr-xs">
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
      <div class="row">
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="m" label="Min.Ticket" stack-label v-model="recordToSubmit.minimumTicket" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="y" label="Duration" stack-label v-model="recordToSubmit.duration" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="y" label="Inv.Period" stack-label v-model="recordToSubmit.investmentPeriod" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="m" label="Target Size" stack-label v-model="recordToSubmit.targetSize" />
        <q-input class="col-xs-4 col-sm-1" @blur="updateRec" outlined suffix="m" label="GP Commitment" stack-label v-model="recordToSubmit.gpCommitment" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined label="Launch" stack-label v-model="recordToSubmit.launch" />
        <q-input class="col-xs-5 col-sm-2" @blur="updateRec" outlined label="Deadline decision" stack-label :value="formatDate(recordToSubmit.decisionDeadline)" @input="val => recordToSubmit.fecha=val" >
          <template v-slot:append>
              <q-icon name="event" class="cursos-pointer">
                <q-popup-proxy>
                  <wgDate v-model="recordToSubmit.decisionDeadline" />
                </q-popup-proxy>
              </q-icon>
          </template>
        </q-input>
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="%" label="Manag.fee" stack-label v-model="recordToSubmit.managementFee" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="%" label="Succ.fee" stack-label v-model="recordToSubmit.successFee" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined suffix="%" label="Hurdle Rate" stack-label v-model="recordToSubmit.hurdleRate" />
        <q-input class="col-xs-3 col-sm-1" @blur="updateRec" outlined label="N.Assets" stack-label v-model="recordToSubmit.nAssets" />
      </div>

      <activosAlterLineas :key="refresh" :value="value"/>
  </q-card>
</template>

<script>
import { mapState } from 'vuex'
import { date } from 'quasar'
import { headerFormData } from 'boot/axios.js'
import wgDate from 'components/General/wgDate.vue'
import activosAlterLineas from 'components/Activos/activosAlterLineas.vue'
export default {
  props: ['value'], // value es el objeto con los campos de filtro que le pasa accionesMain con v-model
  data () {
    return {
      refresh: 0,
      recordToSubmit: {
        minimumTicket: 0,
        duration: 0,
        investmentPeriod: 0,
        targetSize: 0,
        gpCommitment: 0,
        launch: 0,
        decisionDeadline: '',
        managementFee: 0,
        successFee: 0,
        hurdleRate: 0,
        nAssets: 0
      }
    }
  },
  computed: {
    ...mapState('login', ['user'])
  },
  methods: {
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    },
    updateRec () {
      var formData = new FormData()
      for (var key in this.recordToSubmit) {
        formData.append(key, this.recordToSubmit[key])
      }
      return this.$axios.post('activos/bd_activos.php/guardarBD', formData, headerFormData)
        .then(response => {
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  components: {
    wgDate: wgDate,
    activosAlterLineas: activosAlterLineas
  },
  mounted () {
    Object.assign(this.recordToSubmit, this.value)
    var objFilter = {
      id: this.value.id
    }
    this.$axios.get('activos/bd_activos.php/findActivosFilter', { params: objFilter })
      .then(response => {
        Object.assign(this.recordToSubmit, response.data[0])
      })
      .catch(error => {
        this.$q.dialog({ title: 'Error', message: error })
      })
  },
  destroyed () {
    // guardamos valor en tabs por si despus queremos recuperarlo
    this.$emit('input', this.recordToSubmit)
  }
}
</script>
