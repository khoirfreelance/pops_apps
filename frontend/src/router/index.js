import { createRouter, createWebHistory } from 'vue-router'

// Import halaman
import Home from '@/views/Home.vue'
import Dashboard from '@/views/admin/Dashboard.vue'
import Login from '@/views/admin/Login.vue'
import Forgot from '@/views/admin/Forgot.vue'
import Config from '@/views/admin/Config.vue'
import Children from '@/views/admin/Children.vue'
import Pregnancy from '@/views/admin/Pregnancy.vue'
import Bride from '@/views/admin/Bride.vue'
import Schedule from '@/views/admin/Schedule.vue'
import Family from '@/views/admin/Family.vue'
import Cadre from '@/views/admin/Cadre.vue'
import Membership from '@/views/admin/Membership.vue'
import Profile from '@/views/admin/Profile.vue'
import Chart from '@/views/admin/Chart.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: Home,
  },
  {
    path: '/admin',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/anak',
    name: 'children',
    component: Children,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/hamil',
    name: 'pregnancy',
    component: Pregnancy,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/catin',
    name: 'bride',
    component: Bride,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/jadwal',
    name: 'schedule',
    component: Schedule,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/keluarga',
    name: 'family',
    component: Family,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/kader',
    name: 'cadre',
    component: Cadre,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/tpk',
    name: 'membership',
    component: Membership,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/grafik',
    name: 'chart',
    component: Chart,
    meta: { requiresAuth: true },
  },
  //auth
  {
    path: '/admin/login',
    name: 'login',
    component: Login,
  },
  {
    path: '/admin/forgot',
    name: 'forgot',
    component: Forgot,
  },
  //administrator
  {
    path: '/admin/config',
    name: 'config',
    component: Config,
    meta: { requiresAuth: true },
  },
  {
    path: '/admin/profile',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Route Guard
router.beforeEach((to, from, next) => {
  const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true'

  if (to.meta.requiresAuth && !isLoggedIn) {
    // kalau belum login, lempar ke login
    next({ name: 'login' })
  } else if (to.name === 'login' && isLoggedIn) {
    // kalau sudah login, jangan balik ke login
    next({ name: 'dashboard' })
  } else {
    next()
  }
})


export default router
