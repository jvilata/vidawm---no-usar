  <!-- componente principal de definicion de formularios. Se apoya en otros 2 componentes: Filter y Grid -->
  <template>
    <div style="height: calc(100vh - 105px)">
    <q-layout view="hhh lpR fFf" container>
      <q-header class="bg-white">
      <q-item clickable v-ripple @click="expanded = !expanded" class="q-ma-md q-pa-xs bg-blue-grey-1 text-grey-8">
        <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
        <q-item-section avatar>
          <q-icon name="fas fa-filter" />
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-h6">
            {{ listaPersonal.find(per=>per.id===filterRecord.idpersonal) !== undefined ? listaPersonal.find(per=>per.id===filterRecord.idpersonal).nombre: '' }}
          </q-item-label>
          <q-item-label>
            <!-- poner un campo de fiterRecord que exista en este filtro -->
            <small>{{ Object.keys(filterRecord).length > 0 ? filterRecord : 'Pulse para definir filtro' }}</small>
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
      </q-header>

      <q-page-container>
        <q-page>
      <q-item class="row q-ma-xs q-pa-xs">
        <q-list v-if="!registrosSeleccionados.noHayDatos" bordered flat separator class="rounded-borders col">
          <q-item clickable v-ripple v-for="(record, key) in registrosSeleccionados" :key="key" @click="editar(record)">
            <q-item-section >
              <div class="col text-center text-grey-8 text-subtitle1">
                {{ record.tipoJornada + ' (' + stringDif(record.fechaInicio, record.fechaFin) + ') - ' + record.estado }}
              </div>
                <span class="text-center text-grey-8 text-caption text-weight-light">
                  {{ formatDate(record.fechaInicio) + ' - ' + formatDate(record.fechaFin) }}
                </span>
            </q-item-section>
          </q-item>
        </q-list>
        <q-banner v-else class="bg-white text-grey-8 col text-center">
          No hay permisos concedidos.
        </q-banner>
      </q-item>
      <q-dialog v-model="expandedReg"  >
        <!-- formulario con campos de filtro -->
        <fichajesRegistro
          :value="recordToSubmit"
          @saveRecord="saveRecord"
          @deleteRecord="deleteRecord"
          @hide="expandedReg = !expandedReg"
        />
      </q-dialog>
      <q-dialog v-model="expanded"  >
        <!-- formulario con campos de filtro -->
        <fichajesFilter
          :value="filterRecord"
          @input="(value) => Object.assign(filterRecord, value)"
          @getRecords="getRecords"
          @hide="expanded = !expanded"
        />
      </q-dialog>
        </q-page>
      </q-page-container>

      <q-footer class="bg-white">
      <div class="row q-mb-sm" >
        <q-item v-if="registrosTotales.length>0" class="col-12 q-ma-xs q-pa-xs">
          <q-list bordered flat separator class="rounded-borders col bg-green-1">
            <q-item v-ripple v-for="(record, key) in registrosTotales" :key="key">
              <q-item-section >
                <div class="col text-center text-grey-8 text-subtitle1">
                  <b>{{ record.tipoJornada + ' - '+ record.estado + ' (' + (record.dias>0?record.dias+'d ':'')+record.horas+'h '+(record.minutos>0?record.minutos+'m':'') + ')' }}</b>
                </div>
              </q-item-section>
            </q-item>
          </q-list>
        </q-item>
        <q-btn icon="add" class="col-6" label="Registrar Jornada/Permiso" color="primary" @click="registrarNuevo"/>
        <q-btn icon="email" class="col-6" label="Enviar PDF a Email" color="primary" @click="enviarEmail(filterRecord)"/>
      </div>
      </q-footer>
    </q-layout>

    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { date } from 'quasar'
