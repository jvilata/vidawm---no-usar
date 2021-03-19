<template>
  <q-layout view="lHh lpR fFf" class="shadow-2 rounded-borders">
    <q-header elevated >
      <q-toolbar>
        <q-btn flat @click="leftDrawerOpen = !leftDrawerOpen" round dense icon="menu" />
        <div class="items-center no-wrap absolute-center">
          <div class="text-subtitle1">{{ nomAplicacion }}</div>
          <div class="text-caption">{{ user.nomEmpresa }}</div>
        </div>
        <div class="q-gutter-sm q-pr-md row items-center no-wrap absolute-right">
          <q-btn round dense flat color="grey-8" icon="notifications" @click="openForm({ name: 'Acciones', label: 'Acciones' })">
            <q-badge v-if="accionesPendientes>0" color="red" text-color="white" floating >
              {{ accionesPendientes }}
            </q-badge>
            <q-tooltip>Acciones Pendientes</q-tooltip>
          </q-btn>
          <q-btn round flat class="bg-red-9 text-weight-light">
            <q-avatar size="40px">
              {{ user.user.email.substr(0,2) }}
            </q-avatar>
            <q-tooltip>Account</q-tooltip>
            <q-menu auto-close :offset="[110, 0]">
              <q-card>
                <q-card-section>
                  <div class="row">
                    <div class="col-4">
                      <q-avatar round flat size="80px" class="bg-red-9 text-white text-weight-light">
                        {{ user.user.email.substr(0,2).toUpperCase() }}
                      </q-avatar>
                    </div>
                    <div class="col">
                      <div class="text-weight-bold">{{ user.pers.nombre }}</div>
                      <div>{{ user.user.email }}</div>
                      <q-btn flat class="text-weight-light" color="primary" @click="desconectar">Desconectar</q-btn>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </q-menu>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>
    <q-drawer v-model="leftDrawerOpen"
        show-if-above bordered
        content-class="bg-grey-3"
        :mini="miniState"
        @mouseover="miniState = false"
        @mouseout="miniState = true"
        :breakpoint="767"
        :width="220">
      <q-scroll-area style="height: calc(100vh - 210px); margin-top: 210px; border-right: 1px solid #ddd">
        <q-list>
          <div v-for="link in menuItemsFilter" :key="link.title">
            <q-item
              v-if="link.title !== 'Otros'"
              clickable
              @click.native="openForm(link.link)"
              exact
              class="text-grey-8"  >
              <q-item-section v-if="link.icon" avatar>
                <q-icon :name="link.icon" />
              </q-item-section>

              <q-item-section>
                <q-item-label>{{ link.title }}</q-item-label>
                <q-item-label v-if="link.caption">{{ link.caption }}</q-item-label>
              </q-item-section>
            </q-item>
            <q-expansion-item
              class="text-grey-8"
              v-else
              icon="settings"
              label="Otros" >
              <div v-for="link in otros" :key="link.title">
                <q-item clickable @click.native="openForm(link.link)" exact>
                  <q-item-section><!--Títulos del DRAWER -->
                    <q-item-label
                      v-ripple
                      clickable
                      v-if="link.title" >
                      {{ link.title  }}
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </div>
            </q-expansion-item>
          </div>
        </q-list>
      </q-scroll-area>
      <q-img class="absolute-top" src="~assets/VIDA_color.jpg" />
    </q-drawer>
    <q-footer>
      <!-- podemos poner tabs en el pie para dispositivos moviles pero quita pantalla y no me gusta
      <q-tabs>
        <q-route-tab v-for="link in menuItems"
          :key="link.title"
          :icon="link.icon"
          :label="link.title"
          :to="link.link"
          exact />
      </q-tabs> -->
    </q-footer>

    <q-page-container>
      <router-view  @contadorAcciones="(value) => accionesPendientes = value"/>
    </q-page-container>
  </q-layout>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import { openURL } from 'quasar'
