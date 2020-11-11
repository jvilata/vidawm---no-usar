  <template>
    <q-card flat>
        <q-tabs
          v-model="localTab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-route-tab v-for="(tab, index) in tabs" :key="index" :name="tab.params.id" :label="tab.label" :to="tab"  />
        </q-tabs>

        <q-separator />
        <q-tab-panels v-model="localTab" animated>
          <q-tab-panel v-for="(tab, index) in tabs" :key="index" :name="tab.params.id"  class="q-pa-none">
            <router-view  @changeTab="(value) => updateTabData([tab, value])" @close="removeTab(tab)" @contadorAcciones="(value) => $emit('contadorAcciones', value)"/>
          </q-tab-panel>
        </q-tab-panels>
    </q-card>
</template>

<script>
import { mapActions, mapState } from 'vuex'
export default {
  data () {
    return {
    }
  },
  computed: {
    ...mapState('tabs', ['tabs', 'ltab']),
    ...mapState('tablasAux', ['listaUsers']),
    localTab: {
      get () {
        return this.ltab
      },
      set (value) {
        this.setltab(value)
      }
    }
  },
  methods: {
    ...mapActions('tabs', ['addTab', 'updateTabData', 'removeTab', 'setltab']),
    ...mapActions('tablasAux', ['loadTablasAux']),
    updateTabData1 ([ptab, pvalue]) {
      console.log(this.tabs, this.ltab)
      console.log('maintasb ', ptab, pvalue)
    }
  }
}
</script>
