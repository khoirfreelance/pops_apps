<template>
  <div class="wrapper">
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
                class="card col-lg-2 col-sm-5 col-5 mx-3 my-2 shadow-bottom border-0 border-top border-4 border-primary"
              >
                <div class="row">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="text-start">
                      <span class="fa fa-home text-secondary p-3 rounded-circle" style="background-color: lightgrey;"></span>
                    </div>
                    <div class="text-end">
                      <h5 class="text-muted">{{ stat.title }}</h5>
                      <h2 class="card-title">{{ stat.value }}</h2>
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
      isCollapsed: false,
      username: '',
      today: '',
      kecamatan: '',
      logoSrc: '/cipayung.png',
      logoLoaded: true,
      windowWidth: window.innerWidth,
      stats: [
        { title: 'RW', value: '1,000', icon: '/icons/icon2.png' },
        { title: 'RT', value: '100,000', icon: '/icons/icon1.png'},
        { title: 'Keluarga Terdaftar', value: '100 M', icon: '/icons/icon3.png' },
        { title: 'TPK', value: '1,234', icon: '/icons/icon4.png' },
        { title: 'Ibu Hamil', value: '56 K', icon: '/icons/icon5.png' },
        { title: 'Posyandu', value: '8 K', icon: '/icons/icon6.png' },
        { title: 'Bidan', value: '1,234', icon: '/icons/icon7.png' },
        { title: 'Calon Pengantin', value: '12 K', icon: '/icons/icon8.png' },
        { title: 'Anak <= 5 Tahun', value: '56', icon: '/icons/icon9.png' },
      ],
    }
  },
  methods: {
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
  mounted() {
    this.getWilayahUser()
    this.handleResize()
    window.addEventListener('resize', this.handleResize)
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
