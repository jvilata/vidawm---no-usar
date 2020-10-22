  <!-- componente que se llama desde accionesMain y que presenta el formulario de filtro y el boton de busqueda -->
  <template>
  <q-card style="width: 400px;" class="q-pr-xs q-gutter-xs">
    <q-card-section class="bg-primary text-white">
      <div class="text-h6">Registro jornada</div>
    </q-card-section>

    <q-form @submit="saveRecord" class="q-gutter-y-xs">
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
        @blur="val=>{if (val!=='ORDINARIA') {filterR.estado='PENDIENTE'; filterR.fechaInicio=filterR.fechaInicio.substring(0,10)+' 00:00'; filterR.fechaFin=filterR.fechaFin.substring(0,10)+' 00:00'}} "
        stack-label
        v-model="filterR.tipoJornada"
        :options="listaTiposJornada"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-input outlined clearable label="Fecha Inicio" stack-label :value="formatDate(filterR.fechaInicio)">
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechaInicio">
                <wgDate :value="filterR.fechaInicio" @input="val => {filterR.fechaInicio=val.substring(0, 10) + ' ' + filterR.fechaInicio.substring(11, 16); $refs.fechaInicio.hide()}"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input class="col-xs-6 col-sm-6" outlined clearable label="Hora Inicio" stack-label :value="formatHour(filterR.fechaInicio)" @input="val => filterR.fechaInicio=val">
        <template v-slot:append>
            <q-icon name="access_time" class="cursos-pointer">
              <q-popup-proxy ref="horaInicio">
                <q-time v-model="filterR.fechaInicio"
                  @input="$refs.horaInicio.hide()"
                  :hour-options="hourOptions"
                  :minute-options="minuteOptions"
                  mask="YYYY-MM-DD HH:mm:ss"
                  format24h
                />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input outlined clearable label="Fecha Fin" stack-label :value="formatDate(filterR.fechaFin)" @input="val => filterR.fechaFin=val">
        <template v-slot:append>
            <q-icon name="event" class="cursos-pointer">
              <q-popup-proxy ref="fechaFin">
                <wgDate :value="filterR.fechaFin" @input="val => {filterR.fechaFin=val.substring(0, 10) + ' ' + filterR.fechaFin.substring(11, 16); $refs.fechaFin.hide()}"/>
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-input class="col-xs-6 col-sm-6" outlined clearable label="Hora Fin" stack-label :value="formatHour(filterR.fechaFin)" @input="val => filterR.fechaFin=val">
        <template v-slot:append>
            <q-icon name="access_time" class="cursos-pointer">
              <q-popup-proxy ref="horaFin">
                <q-time v-model="filterR.fechaFin"
                  @input="$refs.horaFin.hide()"
                  :hour-options="hourOptions"
                  :minute-options="minuteOptions"
                  mask="YYYY-MM-DD HH:mm:ss"
                  format24h
                />
              </q-popup-proxy>
            </q-icon>
        </template>
      </q-input>
      <q-select
        :readonly="this.user.pers.userRol !== '1'"
        outlined
        clearable
        label="Estados Jornada"
        stack-label
        v-model="filterR.estado"
        :options="listaEstadosJornada"
        option-value="codElemento"
        option-label="codElemento"
        emit-value
      />
      <q-card-actions align="right">
        <q-btn  type="submit" :label="(filterR.id !== -1 ?'Modificar':'Registrar')" color="primary"/>
        <q-btn v-if="filterR.id !== -1" label="Borrar" color="negative" @click="borrarRegistro"/>
        <q-btn  label="Cancelar" color="primary" @click="$emit('hide')"/><!-- lo captura accionesMain -->
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
        fechaInicio: ''
      },
      listaEntidadesFilter: [],
      hourOptions: [7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
      minuteOptions: [0, 30],
      secondOptions: [0, 10, 20, 30, 40, 50]
    }
  },
  computed: {
    ...mapState('tablasAux', ['listaTiposJornada', 'listaEstadosJornada']),
    ...mapState('personal', ['listaPersonal']),
    ...mapState('login', ['user'])
  },
  methods: {
    crearRegistro () { // crear nuevo registro
      var horaInicio1 = this.user.pers.horaInicio1
      if (horaInicio1 === '' || horaInicio1 === null) horaInicio1 = '09:00'
      var horaFin1 = this.user.pers.horaFin1
      if (horaFin1 === '' || horaFin1 === null) horaFin1 = '14:00'
      var horaInicio2 = this.user.pers.horaInicio2
      if (horaInicio2 === '' || horaInicio2 === null) horaInicio2 = '15:00'
      var horaFin2 = this.user.pers.horaFin2
      if (horaFin2 === '' || horaFin2 === null) horaFin2 = '18:00'

      this.filterR = {
        id: -1,
        idpersonal: this.user.pers.id,
        tipoJornada: 'ORDINARIA',
        fechaInicio: date.formatDate(new Date(), 'YYYY-MM-DD'),
        fechaFin: date.formatDate(new Date(), 'YYYY-MM-DD'),
        estado: 'APROBADO'
      }
      // en this.value tengo el ultmo fichaje
      if (this.value.fechaInicio && this.value.fechaInicio.substring(0, 10) === this.filterR.fechaInicio.substring(0, 10) && // misma fecha
        this.value.fechaInicio.substring(11, 16) === horaInicio1) { // primera hora de fichaje, falta la segunda
        this.filterR.fechaInicio += ' ' + horaInicio2 // falta fichar la segunda parte
        this.filterR.fechaFin += ' ' + horaFin2
      } else {
        this.filterR.fechaInicio += ' ' + horaInicio1 // falta fichar la segunda parte
        this.filterR.fechaFin += ' ' + horaFin1
      }
    },
    borrarRegistro () {
      this.$q.dialog({
        title: 'Confirmar',
        message: '¿ Borrar este registro ?',
        ok: true,
        cancel: true,
        persistent: true
      }).onOk(() => {
        this.$emit('deleteRecord', this.filterR)
      })
    },
    saveRecord () {
      if (this.filterR.fechaInicio.length < 18) this.filterR.fechaInicio += ':00' // añado segundos
      if (this.filterR.fechaFin.length < 18) this.filterR.fechaFin += ':00' // añado segundos
      this.$emit('saveRecord', this.filterR) // lo captura accionesMain
    },
    formatDate (pdate) {
      return date.formatDate(pdate, 'DD-MM-YYYY')
    },
    formatHour (pdate) {
      return date.formatDate(pdate, 'HH:mm')
    }
  },
  mounted () {
    this.filterR = Object.assign({}, this.value) // asignamos valor del parametro por si viene de otro tab
    if (this.value.registroNuevo === true) this.crearRegistro()
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
