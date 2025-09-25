<template>
  <div class="bride-wrapper">
    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div
          class="flex-grow-1 p-4 bg-light container-fluid"
          :style="{
            backgroundImage: background ? `url(${background})` : 'none',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundAttachment: 'fixed',
          }"
        >
          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div
              class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between"
            >
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <div class="my-3">
                  <h2 class="fw-bold mt-3 mb-0 text-white">Data Log</h2>
                  <small class="text-white"> Daftar log user </small>
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">
                      Log
                    </li>
                  </ol>
                </nav>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img src="/src/assets/admin.png" alt="Welcome" class="img-fluid welcome-img" />
              </div>
            </div>
          </div>

          <!-- Filter -->
          <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- NIK -->
              <div class="col-md-12">
                <label for="nik" class="form-label">NIK Petugas</label>
                <input
                  type="text"
                  v-model="filter.nik"
                  id="nik"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK Petugas"
                />
              </div>

              <!-- Expandable section -->
              <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                <!-- context -->
                <div class="col-md-6">
                  <label for="context" class="form-label">Context Log</label>
                  <input
                    type="text"
                    v-model="advancedFilter.context"
                    id="context"
                    class="form-control"
                  />
                </div>

                <!-- Activity -->
                <div class="col-md-6">
                  <label for="activity" class="form-label">Activity Log</label>
                  <input
                    type="text"
                    v-model="advancedFilter.activity"
                    id="activity"
                    class="form-control"
                  />
                </div>

                <!-- Tombol -->
                <div class="col-md-12">
                  <button type="submit" class="btn btn-primary float-start" @click="applyFilter">
                    <i class="bi bi-search"></i> Cari
                  </button>
                  <button type="button" class="btn btn-secondary float-end" @click="resetFilter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </button>
                </div>
              </div>
            </form>

            <!-- Expand/Collapse Button -->
            <div class="text-end mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="toggleExpand">
                <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
              </button>
            </div>
          </div>

          <!-- Table -->
          <div class="container-fluid my-3">
            <div class="card modern-card mt-4">
              <div class="card-body">
                <div class="datatable-responsive">
                  <EasyDataTable
                    :headers="headers"
                    :items="filteredLog"
                    buttons-pagination
                    :rows-per-page="5"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <CopyRight class="mt-5" />
      </div>
    </div>
  </div>

</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import axios from 'axios'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Log',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
      isCollapsed: false,
      isFilterOpen: false,
      log:[],
      headers: [
        { text: 'NIK', value: 'nik' },
        { text: 'Petugas', value: 'nama' },
        { text: 'Lingkup', value: 'context' },
        { text: 'Aktivitas', value: 'activity' },
        { text: 'Waktu', value: 'timestamp' },
      ],
      // filter
      filter: {
        nik: '',
      },
      advancedFilter: {
        context: '',
        activity: '',
      },
      appliedFilter: {}, // hasil filter simpan di sini
    }
  },
  computed: {
    background() {
      try {
        const config = JSON.parse(localStorage.getItem('siteConfig'))
        return config?.background || null
      } catch {
        return null
      }
    },
    filteredLog() {
      return this.log.filter((item) => {
        return (
          // NIK realtime
          (!this.filter.nik || item.nik.includes(this.filter.nik)) &&
          // Advanced filter hanya aktif setelah "Cari"
          (!this.appliedFilter.context || item.context === this.appliedFilter.context) &&
          (!this.appliedFilter.activity || item.activity === this.appliedFilter.activity)
        )
      })
    },
  },
  methods: {
    async loadLog(){
      try {
        const res = await axios.get('http://localhost:8000/api/log',{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.log = res.data
        console.log(this.log);
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    toggleExpand() {
      this.isFilterOpen = !this.isFilterOpen
    },
    applyFilter() {
      // copy isi advancedFilter ke appliedFilter saat tombol Cari ditekan
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter.nik = ''
      this.advancedFilter = {
        context: '',
        activity: '',
      }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
  },
  mounted() {
    this.loadLog()
  },
}
</script>

<style scoped>
.datatable-responsive {
  width: 100%;
  overflow-x: auto; /* aktifkan scroll horizontal */
}

.datatable-responsive table {
  min-width: 300px; /* sesuaikan lebar minimal tabel */
}

.bride-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.bride-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.filter-wrapper {
  position: relative;
  z-index: 1050;
  margin-top: -30px !important;
  width: 97%;
}
/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
}
.breadcrumb-item + .breadcrumb-item::before {
  color: rgba(255, 255, 255, 0.7);
}
.table-wrapper {
  width: 100%;
  overflow-x: auto; /* ✅ Scroll horizontal */
  -webkit-overflow-scrolling: touch; /* smooth di mobile */
}

.table-modern td {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
@media (max-width: 768px) {
  .table-modern {
    min-width: auto;
  }
}
</style>
