<template>
  <div style="height: calc(100vh - 105px)">
    <q-card>
      <q-card-section   class="q-pa-xs">
            <q-item class="q-pa-xs bg-indigo-1 text-grey-8">
              <!-- cabecera de formulario. Botón de busqueda y cierre de tab -->
              <q-item-section avatar>
                <q-icon name="edit" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-h6">
                  {{ title }} / {{ value.nomEntidad }}
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
    </q-card>
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
              <q-badge v-if="tab.link.name==='facturasGridMovimientos' && numMov>0" color="red" text-color="white" floating >
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
      title: 'Facturas',
      numRentab: 0,
      numMov: 0,
      numFacturas: 0,
      numAcciones: 0,
      menuItems: [
        {
          title: 'General',
          link: { name: 'facturasForm' }
        },
        {
          title: 'Movimientos',
          link: { name: 'facturasGridMovimientos' }
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
      this.numMov = this.value.nummov // no hace falta hacer la consulta porque ya me la pasa el backend
      /* var objFilter = { tipoObjeto: 'F', idObjeto: this.value.id }
      return this.$axios.get('movimientos/bd_movimientos.php/movimientos', { params: objFilter })
        .then(response => {
          this.numMov = response.data.length
        })
        .catch(error => {
          console.log(error)
        }) */
    }
  },
  mounted () {
    this.getCounters()
    this.$router.replace({ name: this.menuItems[0].link.name, params: { id: this.id, value: this.value } })
  }
}
</script>

<style>

</style>
