<template>
  <div class="d-flex">
    <!-- Button toggle sidebar (mobile) -->
    <button
      class="btn btn-primary d-md-none m-3"
      type="button"
      data-bs-toggle="offcanvas"
      data-bs-target="#adminSidebar"
      aria-controls="adminSidebar"
    >
      <i class="bi bi-list"></i>
    </button>

    <!-- Offcanvas untuk mobile -->
    <div
      class="offcanvas offcanvas-start"
      tabindex="-1"
      id="adminSidebar"
      aria-labelledby="adminSidebarLabel"
    >
      <div class="offcanvas-header">
        <!-- <h5 class="offcanvas-title fw-bold" id="adminSidebarLabel">Admin Panel</h5> -->
        <button
          type="button"
          class="btn-close"
          data-bs-dismiss="offcanvas"
          aria-label="Close"
        ></button>
      </div>
      <div class="offcanvas-body p-0">
        <SidebarContent />
      </div>
    </div>

    <!-- Sidebar desktop -->
    <div
      :class="['d-none d-md-flex flex-column flex-shrink-0 p-3 bg-light vh-100 border-end']"
      :style="{ width: isCollapsed ? '80px' : '250px', transition: 'width 0.3s' }"
    >
      <SidebarContent :is-collapsed="isCollapsed" />
    </div>
  </div>
</template>

<script>
import SidebarContent from './SidebarContent.vue'

export default {
  name: 'NavbarAdmin',
  components: { SidebarContent },
  props: {
    isCollapsed: {
      type: Boolean,
      default: false,
    },
  },
}
</script>

<style>
.offcanvas {
  max-width: 35% !important;
}
</style>
