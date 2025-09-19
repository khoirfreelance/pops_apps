<template>
  <div class="chart-wrapper">
    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />

      <!-- Main Content -->
      <div
        class="flex-grow-1 d-flex flex-column"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <!-- Banner -->
        <div
          class="chart-banner text-white p-5 d-flex flex-column flex-md-row justify-content-between align-items-center"
        >
          <div>
            <h2 class="fw-bold mb-2">Data Keluarga</h2>
            <p class="mb-0">List daftar keluarga yang terdaftar di dalam posyandu</p>
          </div>
          <nav aria-label="breadcrumb" class="mt-3 mt-md-0">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item">
                <router-link to="/admin" class="text-decoration-none text-white-50">
                  Beranda
                </router-link>
              </li>
              <li class="breadcrumb-item active text-white" aria-current="page">Data Keluarga</li>
            </ol>
          </nav>
        </div>

        <!-- Filter -->
        <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
          <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
            <div class="row g-3 align-items-end mt-2">
              <!-- Bulan -->
              <div class="col-md-4">
                <label for="bulan" class="form-label">Bulan</label>
                <select name="bulan" id="bulan" class="form-control" v-model="advancedFilter.bulan">
                  <option value="">Semua Bulan</option>
                  <option v-for="(nama, index) in bulanMap" :key="index" :value="index + 1">
                    {{ nama }}
                  </option>
                </select>
              </div>

              <!-- Tahun -->
              <div class="col-md-4">
                <label for="tahun" class="form-label">Tahun</label>
                <select name="tahun" id="tahun" class="form-control" v-model="advancedFilter.tahun">
                  <option value="">Semua Tahun</option>
                  <option v-for="tahun in tahunOptions" :key="tahun" :value="tahun">
                    {{ tahun }}
                  </option>
                </select>
              </div>

              <!-- gender -->
              <div class="col-md-4">
                <label for="gender" class="form-label">Jenis Kelamin</label>
                <select
                  name="gender"
                  v-model="advancedFilter.gender"
                  id="gender"
                  class="form-control"
                >
                  <option value="">L/P</option>
                  <option value="L">Laki-laki</option>
                  <option value="P">Perempuan</option>
                </select>
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
        </div>

        <!-- Button Group -->
        <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-square"></i> Tambah Data
          </button>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
            <i class="bi bi-filetype-csv"></i> Import Data Keluarga
          </button>
        </div>

        <!-- Table -->
        <div class="container-fluid">
          <div class="card modern-card mt-4">
            <div class="card-body">
              <h5 class="fw-semibold mb-3">Berat Badan / Usia</h5>

              <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                  <thead>
                    <tr>
                      <th rowspan="2" class="text-start">Status</th>
                      <th colspan="3">
                        Total <span class="text-muted">{{ headerLabel }}</span>
                      </th>
                    </tr>
                    <tr>
                      <th>Total</th>
                      <th>Laki-laki</th>
                      <th>Perempuan</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="row in filteredData" :key="row.status">
                      <td class="fw-semibold text-start">{{ row.status }}</td>
                      <td v-html="row.data.total"></td>
                      <td>{{ row.data.laki }}</td>
                      <td>{{ row.data.perempuan }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.chart-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}

/* Gradient Banner */
.chart-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1.5rem 1.5rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
}
</style>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Config',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
      isCollapsed: false,
      isFilterOpen: false,
      advancedFilter: {
        bulan: '',
        tahun: '',
        gender: '',
      },
      tableData: [
        {
          status: 'Aktif',
          data: { total: 20, laki: 12, perempuan: 8 },
          tanggal: '2025-07-05',
        },
        {
          status: 'Nonaktif',
          data: { total: 15, laki: 7, perempuan: 8 },
          tanggal: '2025-07-12',
        },
        {
          status: 'Pending',
          data: { total: 10, laki: 6, perempuan: 4 },
          tanggal: '2025-08-03',
        },
      ],
      bulanMap: [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ],
      filteredData: [],
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
    headerLabel() {
      const bulanMap = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ]
      const now = new Date()

      const bulan = this.advancedFilter.bulan
        ? parseInt(this.advancedFilter.bulan)
        : now.getMonth() + 1
      const tahun = this.advancedFilter.tahun ? this.advancedFilter.tahun : now.getFullYear()

      return `${bulanMap[bulan - 1]} ${tahun}`
    },
    tahunOptions() {
      const currentYear = new Date().getFullYear()
      return [currentYear - 1, currentYear, currentYear + 1]
    },
  },
  mounted() {
    this.applyFilter()
  },

  methods: {
    applyFilter() {
      this.filteredData = this.tableData.filter((item) => {
        const date = new Date(item.tanggal)
        const bulan = date.getMonth() + 1
        const tahun = date.getFullYear()

        const matchBulan = this.advancedFilter.bulan
          ? parseInt(this.advancedFilter.bulan) === bulan
          : true
        const matchTahun = this.advancedFilter.tahun
          ? parseInt(this.advancedFilter.tahun) === tahun
          : true
        const matchGender = this.advancedFilter.gender
          ? (this.advancedFilter.gender === 'L' && item.data.laki > 0) ||
            (this.advancedFilter.gender === 'P' && item.data.perempuan > 0)
          : true

        return matchBulan && matchTahun && matchGender
      })
    },
    resetFilter() {
      this.advancedFilter = { bulan: '', tahun: '', gender: '' }
      this.filteredData = this.tableData
    },
  },
}
</script>