export default {
  name: 'MainLayout',
  data () {
    return {
      nomAplicacion: 'Wealth Management',
      accionesPendientes: 0,
      leftDrawerOpen: false,
      miniState: false,
      menuItems: [
        {
          title: 'Activos',
          // caption: 'github.com/quasarframework',
          icon: 'account_balance',
          rol: '0',
          link: {
            name: 'Activos',
            label: 'Activos'
          }
        },
        {
          title: 'Valoraciones',
          // caption: '',
          icon: 'euro_symbol',
          rol: '1',
          link: {
            name: 'valoraciones',
            label: 'Valoraciones'
          }
        },
        {
          title: 'Alternativos',
          // caption: '',
          icon: 'api',
          rol: '1',
          link: {
            name: 'alternativos',
            label: 'Alternativos'
          }
        },
        {
          title: 'Dashboard',
          rol: '1',
          icon: 'dashboard',
          link: {
            name: 'dashboard',
            label: 'Dashboard'
          }
        },
        {
          title: 'Facturas',
          rol: '0',
          icon: 'description',
          link: {
            name: 'Facturas',
            label: 'Facturas'
          }
        },
        {
          title: 'Pagos',
          rol: '1',
          icon: 'payments',
          link: {
            name: 'Pagos',
            label: 'Pagos'
          }
        },
        {
          title: 'Entidades',
          rol: '0',
          icon: 'business',
          link: {
            name: 'Entidades',
            label: 'Entidades'
          }
        },
        {
          title: 'Fichajes',
          rol: '0',
          icon: 'credit_card',
          link: {
            name: 'fichajesMain',
            label: 'Fichajes'
          }
        },
        {
          title: 'Otros',
          rol: '1',
          icon: 'chat',
          link: {
            name: 'otros',
            label: 'Otros'
          }
        },
        {
          title: 'Ayuda',
          rol: '0',
          icon: 'help',
          link: {
            name: 'help',
            label: 'Ayuda'
          }
        }
      ],
      otros: [
        {
          title: 'Personal',
          icon: 'group',
          link: {
            name: 'personalMain',
            label: 'Personal'
          }
        },
        {
          title: 'Notas de Gasto',
          icon: 'euro_symbol',
          link: {
            name: 'notasMain',
            label: 'Notas de Gasto'
          }
        },
        {
          title: 'Donaciones',
          rol: '0',
          icon: 'redeem',
          link: {
            name: 'donativosMain',
            label: 'Donaciones'
          }
        },
        {
          title: 'Acciones',
          rol: '0',
          icon: 'list',
          link: {
            name: 'Acciones',
            label: 'Acciones'
          }
        },
        {
          title: 'Tablas Auxiliares',
          icon: 'chat',
          link: {
            name: 'tablasMain',
            label: 'Tablas Auxiliares'
          }
        },
        {
          title: 'Usuarios',
          icon: 'login',
          link: {
            name: 'userMain',
            label: 'Usuarios'
          }
        },
        {
          title: 'App Clásica',
          icon: 'mobile_off',
          link: {
            name: 'openClassic',
            label: 'App Clásica'
          }
        }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user']),
    menuItemsFilter () {
      var arr = []
      this.menuItems.forEach(element => {
        if ((this.user.pers.userRol === '2' && element.title === 'Fichajes') || // solo fichaje
          (this.user.pers.userRol === '1') || // admin
          (this.user.pers.userRol === '0' && element.rol === '0')) {
          arr.push(element)
        }
      })
      return arr
    }
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    ...mapActions('login', ['desconectarLogin']),
    openForm (link) {
      if (link.name === 'help') {
        var strUrl = 'https://vidawm.com/ayuda-gestion-activos/'
        if (window.cordova === undefined) { // desktop
          openURL(strUrl)
        } else { // estamos en un disp movil
          window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
        }
      } else if (link.name === 'openClassic') {
        this.openClassic()
      } else {
        this.addTab([link.name, link.label, {}, 1])
      }
    },
    openClassic () {
      var strUrl = 'https://vidawm.com/privado/index_sencha.html'
      if (window.cordova === undefined) { // desktop
        openURL(strUrl)
      } else { // estamos en un disp movil
        window.cordova.InAppBrowser.open(strUrl, '_system') // openURL
      }
    },
    desconectar () {
      this.desconectarLogin()
    }
  },
  mounted () {
    // llamo a la action->addTab del store->tabs y param: ['acciones','acciones',{},1]
    if (this.user.pers.userRol !== '2') this.addTab(['Acciones', 'Acciones', {}, 1])
    else this.addTab(['fichajesMain', 'Fichajes', {}, 1])
  }
}
</script>

<style>
  .q-drawer .q-router-link--exact-active {
    color: white !important;
  }
</style>
