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
      <q-scroll-area style="height: calc(100vh - 170px); margin-top: 210px; border-right: 1px solid #ddd">
        <q-list>
          <q-item v-for="link in menuItems" :key="link.title"
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
        </q-list>
      </q-scroll-area>
      <q-img class="absolute-top" src="~assets/VIDA_color.jpg" >
      </q-img>
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
          link: {
            name: 'Activos',
            label: 'Activos'
          }
        },
        {
          title: 'Valoraciones',
          // caption: '',
          icon: 'euro_symbol',
          link: {
            name: 'valoraciones',
            label: 'Valoraciones'
          }
        },
        {
          title: 'Dashboard',
          // caption: 'github.com/quasarframework',
          icon: 'dashboard',
          link: {
            name: 'dashboard',
            label: 'Dashboard'
          }
        },
        {
          title: 'Facturas',
          // caption: 'github.com/quasarframework',
          icon: 'description',
          link: {
            name: 'Facturas',
            label: 'Facturas'
          }
        },
        {
          title: 'Entidades',
          icon: 'business',
          link: {
            name: 'Entidades',
            label: 'Entidades'
          }
        },
        {
          title: 'Acciones',
          icon: 'list',
          link: {
            name: 'Acciones',
            label: 'Acciones'
          }
        }
      ]
    }
  },
  computed: {
    ...mapState('login', ['user'])
  },
  methods: {
    ...mapActions('tabs', ['addTab']),
    ...mapActions('login', ['desconectarLogin']),
    openForm (link) {
      this.addTab([link.name, link.label, {}, 1])
    },
    desconectar () {
      this.desconectarLogin()
    }
  }
}
</script>

<style>
  @media screen and (min-width: 768px) {
    .q-footer {
      display: none;
    }
  }

  .q-drawer .q-router-link--exact-active {
    color: white !important;
  }
</style>
