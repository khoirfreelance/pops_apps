<template>
  <div
    class="sidebar-desktop d-flex flex-column flex-shrink-0 p-3 bg-light border-end position-fixed start-0 vh-100"
    :style="{ width: isCollapsed ? '80px' : '250px', top: '56px', transition: 'width 0.3s' }"
  >
    <!-- Tombol toggle di kanan sidebar -->
    <button
      class="btn toggle-btn position-absolute top-50 translate-middle-y"
      @click="$emit('toggle-sidebar')"
    >
      <i :class="isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left'"></i>
    </button>

    <!-- Konten sidebar -->
    <SidebarContent :is-collapsed="isCollapsed" />
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
  mounted() {
  if (window.innerWidth <= 768) {
    this.$emit('toggle-sidebar', true)
  }
}

}
</script>

<style scoped>
.sidebar-desktop {
  top: 56px;
  height: calc(100vh - 56px);
  overflow-y: auto;
  z-index: 1030;
  position: fixed;
  padding-bottom: 80px; /* 🔥 tambahkan ini */
}

/* tombol toggle kecil di sisi kanan */
.toggle-btn {
  right: -10px; /* keluar sedikit dari sidebar */
  background-color: #fff;
  border: 1px solid #ccc;
  /* border-radius: 10%; */
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6c757d;
  transition: all 0.3s ease;
  z-index: 2000;
}

.toggle-btn:hover {
  background-color: var(--bs-primary);
  color: #fff;
  border-color: var(--bs-primary);
  transform: scale(1.1);
}
@media (max-width: 768px) {
  .toggle-btn {
    display: none !important;
  }
}

</style>
