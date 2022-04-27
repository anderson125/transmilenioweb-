import Vue from 'vue';
import Router from 'vue-router';
import store from '../store/index'

// Components
import LoginComponent from '../components/login/Home';
import ParamsHomeComponent from '../components/params/Home';
import WebUserComponent from '../components/user/web/Home';
import BikerUserComponent from '../components/user/biker/Home';
import ParkingHomeComponent from '../components/parking/Home';
import BicyHomeComponent from '../components/bicy/Home';
import VisitHomeComponent from '../components/visit/Home';
import PermissionsHomeComponent from '../components/permissions/Home';
import InventoryHomeComponent from '../components/inventory/Home';
import YoungHomeComponent from '../components/young/Home';
import ReportHomeComponent from '../components/report/Home';
import SmsComponent from '../components/sms/Home';
import QrComponent from '../components/qr/Home';
import HelpComponent from '../components/help/Home';
import QrPrintingComponent from '../components/qr/printing';
import QrPrintingDetailedComponent from '../components/qr/printingDetaileds';
import PageNotFoundComponent from '../components/404/Home';

Vue.use(Router);

let router = new Router({
  mode: 'history',
  routes: [
  {
    path: '/report',
    name: 'report',
    meta: {
      requiresAuth: true
    },
    component: ReportHomeComponent
  },
  {
    path: '/qr',
    name: 'qr',
    meta: {
      requiresAuth: true
    },
    component: QrComponent
  },
  {
    path: '/help',
    name: 'help',
    meta: {
      requiresAuth: true
    },
    component: HelpComponent
  },
  {
    path: '/qr/print',
    name: 'printQr',
    meta: {
      requiresAuth: true
    },
    component: QrPrintingComponent
  },
  {
    path: '/qr/printDetaileds',
    name: 'printDQr',
    meta: {
      requiresAuth: true
    },
    component: QrPrintingDetailedComponent
  },
  
  {
    path: '/permissions',
    name: 'permissions',
    meta: {
      requiresAuth: true
    },
    component: PermissionsHomeComponent
  },
  {
    path: '/login',
    name: 'login',
    component: LoginComponent
  },
  {
    path: '/young',
    name: 'young',
    meta: {
      requiresAuth: true
    },
    component: YoungHomeComponent
  },
  {
    path: '/inventory',
    name: 'inventory',
    meta: {
      requiresAuth: true
    },
    component: InventoryHomeComponent
  },
  {
    path: '/visit',
    name: 'visit',
    meta: {
      requiresAuth: true
    },
    component: VisitHomeComponent
  },
  {
    path: '/bicy',
    name: 'bicy',
    meta: {
      requiresAuth: true
    },
    component: BicyHomeComponent
  },
  {
    path: '/parking',
    name: 'parking',
    meta: {
      requiresAuth: true
    },
    component: ParkingHomeComponent
  },
  {
    path: '/params',
    name: 'params',
    meta: {
      requiresAuth: true
    },
    component: ParamsHomeComponent
  },
  {
    path: '/user/web',
    name: 'user.web',
    meta: {
      requiresAuth: true
    },
    component: WebUserComponent
  },
  {
    path: '/user/biker',
    name: 'user.biker',
    meta: {
      requiresAuth: true
    },
    component: BikerUserComponent
  },
  {
    path: '/sms',
    name: 'sms',
    meta: {
      requiresAuth: true
    },
    component: SmsComponent
  },
  {
    path: "*",
    name: '404',
    meta: {
      requiresAuth: true
    },
    component: PageNotFoundComponent
  }

  ],
});
router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!store.getters.currentAuth) {
      next('/login')
      return
    } else {
      next()
    }
  } else {
    next()
  }


})

export default router
