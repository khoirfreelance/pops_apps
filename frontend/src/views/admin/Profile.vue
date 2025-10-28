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
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Profile Card -->
          <div class="card profile-card border-0 shadow-sm mb-4 overflow-hidden">
            <!-- Gradient Header + Cover Action -->
            <div class="profile-header position-relative">
              <button
                class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-3 shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#coverModal"
              >
                <i class="bi bi-image me-1"></i> Change Cover
              </button>
            </div>

            <div
              class="card-body d-flex flex-column flex-md-row align-items-center gap-3 position-relative"
            >
              <!-- Avatar -->
              <div class="position-relative">
                <img
                  src="/src/assets/man.png"
                  alt="Profile"
                  class="rounded-circle shadow-lg profile-avatar"
                />
                <button
                  class="btn btn-sm btn-primary rounded-pill position-absolute bottom-0 start-50 translate-middle-x shadow-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#avatarModal"
                >
                  <i class="bi bi-pencil"></i>
                </button>
              </div>

              <!-- Info -->
              <div class="flex-grow-1 text-center text-md-start mt-4 mt-md-0 bg-transparent">
                <h4 class="fw-bold mb-1">Ruls</h4>
                <p class="text-muted mb-1">Moodle Specialist</p>
                <p class="text-muted small mb-0">
                  <i class="bi bi-envelope me-1"></i> ruls@example.com
                </p>
              </div>

              <!-- Action -->
              <div class="mt-3 mt-md-0">
                <button class="btn btn-primary btn-sm me-2 shadow-sm">
                  <i class="bi bi-pencil-square me-1"></i> Edit Profile
                </button>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-pills mb-3" id="profileTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#info">
                Info
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security">
                Security
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#settings">
                Settings
              </button>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Info -->
            <div class="tab-pane fade show active" id="info">
              <div class="card border-0 shadow-sm p-3 mb-4">
                <h5 class="fw-bold mb-3">Personal Information</h5>
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="Ruls" readonly />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" disabled class="form-control" value="ruls@example.com" />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <input type="text" class="form-control" value="Moodle Specialist" readonly />
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" value="+62 812-xxxx-xxxx" readonly />
                  </div>
                </div>
              </div>
            </div>

            <!-- Security -->
            <div class="tab-pane fade" id="security">
              <div class="card border-0 shadow-sm p-3 mb-4">
                <h5 class="fw-bold mb-3">Security Settings</h5>
                <button class="btn btn-outline-secondary btn-sm mb-2">
                  <i class="bi bi-key me-1"></i> Change Password
                </button>
                <button class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-shield-lock me-1"></i> Enable 2FA
                </button>
              </div>
            </div>

            <!-- Settings -->
            <div class="tab-pane fade" id="settings">
              <div class="card border-0 shadow-sm p-3 mb-4">
                <h5 class="fw-bold mb-3">Preferences</h5>
                <!-- <div class="form-check form-switch mb-2">
                  <input class="form-check-input" type="checkbox" checked />
                  <label class="form-check-label">Dark Mode</label>
                </div> -->
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" />
                  <label class="form-check-label">Email Notifications</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <CopyRight class="mt-auto" />
      </div>
    </div>

    <!-- Modal: Change Avatar -->
    <div class="modal fade" id="avatarModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">Change Profile Photo</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <input type="file" class="form-control" accept="image/*" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary">Upload</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Change Cover -->
    <div class="modal fade" id="coverModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-secondary text-white">
            <h5 class="modal-title">Change Cover Photo</h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
            ></button>
          </div>
          <div class="modal-body">
            <input type="file" class="form-control" accept="image/*" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
              Cancel
            </button>
            <button type="button" class="btn btn-secondary">Upload</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>

.profile-card {
  border-radius: 1rem;
  overflow: hidden;
  background: #fff;
  position: relative;
}

.profile-header {
  height: 120px;
  width: 100%;
  background: linear-gradient(135deg, var(--bs-primary), var(--bs-secondary));
}

.profile-avatar {
  width: 110px;
  height: 110px;
  object-fit: cover;
  border: 4px solid #fff;
  margin-top: -60px;
  transition: transform 0.3s ease;
}

.profile-avatar:hover {
  transform: scale(1.05);
}

.nav-tabs .nav-link {
  border: none;
  font-weight: 500;
  color: #666;
}
/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb; /* biar tetap clean */
}
</style>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import axios from 'axios'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Profile',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
      configCacheKey: 'site_config_cache',
      // required
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth:'',
      kelurahan: '',
      logoSrc: '/cipayung.png',
      logoLoaded: true,
      windowWidth: window.innerWidth,
      // -------------------
    }
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
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
  },
  computed: {
    background() {
      const config = JSON.parse(localStorage.getItem('siteConfig'))
      return config && config.background ? config.background : null
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.getWilayahUser(),
        this.handleResize(),
        this.loadConfigWithCache(),
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
