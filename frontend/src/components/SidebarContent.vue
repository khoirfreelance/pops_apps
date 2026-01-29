<template>
  <div :class="['sidebar-container', isCollapsed ? 'sidebar-collapsed' : '']">
    <!-- Menu List -->
    <ul class="nav flex-column mt-3 px-2">
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Beranda"
        ref="tooltipItem"
      >
        <router-link to="/admin" class="nav-link" :class="{ active: isActive('/admin') }">
          <i class="bi bi-house"></i>
          <span v-if="!isCollapsed">Beranda</span>
        </router-link>
      </li>

      <li class="menu-section mt-4" v-if="!isCollapsed">Menu</li>
      <li class="border-bottom border-primary mt-3" v-if="isCollapsed"></li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Gizi Anak"
        ref="tooltipItem"
      >
        <router-link to="/admin/anak" class="nav-link" :class="{ active: isActive('/admin/anak') }">
          <i class="fa-solid fa-baby"></i>
          <span v-if="!isCollapsed">Gizi Anak</span>
        </router-link>
      </li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Ibu Hamil"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/hamil"
          class="nav-link"
          :class="{ active: isActive('/admin/hamil') }"
        >
          <i class="fa-solid fa-person-pregnant"></i>
          <span v-if="!isCollapsed">Ibu Hamil</span>
        </router-link>
      </li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Calon Pengantin"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/catin"
          class="nav-link"
          :class="{ active: isActive('/admin/catin') }"
        >
          <i class="bi bi-people-fill"></i>
          <span v-if="!isCollapsed">Calon Pengantin</span>
        </router-link>
      </li>
      <li class="border-bottom border-primary mt-3" v-if="isCollapsed"></li>
      <li class="menu-section mt-4" v-if="!isCollapsed">Master Data</li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Kelola"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/import"
          class="nav-link"
          :class="{ active: isActive('/admin/import') }"
        >
            <i class="bi bi-cloud-upload-fill"></i>
          <span v-if="!isCollapsed"> Kelola</span>
        </router-link>
      </li>

      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Keluarga"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/keluarga"
          class="nav-link"
          :class="{ active: isActive('/admin/keluarga') }"
        >
          <i class="fa-solid fa-people-roof"></i>
          <span v-if="!isCollapsed">Keluarga</span>
        </router-link>
      </li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Admin / Kader"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/kader"
          class="nav-link"
          :class="{ active: isActive('/admin/kader') }"
        >
          <i class="bi bi-file-person"></i>
          <span v-if="!isCollapsed">Admin / Kader</span>
        </router-link>
      </li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Keanggotaan TPK"
        ref="tooltipItem"
      >
        <router-link to="/admin/tpk" class="nav-link" :class="{ active: isActive('/admin/tpk') }">
          <i class="bi bi-person-vcard"></i>
          <span v-if="!isCollapsed">Keanggotaan TPK</span>
        </router-link>
      </li>
      <li class="border-bottom border-primary mt-3" v-if="isCollapsed"></li>
      <li class="menu-section mt-4" v-if="!isCollapsed">Administrator</li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Konfigurasi"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/config"
          class="nav-link"
          :class="{ active: isActive('/admin/config') }"
        >
          <i class="bi bi-gear"></i>
          <span v-if="!isCollapsed">Konfigurasi</span>
        </router-link>
      </li>
      <li
        :data-bs-toggle="isCollapsed ? 'tooltip' : null"
        data-bs-placement="right"
        title="Log"
        ref="tooltipItem"
      >
        <router-link
          to="/admin/log"
          class="nav-link"
          :class="{ active: isActive('/admin/log') }"
        >
          <i class="bi bi-display"></i>
          <span v-if="!isCollapsed">Log</span>
        </router-link>
      </li>
      <li class="p-4" v-if="!isCollapsed"></li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'SidebarContent',
  props: {
    isCollapsed: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    isActive(path) {
      return this.$route.path === path
    },
  },
}
</script>

<style scoped>
/* Wrapper sidebar */
.sidebar-container {
  position: fixed;
  top: 80px; /* tinggi header Bootstrap (biasanya 56px) */
  left: 0;
  height: calc(100vh - 56px);
  width: 250px;
  overflow-y: auto;
  border-right: 1px solid #eaeaea;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
  background-color: #fff;
  z-index: 1030; /* biar di atas konten tapi di bawah header */
}

.sidebar-container.sidebar-collapsed {
  width: 80px;
}

/* Menu item */
.nav-link {
  color: #333;
  border-radius: 12px;
  padding: 10px;
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
  transition: all 0.25s ease-in-out;
  font-weight: 500;
}

/* Icon lingkaran */
.nav-link i {
  color: var(--bs-primary);
  font-size: 1.1rem;
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: #f2f4f7; /* selalu ada lingkaran */
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

/* Hover */
.nav-link:hover i {
  background-color: #e0f0e8;
  color: var(--bs-primary);
}

/* Active: tetap lingkaran */
.nav-link.active i {
  background-color: var(--bs-primary);
  color: #fff;
}

/* Hover/active link text */
.nav-link:hover {
  color: var(--bs-primary);
  transform: translateX(4px);
}

.nav-link.active {
  background-color: transparent; /* jangan timpa lingkaran */
  color: var(--bs-primary);
  font-weight: 600;
}
.menu-section {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #999;
  margin-left: 10px;
  margin-bottom: 8px;
}

.sidebar-footer {
  border-top: 1px solid #eee;
}

@media (max-width: 991px) {
  .sidebar-container {
    top: 100px;
  }
}
</style>
