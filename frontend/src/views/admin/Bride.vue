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
    <HeaderAdmin />

    <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"   />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Welcome Card -->
          <Welcome />

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <div
                v-for="(filter, key) in filterOptions"
                :key="key"
                class="col-md-3 position-relative"
              >
                <label class="form-label text-primary">{{ filter.label }}</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start overflow-hidden text-nowrap text-truncate d-flex align-items-center justify-content-between"
                    type="button"
                    data-bs-toggle="dropdown"
                  >
                    <span v-if="!filters[key].length" class="text-muted">{{ filter.placeholder }}</span>
                    <span v-else>{{ filters[key].join(', ') }}</span>
                  </button>

                  <ul class="dropdown-menu w-100 p-2" style="max-height: 260px; overflow-y: auto;">
                    <li
                      v-for="item in filter.options"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="`${key}-${item}`"
                        :value="item"
                        v-model="filters[key]"
                      />
                      <label class="form-check-label w-100" :for="`${key}-${item}`">{{ item }}</label>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" @click="selectAll(key)">
                        Pilih Semua
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-secondary fw-semibold" @click="clearAll(key)">
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Periode -->
              <div class="col-md-6 text-center">
                <label class="form-label text-primary">Pilih Periode:</label>
                <div class="d-flex justify-content-between gap-2">
                  <select v-model="filters.periodeAwal" class="form-select text-muted">
                    <option value="">Awal</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                  <select v-model="filters.periodeAkhir" class="form-select text-muted">
                    <option value="">Akhir</option>
                    <option v-for="p in periodeOptions" :key="p" :value="p">{{ p }}</option>
                  </select>
                </div>
              </div>

              <!-- Tombol Aksi -->
              <div class="col-md-3 d-flex justify-content-between">
                <button type="submit" class="btn btn-gradient fw-semibold">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <!-- Ringkasan Statistik-->
          <div class="container-fluid my-4">

            <div class="row">
              <div class="col-xl-10 col-sm-12">
                <div class="row justify-content-center">
                  <div
                    v-for="(item, index) in kesehatan"
                    :key="index"
                    class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12 mb-3"
                  >
                    <div
                      class="card shadow-sm border-0 rounded-3 overflow-hidden"
                      :class="`border-start border-4 border-${item.color}`"
                    >
                      <div class="card-header">
                        <h6 class="fw-bold mb-1">{{ item.title }}</h6>
                      </div>
                      <div class="card-body py-3 d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0" :class="`text-${item.color}`">{{ item.value }}</h3>
                        <p class="mb-0" :class="`text-${item.color}`">{{ item.percent }}</p>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <!-- TOTAL BUMIL -->
              <div class="col-xl-2 col-sm-12">
                <div class="card text-center shadow-sm border p-2 h-100 d-flex flex-column justify-content-center">
                  <h6 class="text-muted fw-bold">Total Calon Pengantin</h6>
                  <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <h1 class="fw-bold text-success mb-0">{{totalCatin}}</h1>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table -->

        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Bride',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome },
  data() {
    return {
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      windowWidth: window.innerWidth,
      configCacheKey: 'site_config_cache',
      kesehatan: [],
      bride: [], // data utama calon pengantin
      bridePending: [], // data pending catin
      filteredCatin: [],
      totalCatin: 0,
      filterOptions: {
        kek: {
          label: 'Status Gizi',
          placeholder: 'Pilih Status Gizi',
          options: ['Normal', 'KEK'],
        },
        anemia: {
          label: 'Status HB',
          placeholder: 'Pilih Status HB',
          options: ['Normal', 'Anemia Ringan', 'Anemia Sedang', 'Anemia Berat'],
        },
        beresiko: {
          label: 'Faktor Risiko',
          placeholder: 'Pilih Risiko',
          options: ['Tidak', 'Ya'],
        },
        intervensi: {
          label: 'Intervensi',
          placeholder: 'Pilih Intervensi',
          options: ['TTD', 'Edukasi', 'Rujuk', 'PMT'],
        },
      },

      headers: [
        // --- Data Perempuan ---
        { text: 'Nama Perempuan', value: 'nama_perempuan' },
        { text: 'NIK Perempuan', value: 'nik_perempuan' },
        { text: 'Pekerjaan Perempuan', value: 'pekerjaan_perempuan' },
        { text: 'Tanggal Lahir Perempuan', value: 'tgl_lahir_perempuan' },
        { text: 'Usia Perempuan', value: 'usia_perempuan' },
        { text: 'HP Perempuan', value: 'hp_perempuan' },

        // --- Data Pria ---
        { text: 'Nama Pria', value: 'nama_pria' },
        { text: 'NIK Pria', value: 'nik_pria' },
        { text: 'Pekerjaan Pria', value: 'pekerjaan_pria' },
        { text: 'Tanggal Lahir Pria', value: 'tgl_lahir_pria' },
        { text: 'Usia Pria', value: 'usia_pria' },
        { text: 'HP Pria', value: 'hp_pria' },

        // --- Data Pernikahan ---
        { text: 'Tanggal Daftar', value: 'tgl_daftar' },
        { text: 'Tanggal Rencana Nikah', value: 'tgl_rencana_menikah' },
        { text: 'Rencana Tempat Tinggal', value: 'rencana_tinggal' },

        // --- Data Pendampingan ---
        { text: 'Tanggal Pemeriksaan', value: 'tgl_pemeriksaan' },
        { text: 'Tanggal Pendampingan', value: 'tgl_pendampingan' },
        { text: 'Dampingan Ke', value: 'dampingan_ke' },
        { text: 'Berat Badan (kg)', value: 'bb' },
        { text: 'Tinggi Badan (cm)', value: 'tb' },
        { text: 'Lingkar Lengan Atas (cm)', value: 'lila' },
        { text: 'Hemoglobin (g/dL)', value: 'hb' },
        { text: 'Status HB', value: 'status_hb' },
        { text: 'Status Gizi', value: 'status_gizi' },
        { text: 'Terpapar Rokok', value: 'catin_terpapar_rokok' },
        { text: 'TTD', value: 'catin_ttd' },
        { text: 'Punya Riwayat Penyakit', value: 'punya_riwayat_penyakit' },
        { text: 'Riwayat Penyakit', value: 'riwayat_penyakit' },
        { text: 'Fasilitas Rujukan', value: 'fasilitas_rujukan' },
        { text: 'Edukasi', value: 'edukasi' },
        { text: 'PMT', value: 'pmt' },
      ],

      filters: {
        kek: [],
        anemia: [],
        beresiko: [],
        usia: [],
        intervensi: [],
        periodeAwal: '',
        periodeAkhir: '',
      },

      periodeOptions: [],
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
    // eslint-disable-next-line vue/no-dupe-keys
    filteredCatin() {
      return this.bride.filter(item => {
        const matchHB = !this.filters.anemia.length || this.filters.anemia.includes(item.status_hb);
        const matchGizi = !this.filters.kek.length || this.filters.kek.includes(item.status_gizi);
        return matchHB && matchGizi;
      });
    },
    visibleHeaders() {
      return this.headers.filter((h) => this.visibleColumns.includes(h.value))
    },
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    if (storedEmail) {
      let namePart = storedEmail.split('@')[0]
      namePart = namePart.replace(/[._]/g, ' ')
      this.username = namePart
        .split(' ')
        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ')
    } else {
      this.username = 'User'
    }
    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  methods: {
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
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        //await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
    getTodayDate() {
      const hari = [
        'Minggu', 'Senin', 'Selasa', 'Rabu',
        'Kamis', 'Jumat', 'Sabtu'
      ]
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
      ]
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    getThisMonth() {
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April',
        'Mei', 'Juni', 'Juli', 'Agustus',
        'September', 'Oktober', 'November', 'Desember'
      ]

      const now = new Date()
      let monthIndex = now.getMonth() - 1
      let year = now.getFullYear()

      // kalau sekarang Januari (0), berarti mundur ke Desember tahun sebelumnya
      if (monthIndex < 0) {
        monthIndex = 11
        year -= 1
      }

      return `${bulan[monthIndex]} ${year}`
    },
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
    },
    async loadBride() {
      try {
        const res = await axios.get(`${baseURL}/api/catin`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        });

        this.bride = res.data.data || [];
        this.totalCatin = this.bride.length;

        // 🔹 Hitung statistik kesehatan
        const normalGizi = 0; //this.bride.filter(c => c?.status_gizi === 'Normal').length;
        const kek = 0;//this.bride.filter(c => c?.status_gizi === 'KEK').length;
        const normalHB = 0;//this.bride.filter(c => c?.status_hb === 'Normal').length;
        const anemia = 0;//this.bride.filter(c =>
          //c?.status_hb && ['Anemia Ringan', 'Anemia Sedang', 'Anemia Berat'].includes(c.status_hb)
        //).length;


        const total = this.totalCatin || 1; // biar gak bagi 0

        this.kesehatan = [
          {
            title: 'Status Gizi Normal',
            value: normalGizi,
            percent: `${((normalGizi / total) * 100).toFixed(1)}%`,
            color: 'success',
          },
          {
            title: 'KEK',
            value: kek,
            percent: `${((kek / total) * 100).toFixed(1)}%`,
            color: 'warning',
          },
          {
            title: 'HB Normal',
            value: normalHB,
            percent: `${((normalHB / total) * 100).toFixed(1)}%`,
            color: 'primary',
          },
          {
            title: 'Anemia',
            value: anemia,
            percent: `${((anemia / total) * 100).toFixed(1)}%`,
            color: 'danger',
          },
        ];
      } catch (error) {
        console.error('Gagal memuat data calon pengantin:', error);
      }
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadConfigWithCache(),
        this.loadBride(),
        this.getPendingData(),
        this.getWilayahUser(),
        this.handleResize(),

        window.addEventListener('resize', this.handleResize)
      ])
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      this.isLoading = false
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
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
/* Smooth Apple-like Modal */
.modern-modal {
  border-radius: 1.5rem;
  border: 1px solid #eaeaea;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  background: #fff;
  transition: all 0.3s ease-in-out;
}

/* Form lebih clean */
.form-control-modern,
.form-select.form-control-modern {
  border-radius: 0.75rem;
  border: 1px solid #ddd;
  padding: 0.6rem 1rem;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.form-control-modern:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
}

/* Animasi modal lebih halus */
.modal.fade .modal-dialog {
  transform: translateY(20px);
  transition:
    transform 0.3s ease-out,
    opacity 0.3s ease-out;
}

.modal.fade.show .modal-dialog {
  transform: translateY(0);
  opacity: 1;
}
.modern-card {
  border-radius: 1rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  border: none;
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

form h5 {
  position: relative;
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
  border-bottom: 2px solid var(--bs-secondary); /* default pakai secondary */
}
@media (max-width: 768px) {
  .table-modern {
    min-width: auto;
  }
}
</style>