import fichajesRegistro from 'components/Fichajes/fichajesRegistro.vue'
import fichajesFilter from 'components/Fichajes/fichajesFilter.vue'
import { headerFormData } from 'boot/axios.js'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      nomFormulario: 'Fichajes',
      expanded: false,
      expandedReg: false,
      filterRecord: {},
      registrosSeleccionados: [],
      registrosTotales: [],
      recordToSubmit: {}
    }
  },
  methods: {
    ...mapActions('personal', ['loadPersonal']),
    getRecords (filter) {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      Object.assign(this.filterRecord, filter) // no haría falta pero así obliga a refrescar el componente para que visulice el filtro
      var objFilter = Object.assign({}, filter)
      if (objFilter.fechaD) objFilter.fechaD = objFilter.fechaD.substring(0, 10) + 'T00:00:00'
      if (objFilter.fechaH) objFilter.fechaH = objFilter.fechaH.substring(0, 10) + 'T00:00:00'
      return this.$axios.get('fichajes/bd_fichajes.php/findFichajesFilter', { params: objFilter })
        .then(response => {
          this.registrosTotales = []
          if (response.data.length > 0) {
            this.registrosSeleccionados = response.data
            this.$axios.get('fichajes/bd_fichajes.php/comprobarHoras', { params: objFilter })
              .then(response => {
                this.registrosTotales = response.data
              })
          } else this.registrosSeleccionados.noHayDatos = true
          this.expanded = false
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    enviarEmail (filter) {
      filter.email = (this.listaPersonal.find(per => per.id === filter.idpersonal) !== undefined ? this.listaPersonal.find(per => per.id === filter.idpersonal).email : this.user.pers.email)
      if (filter.fechaD) filter.fechaD = filter.fechaD.substring(0, 10) + 'T00:00:00'
      if (filter.fechaH) filter.fechaH = filter.fechaH.substring(0, 10) + 'T00:00:00'
      this.$axios.get('fichajes/pdf_fichajes.php', { params: filter })
        .then(response => {
          this.$q.dialog({ title: 'Atencion', message: 'Se ha enviado mail a ' + filter.email })
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    registrarNuevo () {
      if (this.registrosSeleccionados.length > 0) this.recordToSubmit = this.registrosSeleccionados[0] // basado en el primer registro de la lista
      else this.recordToSubmit = {}
      this.recordToSubmit.registroNuevo = true
      this.expandedReg = true
    },
    editar (record) {
      this.recordToSubmit = record
      this.recordToSubmit.registroNuevo = false
      this.expandedReg = true
    },
    saveRecord (record) {
      var formData = new FormData()
      for (var key in record) {
        formData.append(key, record[key])
      }
      return this.$axios.post('fichajes/bd_fichajes.php/guardarBD', formData, headerFormData)
        .then(response => {
          if (response.data.success) {
            this.getRecords(this.filterRecord)
            this.expandedReg = false
          } else {
            this.$q.dialog({ title: 'Error', message: response.data.failure })
          }
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    deleteRecord (record) {
      this.$axios.delete(`fichajes/bd_fichajes.php/fichajes/${record.id}`, JSON.stringify({ id: record.id }))
        .then(response => {
          this.getRecords(this.filterRecord)
          this.expandedReg = false
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    stringDif (fi, ff) {
      var dias = date.getDateDiff(ff, fi, 'days')
      var horas = Math.abs(dias * 24 - date.getDateDiff(ff, fi, 'hours'))
      var minutos = Math.abs(dias * 24 * 60 + horas * 60 - date.getDateDiff(ff, fi, 'minutes'))
      var str1 = ''
      if (dias > 0) str1 += dias + ' dia '
      if (horas > 0) str1 += horas + ' horas '
      if (minutos > 0) str1 += minutos + 'min '
      if (dias === 0 && horas === 0 && minutos === 0) str1 = '0 horas'
      return str1
    },
    formatDate (d) {
      return date.formatDate(d, 'DD-MM-YYYY HH:mm')
    }
  },
  computed: {
    ...mapState('login', ['user']), // importo state.user desde store-login
    ...mapState('personal', ['listaPersonal'])
  },
  mounted () {
    if (this.listaPersonal.length === 0) this.loadPersonal(this.user.codEmpresa)
    this.filterRecord = { codEmpresa: this.user.codEmpresa, idpersonal: this.user.pers.id, ultimo: '1' }
    this.getRecords(this.filterRecord)
  },
  components: {
    fichajesRegistro: fichajesRegistro,
    fichajesFilter: fichajesFilter
  }
}
</script>
