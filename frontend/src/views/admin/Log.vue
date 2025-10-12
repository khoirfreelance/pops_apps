<template>
  <div class="bride-wrapper">
    <!-- 🔄 Spinner Overlay -->
    <transition name="fade">
      <div
        v-if="isLoading"
        class="spinner-overlay d-flex justify-content-center align-items-center"
      >
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </transition>

    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div class="flex-grow-1 p-4 bg-light container-fluid">
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
            <form class="row g-4 align-items-end" @submit.prevent="applyFilter">
              <!-- NIK -->
              <div class="col-md-12">
                <label for="nik" class="form-label text-primary fw-semibold">NIK Petugas</label>
                <input
                  type="text"
                  v-model="filter.nik"
                  id="nik"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK Petugas"
                />
              </div>

              <!-- Context -->
              <div class="col-md-6">
                <label class="form-label mb-2 text-primary fw-semibold">Context Log</label>
                <div class="row">
                  <div class="col-6" v-for="(col, colIndex) in splitArray(contextOptions, 3)" :key="'ctx-col-' + colIndex">
                    <div class="form-check mb-3" v-for="c in col" :key="'ctx-' + c">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        :value="c"
                        v-model="advancedFilter.context"
                        :id="'context-' + c"
                      />
                      <label class="form-check-label text-capitalize" :for="'context-' + c">
                        {{ c }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Activity -->
              <div class="col-md-6">
                <label class="form-label text-primary fw-semibold mb-2">Activity Log</label>
                <div class="row">
                  <div class="col-6" v-for="(col, colIndex) in splitArray(activityOptions, 3)" :key="'act-col-' + colIndex">
                    <div class="form-check mb-3" v-for="a in col" :key="'act-' + a">
                      <input
                        class="form-check-input"
                        type="checkbox"
                        :value="a"
                        v-model="advancedFilter.activity"
                        :id="'activity-' + a"
                      />
                      <label class="form-check-label text-capitalize" :for="'activity-' + a">
                        {{ a }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tombol -->
              <div class="col-md-12 d-flex justify-content-between mt-3">
                <small class="text-muted fst-italic m-3">*Bisa pilih lebih dari 1</small>
                <button type="button" class="btn btn-secondary" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
              </div>
            </form>
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
      isLoading: true,
      isCollapsed: false,
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
        context: [],
        activity: [],
      },
      contextOptions: [
        'gizi anak',
        'ibu Hamil',
        'Calon Pengantin',
        'keluarga',
        'Kader / Pengguna',
        'Anggota TPK',
      ],
      activityOptions: ['store', 'view', 'delete', 'update', 'assign'],
      appliedFilter: {}, // hasil filter simpan di sini
    }
  },
  computed: {
    filteredLog() {
      return this.log.filter((item) => {
        return (
          (!this.filter.nik || item.nik.includes(this.filter.nik)) &&
          (this.advancedFilter.context.length === 0 ||
            this.advancedFilter.context.includes(item.context)) &&
          (this.advancedFilter.activity.length === 0 ||
            this.advancedFilter.activity.includes(item.activity))
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
    splitArray(array, size) {
      const result = []
      for (let i = 0; i < array.length; i += size) {
        result.push(array.slice(i, i + size))
      }
      return result
    },
    applyFilter() {
      // copy isi advancedFilter ke appliedFilter saat tombol Cari ditekan
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter.nik = ''
      this.advancedFilter.context = []
      this.advancedFilter.activity = []
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadLog(),
      ])
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      this.isLoading = false
    }
  }
}
</script>

<style scoped>
.spinner-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  backdrop-filter: blur(2px);
  transition: opacity 0.3s ease;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
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
