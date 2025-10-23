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
      <NavbarAdmin  :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"  />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >
          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0 bg-light">
            <div class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between">
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <h3>
                  <span class="fw-normal fs-6">Selamat datang,</span> <br />
                  {{ username }}
                </h3>
                <img
                  v-if="logoLoaded"
                  :src="logoSrc"
                  alt="Logo"
                  height="50"
                  class="mt-4"
                  @error="logoLoaded = false"
                />
                <!-- jika gagal load logo, tampilkan kecamatan -->
                <span
                  v-else
                  class="text-muted fw-bold fs-5 mt-4"
                >
                  {{ kecamatan || 'Wilayah' }}
                </span>
                <p class="small d-flex align-items-center mt-1">
                  Data terakhir diperbarui pada &nbsp;<strong>{{ today }}</strong>
                </p>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img
                  src="/banner.png"
                  alt="Welcome"
                  class="img-fluid welcome-img"
                  style="max-width: 280px"
                />
              </div>
            </div>
          </div>

          <!-- Statistic Cards -->
          <div class="container-fluid mt-2">
            <div class="row justify-content-center" style="/* gap: 0.3rem !important; */">
              <div
                v-for="(stat, index) in stats"
                :key="index"
                class="stat-card col-lg-2 col-sm-5 col-5 mx-3 my-2 shadow-bottom border-0 border-top border-4 border-primary"
              >
                <div class="row">
                  <div class="card-body d-flex align-items-center justify-content-between mx-2">
                    <div class="icon-wrap d-flex align-items-center justify-content-center">
                      <span :class="['stat-icon', stat.icon]"></span>
                    </div>
                    <div class="text-end ms-2">
                      <h5 class="text-muted mb-1">{{ stat.title }}</h5>
                      <h2 class="card-title fw-bold mb-0">{{ stat.value }}</h2>
                    </div>
                  </div>

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
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import axios from 'axios'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Dashboard',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      kecamatan: '',
      logoSrc: '/cipayung.png',
      logoLoaded: true,
      windowWidth: window.innerWidth,
      stats: [],
    }
  },
  methods: {
    async fetchStats() {
      try {
        const res = await axios.get('http://localhost:8000/api/dashboard/stats', {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
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
    async getWilayahUser() {
      try {
        const res = await axios.get('http://localhost:8000/api/user/region', {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const wilayah = res.data
        this.kecamatan = wilayah.kecamatan || 'Tidak diketahui'
      } catch (error) {
        this.kecamatan = error
      }
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
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
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
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
  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.getWilayahUser(),
        this.fetchStats(),
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
/* ====== Layout umum ====== */
.wrapper {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.content {
  display: flex;
  margin-top: 60px;
  flex: 1;
  flex-direction: row;
  overflow-x: hidden;
  transition: margin-left 0.3s ease;
}
.stat-card {
  border-radius: 10px;
  min-height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.shadow-bottom {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Efek hover lembut */
.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Ikon */
.stat-icon {
  background-color: #e9ecef;
  color: var(--bs-secondary);
  font-size: 2rem;
  width: 60px;
  height: 60px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.stat-icon:hover {
  background-color: var(--bs-primary);
  color: #fff;
  transform: scale(1.1);
}

/* Pastikan ikon tetap center vertikal */
.icon-wrap {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* =============== media frame ================ */
/* Sidebar fix untuk layar besar */
@media (min-width: 992px) {
  .content {
    margin-left: 260px; /* sesuaikan lebar sidebar */
  }

  .content.sidebar-collapsed {
    margin-left: 80px; /* versi collapse */
  }
}

/* ====== Versi mobile (≤991px) ====== */
@media (max-width: 991px) {
  .content {
    flex-direction: column;
    margin-left: 80px !important; /* default: sidebar collapsed */
    margin-top: 60px; /* biar gak ketimpa header */
    transition: margin-left 0.3s ease;
  }

  /* Kalau sidebar lagi terbuka (utuh 250px) */
  .content.sidebar-expanded {
    margin-left: 250px !important; /* geser sesuai lebar sidebar */
  }

  /* Sidebar biar ngambang di kiri tapi gak nutup konten */
  .navbar-admin {
    position: fixed;
    top: 60px;
    left: 0;
    width: 250px;
    height: calc(100vh - 60px);
    background-color: #fff;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    transform: translateX(-100%);
    transition: transform 0.3s ease;
    z-index: 1040;
  }

  /* Saat sidebar aktif, tampil penuh */
  .navbar-admin.active {
    transform: translateX(0);
  }

  /* Konten utama */
  .container-fluid {
    padding: 1rem;
  }

  .welcome-card {
    margin: 0 auto;
    max-width: 95%;
  }

  .welcome-img {
    max-width: 180px;
    margin-top: 1rem;
  }

}

</style>
