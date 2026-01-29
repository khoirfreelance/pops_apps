<template>
  <div class="card welcome-card shadow-sm mb-1 border-0 rounded">
    <div class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between">
      <!-- Kiri -->
      <div class="text-start my-2">
        <div class="mb-0" style="font-size: 12;">Selamat datang,</div>
        <h2 class="mt-0">{{ username }}</h2>
        <!-- Logo / fallback -->
         <img
          :src="logoSrc"
          alt="Logo"
          height="50"
          @error="onLogoError"
        />
        <!-- <img
          v-if="logoLoaded"
          :src="logoSrc"
          alt="Logo"
          height="50"
          @error="logoLoaded = false"
        />
        <span v-else class="text-muted fw-bold fs-5 mt-4">
          {{ kelurahan || 'Semua Desa' }}
        </span> -->

        <p class="small mb-2 d-flex align-items-center mt-1"
          style="font-size:8px; white-space:normal; word-break:break-word; flex-wrap:wrap;max-width:375px;">
          Data terakhir diperbarui pada&nbsp;<strong>{{ today }}</strong>
        </p>
      </div>

      <!-- Kanan -->
      <div class="mt-3 mt-md-0">
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import { eventBus } from '@/eventBus'

const API_PORT = 8000
const { protocol, hostname } = window.location
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Welcome',
  data() {
    return {
      username: '',
      kelurahan: '',
      logoSrc: '/default.png', // 👈 default sejak awal
      //logoLoaded: true,
      today: '',
      configCacheKey: 'site_config_cache',
    }
  },
  async mounted() {
    this.today = this.getTodayDate()
    await this.loadConfigWithCache()
    this.kelurahan = localStorage.getItem('kelurahan_label') || 'Semua Desa'

    // 🔥 dengarkan perubahan
    eventBus.on('kelurahanChanged', this.updateKelurahan)
  },
  methods: {
    onLogoError(e) {
      // cegah infinite loop
      if (!e.target.src.includes('default.png')) {
        e.target.src = '/default.png'
      }
    },
    getTodayDate() {
      const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']
      const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    updateKelurahan(label) {
      this.kelurahan = label
    },
    async loadConfigWithCache() {
      try {
        const cached = localStorage.getItem(this.configCacheKey)
        if (cached) {
          const parsed = JSON.parse(cached)
          this.logoSrc = parsed.logo || '/default.png'
          return
        }

        const res = await axios.get(`${baseURL}/api/config`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const data = res.data?.data
        if (data) {
          this.logoSrc = data.logo || '/default.png'
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
      } catch (error) {
        console.warn('Gagal load config:', error)
        this.logoSrc = '/default.png'
      }
    }
  },
  created() {
    const userName = localStorage.getItem('userName')
    if (userName) {
      this.username = userName
        .split(' ')
        .map(w => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ')
    } else {
      this.username = 'User'
    }
    this.today = this.getTodayDate()
  },
  beforeUnmount() {
    eventBus.off('kelurahanChanged', this.updateKelurahan)
  },
}
</script>

<style scoped>
.welcome-card {
    background-image: url('@/assets/welcome0.png');
    background-repeat: no-repeat;
    background-size: cover;
    background-position-x: right;
    height: 200px;
}

.welcome-img {
    object-fit: stretch;
}

/* ====== Versi mobile (≤991px) ====== */
@media (max-width: 991px) {
    .welcome-card {
        margin-top: 60px;
        /* background: linear-gradient(0deg, #10754f 10%, #f7fcf4 13% ) !important; */
        border-radius: 0 0 20px 20px !important;
    }
}

@media (max-width: 600px) {
    .welcome-card {
        height: 170px;
        background: linear-gradient(0deg, #10754f 10%, #f7fcf4 13% ) !important;
    }
}
</style>
