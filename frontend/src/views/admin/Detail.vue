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

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >
          <!-- Welcome Card -->
          <Welcome />

          <!-- Statistic Cards -->
          <div class="mt-3">
            <div class="row justify-content-center g-2">
              <div v-for="(stat, index) in stats" :key="index"
                class="col-xl-1_9 col-lg-2_custom col-md-3 col-sm-3 col-6">
                <div class="stat-card shadow-sm rounded h-100">
                  <h6 class="text-muted pt-2 ps-2" style="font-size: 16px;">{{ stat.title }}</h6>
                  <div class="card-body d-flex align-items-center justify-content-between px-2">
                    <!-- Text -->
                    <h4 class="fw-bold mb-0">{{ stat.value }}</h4>
                    <!-- Icon -->
                    <div class="icon-wrap d-flex align-items-center justify-content-center mb-1">
                      <i :class="[stat.icon]"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

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
                <thead class="table-success">
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
          <div class="row mt-3 align-items-stretch">

            <!-- KIRI -->
            <div class="col-12 col-lg-6 col-md-12 d-flex">
              <div class="card border border-primary shadow p-3 my-3 flex-fill d-flex flex-column"
                  style="background:#e2ece7;">
                <h6 class="text-primary fw-bold">
                  Berdasarkan Kategori Usia
                </h6>

                <div class="table-responsive bg-light p-3 rounded flex-grow-1">
                  <canvas ref="usiaChart"></canvas>
                </div>
              </div>
            </div>

            <!-- KANAN -->
            <div class="col-12 col-lg-6 col-md-12 d-flex">
              <div class="card border border-primary shadow p-3 my-3 flex-fill d-flex flex-column"
                  style="background:#e2ece7;">
                <h6 class="fw-bold mb-4 text-primary">
                  Berdasarkan Jenis Kelamin
                </h6>

                <div class="row justify-content-center flex-grow-1">
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

                    <!-- TABLE -->
                    <div class="px-4 mt-3">
                      <table class="table table-borderless table-sm mb-0 bg-transparent">
                        <tbody>
                          <tr
                            v-for="(cat, i) in item.categories"
                            :key="i"
                            class="bg-transparent"
                          >
                            <td
                              class="bg-transparent p-1"
                              :class="i < 2 ? 'text-danger' : 'text-primary'"
                            >
                              {{ cat.name }}
                            </td>

                            <td
                              class="bg-transparent p-1 text-start fw-bold"
                              :class="i < 2 ? 'text-danger' : 'text-primary'"
                            >
                              {{ cat.value }}
                            </td>
                          </tr>
                        </tbody>
                      </table>
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
                  <canvas ref="indiChart" style="height: 300px;"></canvas>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="card border border-primary shadow p-3 my-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-sm text-center align-middle">
                    <thead class="table-light">
                      <tr>
                        <th class="text-start">Indikator</th>
                        <th
                          v-for="(month, i) in detailIndikator.months"
                          :key="i"
                        >
                          {{ formatMonth(month) }}
                        </th>
                      </tr>
                    </thead>

                    <tbody>
                      <tr
                        v-for="catKey in categoryConfig.order"
                        :key="catKey"
                      >
                        <!-- LABEL -->
                        <td class="text-start fw-semibold">
                          {{ categoryConfig.map[catKey]?.label || catKey }}
                        </td>

                        <!-- VALUE PER BULAN -->
                        <td
                          v-for="(val, i) in detailIndikator?.data?.[catKey] ?? []"
                          :key="i"
                        >
                          {{ val }}
                        </td>

                      </tr>
                    </tbody>
                  </table>
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
      /* filteredData: [], // data hasil filter
      rawData:[],
      configCacheKey: 'site_config_cache',

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
      stats: [],
      isCollapsed: false,
      //detailTablePerBulan: [],
      detailTren: null,
      detailUmur: {},
      detailIndikator: {},
      indikatorChartInstance: null,
      usiaChartInstance: null,
      detailTable: [],
      tipe:'',
      title:'',
      posyanduList: [],
      rwReadonly: true,
      isLoading: false,
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
    async fetchStats() {
      try {
        const res = await axios.get(`${baseURL}/api/dashboard/stats/`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const data = res.data
        this.stats = [
          { title: 'RW', value: data.rw, icon: 'bi bi-houses-fill' },
          { title: 'RT', value: data.rt, icon: 'bi bi-house-fill' },
          { title: 'Keluarga Terdaftar', value: data.keluarga, icon: 'fa-solid fa-people-roof' },
          { title: 'TPK', value: data.tpk, icon: 'bi bi-person-vcard' },
          { title: 'Ibu Hamil', value: data.ibu_hamil, icon: 'fa-solid fa-person-pregnant' },
          { title: 'Posyandu', value: data.posyandu, icon: 'bi bi-heart-pulse' },
          { title: 'Bidan', value: data.bidan, icon: 'fa-solid fa-stethoscope' },
          { title: 'Calon Pengantin', value: data.catin, icon: 'bi bi-arrow-through-heart' },
          { title: 'Anak <= 5 Tahun', value: data.anak, icon: 'fa-solid fa-baby' },
        ]
      } catch (e) {
        console.error(e)
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
    trendClass(status, trend) {
      const positiveIsGood = ['Normal']

      if (positiveIsGood.includes(status)) {
        return trend > 0 ? 'text-success' : trend < 0 ? 'text-danger' : ''
      }

      // di bawah normal → trend naik = buruk
      return trend > 0 ? 'text-danger' : trend < 0 ? 'text-success' : ''
    },
    formatMonth(month) {
      const [y, m] = month.split('-')
      const date = new Date(y, m - 1)
      return date.toLocaleString('id-ID', {
        month: 'long',
        year: 'numeric'
      })
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
    async loadDetail() {
      try {
        const res = await axios.get(`${baseURL}/api/detail-tren`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters, // 🔹 kirim semua filter ke backend
        })
        this.detailTren = res.data.data
      } catch (error) {
        console.error(`LOAD ERROR:`, error)
      }
    },
    async loadUmur() {
      try {
        const res = await axios.get(`${baseURL}/api/detail-umur`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters, // 🔹 kirim semua filter ke backend
        })
        this.detailUmur = res.data.detail_umur
        this.$nextTick(() => {
          this.drawUsiaChart()
        })
      } catch (error) {
        console.error(`LOAD ERROR:`, error)
      }
      //await this.fetchDetail('/api/detail-umur')

    },
    drawUsiaChart() {
      if (!this.$refs.usiaChart || !this.detailUmur) return

      if (this.usiaChartInstance) {
        this.usiaChartInstance.destroy()
      }

      // ======================
      // LABEL USIA (X)
      // ======================
      const ageLabels = Object.keys(this.detailUmur).filter(
        k => !['start', 'end'].includes(k)
      )

      const { order, map } = this.categoryConfig

      // ======================
      // DATASET DINAMIS
      // ======================
      const datasets = order.map(cat => ({
        label: map[cat]?.label || cat,
        backgroundColor: map[cat]?.color || '#999',
        data: ageLabels.map(age =>
          this.detailUmur[age]?.[cat]?.[0] ?? 0
        )
      }))

      const ctx = this.$refs.usiaChart.getContext('2d')

      this.usiaChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ageLabels,
          datasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right'
            },
            datalabels: { display: false }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Kategori Usia (bulan)'
              }
            },
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Total Individu'
              }
            }
          }
        }
      })
    },
    async loadIndikator() {
      try {
        const res = await axios.get(`${baseURL}/api/detail-indikator`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters, // 🔹 kirim semua filter ke backend
        })
        this.detailIndikator = res.data.indikator
        this.$nextTick(() => {
          this.drawIndikatorChart()
        })
      } catch (error) {
        console.error(`LOAD ERROR:`, error)
      }
      //await this.fetchDetail('/api/detail-indikator')
    },
    drawIndikatorChart() {
      if (!this.detailIndikator || !this.$refs.indiChart) return

      if (this.indikatorChartInstance) {
        this.indikatorChartInstance.destroy()
      }

      const ctx = this.$refs.indiChart.getContext('2d')

      // 👉 FORMAT MONTHS: "2025-01" → "Jan 2025"
      const labels = (this.detailIndikator.months || []).map(m => {
        const d = new Date(m + '-01') // aman buat parsing
        return new Intl.DateTimeFormat('id-ID', {
          month: 'short',
          year: 'numeric'
        }).format(d)
      })

      const dataMap = this.detailIndikator.data || {}
      const cfg = this.categoryConfig

      const datasets = cfg.order
        .filter(cat => dataMap[cat])
        .map(cat => {
          const meta = cfg.map[cat]
          return {
            label: meta.label,
            data: dataMap[cat],
            fill: true,
            tension: 0.3,
            borderWidth: 2,
            borderColor: meta.color,
            backgroundColor: meta.color + '55',
            pointRadius: 2,
            stack: 'indikator'
          }
        })

      this.indikatorChartInstance = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: { position: 'top' },
            datalabels: { display: false },
            tooltip: {
              mode: 'index',
              intersect: false
            }
          },
          scales: {
            x: {
              stacked: true,
              title: {
                display: true,
                text: 'Bulan'
              }
            },
            y: {
              stacked: true,
              beginAtZero: true,
              title: {
                display: true,
                text: 'Jumlah Anak'
              }
            }
          }
        }
      })
    },
    async getWilayahUser() {
      try {
        const res = await axios.get(`${baseURL}/api/user/region`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const wilayah = res.data
        //console.log(wilayah);

        this.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.filters.provinsi = wilayah.provinsi || ''
        this.filters.kota = wilayah.kota || ''
        this.filters.kecamatan = wilayah.kecamatan || ''
        this.filters.kelurahan = this.kelurahan
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
    async fetchPosyanduByWilayah(id_wilayah) {
      if (!id_wilayah) {
        console.warn('ID wilayah kosong, tidak bisa fetch posyandu')
        return
      }

      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}/wilayah`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        this.posyanduList = res.data?.data || res.data || []
        //console.log("Posyandu list:", this.posyanduList);
      } catch (error) {
        console.error('Gagal mengambil data posyandu:', error)
        this.posyanduList = []
      }
    },
    handlePosyanduChange() {
      const selected = this.posyanduList.find((p) => p.nama_posyandu === this.filters.posyandu)

      if (selected) {
        this.rwList = selected.rw || []
        this.rtList = [] // reset RT
        this.filters.rw = ''
        this.filters.rt = ''
        this.rwReadonly = false
        this.rtReadonly = true
      } else {
        this.rwList = []
        this.rtList = []
        this.filters.rw = ''
        this.filters.rt = ''
        this.rwReadonly = true
        this.rtReadonly = true
      }
    },
    handleRWChange() {
      const selected = this.posyanduList.find((p) => p.nama_posyandu === this.filters.posyandu)

      if (selected) {
        // RT yang terkait RW tertentu
        this.rtList = selected.rt || []
        this.filters.rt = ''
        this.rtReadonly = false
      } else {
        this.rtList = []
        this.filters.rt = ''
        this.rtReadonly = true
      }
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      this.applyFiltersFromRoute()
      await this.getWilayahUser()
      await this.loadUmur()
      await this.loadDetail()
      await this.loadIndikator()
      await this.fetchStats()
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
          titleClass: 'text-secondary',
          valueClass: 'text-secondary'
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
    },
    categoryConfig() {
      const configs = {
        bbu: {
          order: [
            'Severely Underweight',
            'Underweight',
            'Normal',
            'Risiko BB Lebih',
            'TIDAK NAIK'
          ],
          map: {
            'Severely Underweight': { label: 'Sangat Kurang', color: '#dc3545' },
            'Underweight': { label: 'Kurang', color: '#ffc107' },
            'Normal': { label: 'Normal', color: '#198754' },
            'Risiko BB Lebih': { label: 'Risiko Lebih', color: '#0d6efd' },
            'TIDAK NAIK'  : { label: 'Tidak Naik', color: '#ab47bc' },
          }
        },

        tbu: {
          order: [
            'Severely Stunted',
            'Stunted',
            'Normal',
            'Tinggi'
          ],
          map: {
            'Severely Stunted': { label: 'Sangat Pendek', color: '#dc3545' },
            'Stunted': { label: 'Pendek', color: '#ffc107' },
            'Normal': { label: 'Normal', color: '#198754' },
            'Tinggi': { label: 'Tinggi', color: '#0d6efd' },
          }
        },

        bbtb: {
          order: [
            'Severely Wasted',
            'Wasted',
            'Normal',
            'Possible Risk of Overweight',
            'Overweight',
            'Obese'
          ],
          map: {
            'Severely Wasted': { label: 'Gizi Buruk', color: '#dc3545' },
            'Wasted': { label: 'Gizi Kurang', color: '#ffc107' },
            'Normal': { label: 'Gizi Baik', color: '#198754' },
            'Possible Risk of Overweight': { label: 'Risiko Gizi Lebih', color: '#0d6efd' },
            'Overweight': { label: 'Gizi Lebih', color: '#6610f2' },
            'Obese': { label: 'Obesitas', color: '#6f42c1' },
          }
        }
      }

      return configs[this.filters.tipe] || { order: [], map: {} }
    }
  }

}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Neuton:wght@400;700&display=swap');

.stat-card {
  background-color: #fff;
  border-top: 4px solid var(--bs-secondary);
  height: 90px;
  /* proporsional */
  transition: all 0.2s ease-in-out;
  max-width: 120px;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
  }

  .icon-wrap {
    background-color: var(--bs-secondary);
    color: #fff;
    border-radius: 8px;
    width: 34px;
    height: 34px;
    font-size: 16px;
    flex-shrink: 0;
  }

  h6 {
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    margin: 0;
  }

  h4 {
    font-family: 'Inter', sans-serif;
    color: #000;
    font-size: 1.1rem;
    margin: 0;
  }
}

