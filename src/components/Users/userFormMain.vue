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
              <q-badge v-if="tab.link.name==='personalGridMovimientos' && numMov>0" color="red" text-color="white" floating >
              {{ numMov }}
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
      numFacturas: 0,
      numAcciones: 0,
      menuItems: [
        {
          title: 'General',
          link: { name: 'personalForm' }
        },
        {
          title: 'Movimientos',
          link: { name: 'personalGridMovimientos' }
        }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user'])
  },
  methods: {
    getCounters () {
      this.getNumMov()
    },
    getNumMov () {
      var objFilter = { tipoObjeto: 'N', idObjeto: this.value.id }
      return this.$axios.get('movimientos/bd_movimientos.php/movimientos', { params: objFilter })
        .then(response => {
          this.numMov = response.data.length
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
