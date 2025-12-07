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
                    v-for="(item, index) in genderData_bb"
                    :key="index"
                  >
                    <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                    <h6 class="title" :class="item.titleClass">{{ item.label }}</h6>
                    <div class="d-flex justify-content-between px-5">
                      <div>
                        <p v-for="(cat, i) in item.categories" :key="i">{{ cat.name }}</p>
                      </div>
                      <div class="fw-bold text-end" :class="item.valueClass">
                        <p v-for="(cat, i) in item.categories" :key="i">
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
      detailRaw: null,
      detailTable: [],
      tipe:'',
      title:'',
      filteredData: [], // data hasil filter
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
      indiChartInstance_bb: null,
      filters: {
        tipe: '',
        kelurahan:''
      },
    }
  },
  methods: {
    async getWilayahUser() {
      try {
        const res = await axios.get(`${baseURL}/api/user/region`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const wilayah = res.data
        this.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.filters.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        //await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
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
    // Implement to TREN and JK DEtail
    async loadDetail() {
      try {
        const res = await axios.get(`${baseURL}/api/detail-tren`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters,
        })
        this.detailRaw = res.data.data
        console.log(this.detailRaw);

        this.$nextTick(() => {
          this.buildDetailTable()
        })

      } catch (e) {
        console.error('LOAD DETAIL ERROR:', e)
      }
    },

    async loadConfigWithCache() {
      try {
        // cek di localStorage
        const cached = localStorage.getItem(this.configCacheKey)
        if (cached) {
          const parsed = JSON.parse(cached)
          this.logoSrc = parsed.logo || null
          return
        }
         // kalau belum ada cache, fetch dari API
        const res = await axios.get(`${baseURL}/api/config`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const data = res.data?.data
        if (data) {
          this.logoSrc = data.logo || null
          // simpan di localStorage untuk load cepat di page berikutnya
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
      }catch (error) {
        console.warn('Gagal load config:', error)
        this.logoLoaded = false
      }
    },
    // Implement to Chart UMUR
    async loadUmur() {
      try {
        const res = await axios.get(`${baseURL}/api/detail-umur`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters,
        })
        this.detailRaw = res.data.data
        console.log(this.detailRaw);
      } catch (e) {
        console.error('LOAD DETAIL ERROR:', e)
      }
    },

    // Implement to Indikator
    async loadIndikator() {
      try {
        const res = await axios.get(`${baseURL}/api/detail-indikator`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters,
        })
        this.detailRaw = res.data.data
        //console.log(this.detailRaw);
      } catch (e) {
        console.error('LOAD DETAIL ERROR:', e)
      }
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      await this.getWilayahUser()

      const { tipe/* , status */ } = this.$route.query
      if (tipe/*  && status */) {
        this.filters.tipe = tipe
        if (tipe === 'bbu') this.title = 'Berat Badan / Usia'
        else if (tipe === 'tbu') this.title = 'Tinggi Badan / Usia'
        else if (tipe === 'bbtb') this.title = 'Berat Badan / Tinggi Badan'
      }
      await this.loadUmur()
      await this.loadDetail()
      await this.loadConfigWithCache()
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
    if (!this.detailRaw || !this.detailRaw.total) return []

    const statusList = Object.keys(this.detailRaw.total)

    // ✅ Ambil label bulan dari backend
    const start = new Date(this.detailRaw.start)
    const end   = new Date(this.detailRaw.end)

    const bulanLabels = [
      start.toLocaleString('id-ID', { month: 'long', year: 'numeric' }),
      end.toLocaleString('id-ID', { month: 'long', year: 'numeric' }),
    ]

    // ✅ Backend hanya 2 bulan valid → index 0 & 1
    return [0, 1].map((bulanIndex) => {
      return {
        bulan: bulanLabels[bulanIndex],
        rows: statusList.map((statusKey) => {
          return {
            status: statusKey,
            total: this.detailRaw.total?.[statusKey]?.[bulanIndex] ?? 0,
            laki: this.detailRaw.L?.[statusKey]?.[bulanIndex] ?? 0,
            perempuan: this.detailRaw.P?.[statusKey]?.[bulanIndex] ?? 0
          }
        })
      }
    })
  }
}

}
</script>

<style scoped>
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
