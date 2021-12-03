
const routes = [
  {
    path: '/',
    component: () => import('layouts/LayoutLogin.vue'),
    children: [
      { path: '', component: () => import('pages/PageLogin.vue') }
    ]
  },
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      {
        path: 'mainTabs',
        component: () => import('components/MainTabs/mainTabs.vue'),
        children: [
          { path: 'Acciones', name: 'Acciones', component: () => import('components/Acciones/accionesMain.vue'), props: true },
          { path: 'donativosMain', name: 'donativosMain', component: () => import('components/Donativos/donativosMain.vue'), props: true },
          { path: 'Pagos', name: 'Pagos', component: () => import('components/Movimientos/pagosMain.vue'), props: true },
          { path: 'Activos', name: 'Activos', component: () => import('components/Activos/activosMain.vue'), props: true },
          {
            path: 'ActivosFormMain/:id',
            name: 'activosFormMain',
            component: () => import('components/Activos/activosFormMain.vue'),
            props: true,
            children: [
              { path: 'activosForm', name: 'activosForm', component: () => import('components/Activos/activosForm.vue'), props: true },
              { path: 'activosClasificacionGrid', name: 'activosClasificacionGrid', component: () => import('components/Activos/activosClasificacionGrid.vue'), props: true },
              { path: 'activosGridRentabAnual', name: 'activosGridRentabAnual', component: () => import('components/Activos/activosGridRentabAnual.vue'), props: true },
              { path: 'activosGridMovimientos', name: 'activosGridMovimientos', component: () => import('components/Movimientos/movimientosGrid.vue'), props: true },
              { path: 'documentosGrid', name: 'documentosGrid', component: () => import('components/Documentos/documentosGrid.vue'), props: true },
              { path: 'activosAlternativosGrid', name: 'activosAlternativosGrid', component: () => import('components/Activos/activosAlternativosGrid.vue'), props: true },
              { path: 'facturasGrid', name: 'facturasGrid', component: () => import('components/Facturas/facturasGrid.vue'), props: true },
              { path: 'activosEvolucionValor', name: 'activosEvolucionValor', component: () => import('components/Activos/activosEvolucionValor.vue'), props: true },
              { path: 'activosAccionesGrid', name: 'activosAccionesGrid', component: () => import('components/Activos/activosAccionesGrid.vue'), props: true }
            ]
          },
          { path: 'Entidades', name: 'Entidades', component: () => import('components/Entidades/entidadesMain.vue'), props: true },
          {
            path: 'EntidadesFormMain/:id',
            name: 'entidadesFormMain',
            component: () => import('components/Entidades/entidadesFormMain.vue'),
            props: true,
            children: [
              { path: 'entidadesForm', name: 'entidadesForm', component: () => import('components/Entidades/entidadesForm.vue'), props: true },
              { path: 'facturasEntGrid', name: 'facturasEntGrid', component: () => import('components/Facturas/facturasGrid.vue'), props: true },
              { path: 'entidadesEvolucionValor', name: 'entidadesEvolucionValor', component: () => import('components/Entidades/entidadesEvolucionValor.vue'), props: true },
              { path: 'entidadesAccionesGrid', name: 'entidadesAccionesGrid', component: () => import('components/Entidades/entidadesAccionesGrid.vue'), props: true }
            ]
          },
          { path: 'Facturas', name: 'Facturas', component: () => import('components/Facturas/FacturasMain.vue'), props: true },
          {
            path: 'facturasFormMain/:id',
            name: 'facturasFormMain',
            component: () => import('components/Facturas/facturasFormMain.vue'),
            props: true,
            children: [
              { path: 'facturasForm', name: 'facturasForm', component: () => import('components/Facturas/facturasForm.vue'), props: true },
              { path: 'facturasGridMovimientos', name: 'facturasGridMovimientos', component: () => import('components/Movimientos/movimientosGrid.vue'), props: true }
            ]
          },
          { path: 'valoraciones', name: 'valoraciones', component: () => import('components/Valoraciones/valoracionesMain.vue'), props: true },
          { path: 'alternativos', name: 'alternativos', component: () => import('components/Alternativos/alternativosMain.vue'), props: true },
          {
            path: 'dashboard',
            name: 'dashboard',
            component: () => import('components/Dashboard/dashboardMain.vue'),
            props: true,
            children: [
              { path: 'panelDatos', name: 'panelDatos', component: () => import('components/Dashboard/dashboardPanelDatos.vue'), props: true },
              { path: 'gridPlanif', name: 'gridPlanif', component: () => import('components/Dashboard/dashboardGridPlanifInversion.vue'), props: true },
              { path: 'resumenTipos', name: 'resumenTipos', component: () => import('components/Dashboard/dashboardResumenPatrimonioMain.vue'), props: true },
              { path: 'analisisTipos', name: 'analisisTipos', component: () => import('components/Dashboard/dashboardPieChartFiltros.vue'), props: true },
              { path: 'evolucionPatrimonio', name: 'evolucionPatrimonio', component: () => import('components/Dashboard/dashboardEvolucionPatrimonio.vue'), props: true },
              { path: 'evolucionClases', name: 'evolucionClases', component: () => import('components/Dashboard/dashboardEvolucionClasesPatrimonio.vue'), props: true },
              { path: 'patrimonioEntidad', name: 'patrimonioEntidad', component: () => import('components/Dashboard/dashboardPatrimonioEntidad.vue'), props: true },
              { path: 'patrimonioComprometido', name: 'patrimonioComprometido', component: () => import('components/Dashboard/dashboardPatrimonioComprometido.vue'), props: true }
            ]
          },
          { path: 'tablasMain', name: 'tablasMain', component: () => import('components/TablasAuxiliar/tablasMain.vue'), props: true },
          { path: 'fichajesMain', name: 'fichajesMain', component: () => import('components/Fichajes/fichajesMain.vue'), props: true },
          { path: 'userMain', name: 'userMain', component: () => import('components/Users/userMain.vue'), props: true },
          { path: 'notasMain', name: 'notasMain', component: () => import('components/Notas/notasMain.vue'), props: true },
          { path: 'personalMain', name: 'personalMain', component: () => import('components/Personal/personalMain.vue'), props: true },
          {
            path: 'personalFormMain/:id',
            name: 'personalFormMain',
            component: () => import('components/Personal/personalFormMain.vue'),
            props: true,
            children: [
              { path: 'personalForm', name: 'personalForm', component: () => import('components/Personal/personalForm.vue'), props: true },
              { path: 'personalGridMovimientos', name: 'personalGridMovimientos', component: () => import('components/Movimientos/movimientosGrid.vue'), props: true }
            ]
          }
        ]
      },
      {
        path: 'sinTabs',
        component: () => import('components/MainTabs/sinTabs.vue')
      }
    ]
  }
]

// Always leave this as last one
if (process.env.MODE !== 'ssr') {
  routes.push({
    path: '*',
    component: () => import('pages/Error404.vue')
  })
}

export default routes
