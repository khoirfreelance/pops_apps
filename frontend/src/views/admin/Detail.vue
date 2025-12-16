<template>
  <div class="wrapper">
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
    <HeaderAdmin/>
    <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <div class="flex-grow-1 d-flex flex-column">
        <!-- Content -->
        <div class="py-4 container-fluid" >
          <!-- Welcome Card -->
          <Welcome />

          <!-- Statistik Berat Badan / Usia -->
          <div class="card border border-primary shadow p-3 my-3">
            <div class="mb-4">

              <h6 class="fw-bold text-primary mb-2">
                {{title}}
              </h6>

              <table
                v-if="detailTablePerBulan.length > 0"
                class="table table-hover table-bordered align-middle mb-0"
              >
                <thead class="table-light">
                  <tr>
                    <th rowspan="2" class="text-center align-middle">Status</th>

                    <th
                      v-for="(bulanItem, i) in detailTablePerBulan"
                      :key="i"
                      colspan="3"
                      class="text-center"
                    >
                      Total {{ bulanItem.bulan }}
                    </th>
                  </tr>

                  <tr>
                    <template v-for="(bulanItem, i) in detailTablePerBulan" :key="i">
                      <th class="text-center" width="100">Total</th>
                      <th class="text-center" width="100">Laki-laki</th>
                      <th class="text-center" width="100">Perempuan</th>
                    </template>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="(statusRow, i) in detailTablePerBulan[0].rows" :key="i">
                    <td class="fw-semibold">
                      {{ statusRow.status }}
                    </td>

                    <template v-for="(bulanItem, b) in detailTablePerBulan" :key="b">
                      <td class="text-center">
                        {{ bulanItem.rows[i].total }}
                        <span
                          v-if="b === detailTablePerBulan.length - 1"
                          :class="trendClass(bulanItem.rows[i].status, bulanItem.rows[i].trend)"
                        >
                          ({{ bulanItem.rows[i].trend > 0 ? '+' : '' }}{{ bulanItem.rows[i].trend }})
                        </span>
                      </td>
                      <td class="text-center">
                        {{ bulanItem.rows[i].laki }}
                      </td>
                      <td class="text-center">
                        {{ bulanItem.rows[i].perempuan }}
                      </td>
                    </template>
                  </tr>
                </tbody>
              </table>

              <!-- OPTIONAL LOADING STATE -->
              <div v-else class="text-center py-4 text-muted">
                Memuat data...
              </div>


            </div>

          </div>

          <!-- Statistik berdasarkan kelompok usia dan gender-->
          <div class="row mt-3">
            <div class="col-12 col-lg-6 col-md-6">
              <div class="card border border-primary shadow p-3 my-3">
                <h6 class="text-primary fw-bold">Berdasarkan Kategori Usia</h6>
                <div class="table-responsive">
                  <canvas ref="usiaChart"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-6 col-md-6">
              <div class="card border border-primary shadow p-3 my-3">
                <h6 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h6>
                <div class="row justify-content-center">
                  <div
                    class="col-md-6 col-sm-12 col-12 mb-4"
                    v-for="(item, index) in detailByGender"
                    :key="index"
                  >
                    <div :class="['circle', item.circleClass]">
                      {{ item.total }}
                    </div>

                    <h6 class="title text-center" :class="item.titleClass">
                      {{ item.label }}
                    </h6>

                    <div class="d-flex justify-content-between px-5 mt-3">
                      <div>
                        <p
                          v-for="(cat, i) in item.categories"
                          :key="i"
                          class="mb-1"
                        >
                          {{ cat.name }}
                        </p>
                      </div>

                      <div
                        class="fw-bold text-end"
                        :class="item.valueClass"
                      >
                        <p
                          v-for="(cat, i) in item.categories"
                          :key="i"
                          class="mb-1"
                        >
                          {{ cat.value }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Statistik berdasarkan 1 tahun terkahir -->
          <div class="row mt-3">
            <div class="col-12">
              <div class="card border border-primary shadow p-3 my-3">
                <div class="table-responsive">
                  <canvas ref="indiChart_bb"></canvas>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <!-- Footer -->
    <CopyRight />
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import Welcome from '@/components/Welcome.vue'
import axios from 'axios'
import {
  Chart,
  PieController,
  ArcElement,
  Tooltip,
  Legend,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Filler,
} from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)
Chart.register(PieController, ArcElement, Tooltip, Legend, ChartDataLabels)
Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  Filler,
)

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Detail',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome },
  data() {
    return {
      detailTablePerBulan: [],
      detailTren: null,
      detailTable: [],
      tipe:'',
      title:'',
      /* filteredData: [], // data hasil filter
      rawData:[],
      configCacheKey: 'site_config_cache',
      isLoading: false,
      isCollapsed: false,
      windowWidth: window.innerWidth,
      dataTable_bb:[],
      dataTable_tb:[],
      dataTable_bbtb:[],
      genderData_bb: [],
      genderData_tb: [],
      genderData_bbtb: [],
      indikatorData: {}, // diisi dari loadChildren
      bulanLabels: [], // diisi daftar bulan
      usiaChartInstance_bb: null,
      usiaChartInstance_tb: null,
      usiaChartInstance_bbtb: null,
      children:[],
      kelurahan:'',
      indiChartInstance_tb: null,
      indiChartInstance_bbtb: null,
      indiChartInstance_bb: null, */
      filters: {
        tipe: '',
        provinsi: '',
        kota: '',
        kecamatan: '',
        kelurahan: '',
        posyandu: '',
        rt: '',
        rw: '',
        periode: '',
      },
    }
  },
  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
    },
    trendClass(status, trend) {
      const positiveIsGood = ['Normal']

      if (positiveIsGood.includes(status)) {
        return trend > 0 ? 'text-success' : trend < 0 ? 'text-danger' : ''
      }

      // di bawah normal → trend naik = buruk
      return trend > 0 ? 'text-danger' : trend < 0 ? 'text-success' : ''
    },
    applyFiltersFromRoute() {
      const q = this.$route.query

      Object.keys(this.filters).forEach(key => {
        this.filters[key] = q[key] || ''
      })

      // set title sekali di sini
      if (this.filters.tipe === 'bbu') this.title = 'Berat Badan / Usia'
      else if (this.filters.tipe === 'tbu') this.title = 'Tinggi Badan / Usia'
      else if (this.filters.tipe === 'bbtb') this.title = 'Berat Badan / Tinggi Badan'
    },
    async fetchDetail(endpoint) {
      try {
        const res = await axios.get(`${baseURL}${endpoint}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters,
        })
        this.detailTren = res.data.data
      } catch (e) {
        console.error(`LOAD ${endpoint} ERROR:`, e)
      }
    },
    async loadDetail() {
      await this.fetchDetail('/api/detail-tren')
    },
    async loadUmur() {
      await this.fetchDetail('/api/detail-umur')
    },
    async loadIndikator() {
      await this.fetchDetail('/api/detail-indikator')
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      this.applyFiltersFromRoute()

      await this.loadUmur()
      await this.loadDetail()
      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('Error mounted:', err)
    } finally {
      this.isLoading = false
    }

  },
  computed: {
    // eslint-disable-next-line vue/no-dupe-keys
    detailTablePerBulan() {
      if (!this.detailTren || !this.detailTren.total) return []

      const statusList = Object.keys(this.detailTren.total)

      // 🔑 Ambil bulan dari start & end backend
      const startDate = new Date(this.detailTren.start)
      const endDate   = new Date(this.detailTren.end)

      const bulanList = [
        {
          label: startDate.toLocaleString('id-ID', {
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC'
          }),
          index: 0
        },
        {
          label: endDate.toLocaleString('id-ID', {
            month: 'long',
            year: 'numeric',
            timeZone: 'UTC'
          }),
          index: 1
        }
      ]

      return bulanList.map((bulan) => ({
        bulan: bulan.label,
        rows: statusList.map((status) => ({
          status,
          total: this.detailTren.total?.[status]?.[bulan.index] ?? 0,
          laki: this.detailTren.L?.[status]?.[bulan.index] ?? 0,
          perempuan: this.detailTren.P?.[status]?.[bulan.index] ?? 0,
          trend: this.detailTren.trend?.[status] ?? 0
        }))
      }))
    },
    detailByGender() {
      if (!this.detailTren?.gender_summary) return []

      const colorMap = {
        L: {
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success'
        },
        P: {
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning'
        }
      }

      return Object.keys(this.detailTren.gender_summary).map((key) => {
        const item = this.detailTren.gender_summary[key]

        return {
          label: item.label,
          total: item.total,
          categories: item.categories,
          ...colorMap[key]
        }
      })
    }
  }

}
</script>

<style scoped>
.circle {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  margin: 0 auto 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 22px;
}

.circle-green {
  background: #1f7a4d;
  color: #fff;
}

.circle-yellow {
  background: #d6c27a;
  color: #fff;
}

.circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  margin: 0 auto 15px auto;
  color: #fff;
}
.male-circle {
  background-color: var(--bs-primary)
}
.female-circle {
  background-color: var(--bs-secondary)
}
.title {
  font-weight: bold;
  text-align: center;
  margin-bottom: 15px;
}
@media (min-width: 1200px) {
  .stat-col {
    flex: 0 0 10%;
    max-width: 10%;
  }
}

.dashboard-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  color: #333;
}

/* Card Statistik */
.stat-card {
  border-radius: 1rem;
  transition: all 0.3s ease;
  /* background: #fff; */
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.08); /* soft primary */
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1a7f37;
}

/* Form & Select */
.form-select,
.btn {
  /* border-radius: 0.75rem; */
  transition: all 0.2s ease-in-out;
}
.form-select:focus,
.btn:focus {
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

/* Card konten */
.card {
  border-radius: 1rem !important;
  border: none;
  background: #fff;
  transition: box-shadow 0.2s ease;
}
.card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Table */
.table {
  font-size: 0.95rem;
}
.table th {
  color: #6c757d;
  font-weight: 600;
}
.table td {
  color: #333;
}
</style>
