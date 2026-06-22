// INSECURE STARTER CODE
// Frontend route guard trusts localStorage role. Students should discover this is not real security.

import { createRouter, createWebHistory } from 'vue-router'
import { isLoggedIn, getRole } from '@/utils/auth'

import LoginView from '@/views/LoginView.vue'
import RegisterView from '@/views/RegisterView.vue'
import DashboardView from '@/views/DashboardView.vue'
import MyBmiView from '@/views/MyBmiView.vue'
import AddBmiView from '@/views/AddBmiView.vue'
import StaffBmiMonitorView from '@/views/StaffBmiMonitorView.vue'
import AdminUsersView from '@/views/AdminUsersView.vue'
import InvestigationGuideView from '@/views/InvestigationGuideView.vue'
import UnauthorizedView from '@/views/UnauthorizedView.vue'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
  { path: '/my-bmi', name: 'my-bmi', component: MyBmiView, meta: { requiresAuth: true } },
  { path: '/add-bmi', name: 'add-bmi', component: AddBmiView, meta: { requiresAuth: true } },
  { path: '/staff/bmi-records', name: 'staff-bmi', component: StaffBmiMonitorView, meta: { requiresAuth: true, roles: ['staff', 'admin'] } },
  { path: '/admin/users', name: 'admin-users', component: AdminUsersView, meta: { requiresAuth: true, roles: ['admin'] } },
  { path: '/investigation-guide', name: 'investigation-guide', component: InvestigationGuideView },
  { path: '/unauthorized', name: 'unauthorized', component: UnauthorizedView }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth && !isLoggedIn()) {
    return next('/login')
  }

  // Investigation question:
// This route guard checks role from localStorage.
// Does this protect the backend API, or only hide frontend pages?
// Frontend route guard improves user experience.
// Backend route protection protects data.
  if (to.meta.roles && !to.meta.roles.includes(getRole())) {
    return next('/unauthorized')
  }

  next()
})

export default router
