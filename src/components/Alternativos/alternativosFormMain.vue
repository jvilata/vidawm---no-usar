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
            </q-route-tab>
          </q-tabs>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
export default {
  props: ['value', 'id', 'keyValue'], // se pasan como parametro desde mainTabs. value = { registrosSeleccionados: [], filterRecord: {} }
  data () {
    return {
      ltab: '',
      title: 'Alternativos',
      menuItems: [
        {
          title: 'Proyección',
          link: { name: 'alternativosMain' }
        },
        {
          title: 'Comparativas',
          link: { name: 'alternativosGridComp' }
        }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user']),
    ...mapState('tabs', ['tabs', 'dataTabs'])
  },
  methods: {
    ...mapActions('tabs', ['removeDataTab'])
  },
  mounted () {
    if (this.dataTabs['altGridComp-1']) {
      this.$router.replace({ name: this.menuItems[1].link.name, params: { id: this.id, value: this.value } }).catch(() => {})
    } else {
      this.$router.replace({ name: this.menuItems[0].link.name, params: { id: this.id, value: this.value } }).catch(() => {})
    }
  },
  destroyed () {
    if (!this.tabs['alternativos-1']) { // cuando cierro el tab de alternativos borro datos temporales
      this.removeDataTab('altGridComp-1')
    }
  }
}
</script>

<style>

</style>
