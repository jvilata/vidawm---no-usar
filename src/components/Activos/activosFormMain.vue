<template>
  <div style="height: calc(100vh - 105px)">
          <q-tab-panels v-model="ltab" animated >
              <q-tab-panel v-for="(tab, index) in menuItems" :key="index" :name="tab.link.name"  class="q-pa-none">
                <router-view @close="$emit('close')"/>
              </q-tab-panel>
          </q-tab-panels>
          <!-- podemos poner tabs en el pie para dispositivos moviles pero quita pantalla y no me gusta bg-primary text-white -->
          <q-tabs v-model="ltab" dense
            class="absolute-bottom bg-primary text-white">
            <q-route-tab v-for="(tab,index) in menuItems"
              no-caps
              :key="index"
              :label="tab.title"
              :name="tab.link.name"
              :to="{ name: tab.link.name, params: { id: id, value: value } }"
              exact>
              <q-badge v-if="tab.link.name==='activosGridRentabAnual' && numRentab>0" color="red" text-color="white" floating >
              {{ numRentab }}
              </q-badge>
              <q-badge v-if="tab.link.name==='activosGridMovimientos' && numMov>0" color="red" text-color="white" floating >
              {{ numMov }}
              </q-badge>
              <q-badge v-if="tab.link.name==='activosClasificacionGrid' && numMov>0" color="red" text-color="white" floating >
              {{ numClas }}
              </q-badge>
              <q-badge v-if="tab.link.name==='documentosGrid' && numMov>0" color="red" text-color="white" floating >
              {{ numDoc }}
              </q-badge>
              <q-badge v-if="tab.link.name==='facturasGrid' && numFacturas>0" color="red" text-color="white" floating >
              {{ numFacturas }}
              </q-badge>
              <q-badge v-if="tab.link.name==='activosAccionesGrid' && numAcciones>0" color="red" text-color="white" floating >
              {{ numAcciones }}
              </q-badge>
            </q-route-tab>
          </q-tabs>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      ltab: '',
      title: 'Activos',
      numRentab: 0,
      numMov: 0,
      numClas: 0,
      numDoc: 0,
      numFacturas: 0,
      numAcciones: 0,
      menuItems: [
        {
          title: 'General',
          link: { name: 'activosForm' }
        },
        {
          title: 'Clasificacion',
          link: { name: 'activosClasificacionGrid' }
        },
        {
          title: 'Rent.Anual',
          link: { name: 'activosGridRentabAnual' }
        },
        {
          title: 'Documentos',
          link: { name: 'documentosGrid' }
        },
        {
          title: 'Evolución Valor',
          link: { name: 'activosEvolucionValor' }
        },
        {
          title: 'Movimientos',
          link: { name: 'activosGridMovimientos' }
        },
        {
          title: 'Alternativos',
          link: { name: 'activosAlternativosGrid' }
        },
        {
          title: 'Facturas',
          link: { name: 'facturasGrid' }
        },
        {
          title: 'Acciones',
          link: { name: 'activosAccionesGrid' }
        }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user'])
  },
  methods: {
    getCounters () {
      this.getNumRentab()
      this.getNumMov()
      this.getNumDoc()
      this.getNumClas()
      this.getFacturas()
      this.getAcciones()
    },
    getNumRentab () {
      var objFilter = { idActivo: this.value.id }
      return this.$axios.get('activos/bd_act_rentabEspAnual.php/findAct_RentabEspAnualFilter', { params: objFilter })
        .then(response => {
          this.numRentab = response.data.length
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getNumMov () {
      var objFilter = { tipoObjeto: 'A', idObjeto: this.value.id }
      return this.$axios.get('movimientos/bd_movimientos.php/movimientos', { params: objFilter })
        .then(response => {
          this.numMov = response.data.length
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getNumClas () {
      var objFilter = { idActivo: this.value.id }
      return this.$axios.get('activos/bd_act_clasificacion.php/findAct_clasificacionFilter', { params: objFilter })
        .then(response => {
          this.numClas = response.data.length
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getNumDoc () {
      var objFilter = { tipoObjeto: 'A', idObjeto: this.value.id }
      return this.$axios.get('documentos/bd_documentos.php/documentos', { params: objFilter })
        .then(response => {
          this.numDoc = response.data.length
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getFacturas () {
      var objFilter = { codEmpresa: this.user.codEmpresa, tipoObjeto: 'A', idObjeto: this.value.id }
      return this.$axios.get('facturas/bd_facturas.php/findFacturasFilter', { params: objFilter })
        .then(response => {
          this.numFacturas = response.data.length
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    },
    getAcciones () {
      // hago la busqueda de registros segun condiciones del formulario Filter que ha lanzado el evento getRecords
      var objFilter = { tipoObjeto: 'A', idObjeto: this.value.id }
      return this.$axios.get('acciones/bd_acciones.php/findAccionesFilter', { params: objFilter })
        .then(response => {
          this.numAcciones = response.data.length
        })
        .catch(error => {
          this.$q.dialog({ title: 'Error', message: error })
        })
    }
  },
  mounted () {
    this.getCounters()
    this.$router.replace({ name: this.menuItems[0].link.name, params: { id: this.id, value: this.value } }).catch(() => {})
  }
}
</script>

<style>

</style>