.stat-text {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: flex-start;
  height: 60px;

  .spacer {
    flex: 1;
  }
}

/* custom kolom 9 per baris */
@media (min-width: 1400px) {
  .col-xl-1_9 {
    flex: 0 0 auto;
    width: 11.11%;
  }
}

/* fallback grid untuk ukuran lain */
@media (min-width: 992px) and (max-width: 1399.98px) {
  .col-lg-2_custom {
    flex: 0 0 auto;
    width: 11.11%;
    /* 5 kolom */
  }

  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;
    max-width: 120px;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 25px !important;
      height: 25px !important;
      font-size: 12px !important;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 10px !important;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 14px !important;
      margin: 0;
    }
  }

  .stat-text {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    height: 60px;

    .spacer {
      flex: 1;
    }
  }
}

@media (min-width: 768px) and (max-width: 991.98px) {
  .col-md-3 {
    flex: 0 0 auto;
    width: 11.11%;
  }

  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;
    max-width: 80px;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 25px !important;
      height: 25px !important;
      font-size: 12px !important;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 6px !important;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 12px !important;
      margin: 0;
    }
  }

  .stat-text {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    height: 60px;

    .spacer {
      flex: 1;
    }
  }
}

@media (max-width: 767.98px) {
  .col-sm-4 {
    flex: 0 0 auto;
    width: 33.33%;
    /* 3 kolom */
  }

  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 30px !important;
      height: 30px !important;
      font-size: 12px !important;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 7px !important;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 12px !important;
      margin: 0;
    }
  }

  @media (min-width: 768px) {
    .sticky-filter {
      position: sticky;
      top: 70px;
      z-index: 10;
    }
  }

  /* Mobile: hilangkan sticky & rapikan */
  /* @media (max-width: 767px) {
    .sticky-filter {
      position: static !important;
    }
    .uniform-input {
      height: 44px;
    }
  } */

  @media (max-width: 767px) {
    .sticky-filter {
      top: 100px;
    }
  }

  .filter-floating-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    border-radius: 50%;
    width: 55px;
    height: 55px;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
  }
}

* {
  font-size: 16px;
}

.welcome-card {
    background-image: url('/welcome0.png') !important;
    background-repeat: no-repeat !important;
    background-size: cover !important;
    background-position-x: right !important;
    height: 200px !important;
}

/* Fade effect */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Rotate icon */
.rotate {
  transform: rotate(180deg);
  transition: transform 0.25s ease;
}

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
