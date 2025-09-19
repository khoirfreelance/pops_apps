<template>
  <header class="navbar navbar-light bg-gradient shadow-sm px-3 py-2">
    <!-- Left -->
    <div class="d-flex align-items-center">
      <a class="navbar-brand p-2 ms-5" href="#">
        <img src="/src/assets/tf_reserved_primary.png" alt="Logo" height="30" />
      </a>
      <!-- tombol toggle di kiri kalau expanded -->
      <button
        v-if="!isCollapsed"
        class="btn btn-outline-light ms-3 d-none d-md-inline"
        @click="$emit('toggle-sidebar')"
      >
        <i class="bi bi-list"></i>
      </button>
    </div>

    <!-- Right -->
    <div class="ms-auto d-flex align-items-center gap-3">
      <!-- Notification -->
      <div class="dropdown">
        <button
          class="btn btn-link position-relative p-0 text-white"
          @click="toggleNotification = !toggleNotification"
        >
          <i class="bi bi-bell fs-5"></i>
          <span
            v-if="events.length > 0"
            class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
          ></span>
        </button>

        <!-- Dropdown -->
        <div
          v-if="toggleNotification"
          class="dropdown-menu dropdown-menu-end show mt-2 p-0 notification-menu"
          style="min-width: 260px; max-height: 260px; overflow-y: auto"
        >
          <div class="p-2 border-bottom fw-semibold small text-muted">Upcoming Events</div>

          <div v-if="events.length === 0" class="p-2 text-center text-muted small">No events</div>

          <div v-for="ev in events" :key="ev.id" class="dropdown-item py-2 border-bottom">
            <div class="fw-semibold">{{ ev.title }}</div>
            <small class="text-muted d-block"> {{ ev.date }} {{ ev.time || '' }} </small>
          </div>
        </div>
      </div>

      <!-- User Menu -->
      <div class="dropdown">
        <button
          class="btn btn-link dropdown-toggle p-0 text-white"
          id="userMenu"
          data-bs-toggle="dropdown"
        >
          <i class="bi bi-person-circle fs-5"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li>
            <router-link to="/admin/profile" class="dropdown-item"> Profile </router-link>
          </li>
          <li><hr class="dropdown-divider" /></li>
          <li>
            <button class="dropdown-item text-danger" @click="handleLogout">Logout</button>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script>
import { eventBus } from '@/eventBus'
import Swal from 'sweetalert2'

export default {
  name: 'HeaderAdmin',
  data() {
    return {
      events: [],
      toggleNotification: false,
      storageKey: 'moodle_calendar_events',
    }
  },
  mounted() {
    this.loadEvents()
    eventBus.on('eventsUpdated', this.loadEvents)
  },
  beforeUnmount() {
    eventBus.off('eventsUpdated', this.loadEvents)
  },
  methods: {
    loadEvents() {
      try {
        this.events = JSON.parse(localStorage.getItem(this.storageKey)) || []
      } catch {
        this.events = []
      }
    },
    handleLogout() {
      Swal.fire({
        title: 'Logout?',
        text: 'Anda yakin ingin keluar dari aplikasi?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
      }).then((result) => {
        if (result.isConfirmed) {
          localStorage.clear()
          window.location.href = '/admin/login'
        }
      })
    },
  },
}
</script>

<style scoped>
header {
  z-index: 1050;
}

.bg-gradient {
  background: linear-gradient(90deg, #006341, #6fa287) !important;
}

/* fix posisi dropdown notifikasi */
.dropdown-menu.notification-menu {
  right: auto !important; /* matikan bawaan dropdown-menu-end */
  left: -200px !important; /* geser ke kiri sesuai lebar menu */
}

.dropdown-menu {
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
  font-size: 0.9rem;
}
.dropdown-item:last-child {
  border-bottom: none;
}
</style>
