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
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"  />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div class="py-4 container-fluid">
          <!-- Welcome Card -->
          <Welcome />

          <!-- Content -->
          <div class="container-fluid mt-2">
            <div class="row justify-content-center">
              <div class="card border rounded-4 shadow-sm">
                <div class="card-body p-4">
                  <form @submit.prevent="handleSubmit" class="row g-4">

                    <!-- Upload Logo -->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold text-primary"><i class="bi bi-cloud-upload"></i> Logo Desa</label>
                      <div
                        class="dropzone p-4 rounded-3 text-center position-relative"
                        :class="{ 'border-primary bg-light': isLogoDrag }"
                        @dragover.prevent="isLogoDrag = true"
                        @dragleave.prevent="isLogoDrag = false"
                        @drop.prevent="handleDrop($event, 'logo')"
                      >
                        <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                        <p class="mb-1 fw-medium">Drag & drop logo here</p>
                        <small class="text-muted">or click to browse</small>

                        <input
                          ref="logoInput"
                          type="file"
                          accept="image/*"
                          class="position-absolute w-100 h-100 top-0 start-0 opacity-0"
                          @change="handleFileChange($event, 'logo')"
                        />

                        <!-- 🔹 Nama file aktif -->
                        <div v-if="form.logoName" class="mt-2 small text-success fw-semibold">
                          <i class="bi bi-check-circle me-1"></i>{{ form.logoName }}
                        </div>
                      </div>
                    </div>

                    <!-- Preview Logo-->
                    <div class="col-md-6">
                      <label class="form-label fw-semibold">Preview</label>
                      <div v-if="logoSrc" class="p-5 text-center">
                        <img
                          :src="logoSrc"
                          alt="Logo Preview"
                          class="img-fluid rounded shadow-sm"
                          style="max-height: 100px"
                        />
                      </div>
                    </div>

                    <!-- Upload Background -->
                    <!-- <div class="col-md-6">
                      <label class="form-label fw-semibold">Upload Background</label>
                      <div
                        class="dropzone p-4 rounded-3 text-center"
                        :class="{ 'border-primary bg-light': isBgDrag }"
                        @dragover.prevent="isBgDrag = true"
                        @dragleave.prevent="isBgDrag = false"
                        @drop.prevent="handleDrop($event, 'background')"
                      >
                        <i class="bi bi-image fs-1 text-primary"></i>
                        <p class="mb-1 fw-medium">Drag & drop background</p>
                        <small class="text-muted">or click to browse</small>
                        <input
                          type="file"
                          accept="image/*"
                          class="position-absolute w-100 h-100 top-0 start-0 opacity-0"
                          @change="handleFileChange($event, 'background')"
                        />
                      </div>
                    </div> -->

                    <!-- Preview Background-->
                    <!-- <div class="col-md-6">
                      <label class="form-label fw-semibold">Preview</label>
                      <div v-if="previewBackground" class="mt-3 text-center">
                        <img
                          :src="previewBackground"
                          alt="Background Preview"
                          class="img-fluid rounded shadow-sm"
                          style="max-height: 150px"
                        />
                      </div>
                    </div> -->

                    <!-- Theme -->
                    <!-- <div class="col-md-12">
                      <label class="form-label fw-semibold">Color Theme</label>
                      <input
                        type="color"
                        v-model="form.colorTheme"
                        class="form-control form-control-color modern-input"
                      />
                    </div> -->

                    <!-- Apps Mode-->
                    <!-- <div class="col-md-12 d-flex align-items-center justify-content-between">
                      <label class="form-label fw-semibold mb-0">Dev Mode</label>
                      <div class="form-check form-switch">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          role="switch"
                          v-model="form.app"
                        />
                        <label class="form-check-label">
                          {{ form.app ? 'On' : 'Off' }}
                        </label>
                      </div>
                    </div> -->

                    <!-- Maintenance Mode -->
                    <!-- <div class="col-md-12 d-flex align-items-center justify-content-between">
                      <label class="form-label fw-semibold mb-0">Maintenance Mode</label>
                      <div class="form-check form-switch">
                        <input
                          class="form-check-input"
                          type="checkbox"
                          role="switch"
                          v-model="form.maintenance"
                        />
                        <label class="form-check-label">
                          {{ form.maintenance ? 'On' : 'Off' }}
                        </label>
                      </div>
                    </div> -->

                    <!-- Save Button -->
                    <div class="col-12 text-end">
                      <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill shadow">
                        <i class="bi bi-save me-2"></i> Simpan
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <CopyRight />
      </div>
    </div>
  </div>

<!-- Loader Overlay with Animated Progress -->
  <div v-if="isLoadingImport"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-50"
    style="z-index: 2000">
    <div class="w-50">
      <div class="progress" style="height: 1.8rem; border-radius: 1rem; overflow: hidden">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
          :style="{ width: importProgress + '%' }" :data-progress="progressLevel">
          <span class="fw-bold">{{ animatedProgress }}%</span>
        </div>
      </div>
    </div>
    <p class="text-white mt-3">Sedang {{transaksi}} data...</p>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import Welcome from '@/components/Welcome.vue'
import axios from 'axios'
import Swal from 'sweetalert2'

const API_PORT = 8000
const { protocol, hostname } = window.location
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Config',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, Welcome },
  data() {
    return {
      transaksi:'',
      progressLevel:0,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      isLoadingImport: false,
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      //logoLoaded: true,
      windowWidth: window.innerWidth,
      logoName: null,
      errorMessage: '',
      successMessage: '',
      form: {
        logo: null,
        app: false,
        maintenance: false,
      },
      logoSrc: '/default.png',
      previewBackground: null,
      isLogoDrag: false,
      isBgDrag: false,
    }
  },
  methods: {
    // --- Wilayah user ---
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
        this.id_wilayah = wilayah.id_wilayah
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },

    // --- Util tanggal ---
    getTodayDate() {
      const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']
      const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    getThisMonth() {
      const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
      const now = new Date()
      let monthIndex = now.getMonth() - 1
      let year = now.getFullYear()
      if (monthIndex < 0) { monthIndex = 11; year -= 1 }
      return `${bulan[monthIndex]} ${year}`
    },

    // --- Layout ---
    handleResize() {
      this.windowWidth = window.innerWidth
      this.isCollapsed = this.windowWidth < 992
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },

    // --- Upload handler ---
    handleFileChange(e, type) {
      const file = e.target.files[0]
      if (!file) return
      this.setFile(file, type)
    },
    handleDrop(e, type) {
      const file = e.dataTransfer.files[0]
      if (!file) return
      this.setFile(file, type)
      if (type === 'logo') this.isLogoDrag = false
      if (type === 'background') this.isBgDrag = false
    },
    setFile(file, type) {
      const reader = new FileReader()
      reader.onload = (ev) => {
        if (type === 'logo') {
          this.logoSrc = ev.target.result
          this.form.logoName = file.name
        } else if (type === 'background') {
          this.previewBackground = ev.target.result
        }
      }
      reader.readAsDataURL(file)
      if (type === 'logo') this.form.logo = file
      else if (type === 'background') this.form.background = file
    },
    normalizeLogoPath(path) {
      if (!path) return null
      // Kalau sudah mengandung http (sudah absolute URL), langsung return
      if (path.startsWith('http')) return path

      // Kalau masih relative, pastikan tanpa "storage/" dobel
      return path.replace(/^storage\//, '')
    },
    async loadConfigWithCache() {
      try {
        const cached = localStorage.getItem(this.configCacheKey)
        const { protocol, hostname } = window.location
        const base = `${protocol}//${hostname}:8000`

        if (cached) {
          const parsed = JSON.parse(cached)
          this.dev = parsed.app == 1 ? 1 : 0

          if (parsed.logo) {
            const cleanLogo = this.normalizeLogoPath(parsed.logo)
            // Kalau cleanLogo sudah URL penuh, pakai langsung
            this.logoSrc = cleanLogo.startsWith('http')
              ? cleanLogo
              : `${base}/storage/${cleanLogo}`
            this.form.logoName = cleanLogo.split('/').pop()
          }


          this.form.app = parsed.app == 1 || parsed.app === true
          this.form.maintenance = parsed.maintenance == 1 || parsed.maintenance === true
          return
        }

        const res = await axios.get(`${base}/api/config`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const data = res.data?.data
        if (data) {
          this.dev = data.app == 1 ? 1 : 0

          if (data.logo) {
            const cleanLogo = this.normalizeLogoPath(data.logo)
            this.logoSrc = `${base}/storage/${cleanLogo}`
            this.form.logoName = cleanLogo.split('/').pop()
          }

          this.form.app = data.app == 1 || data.app === true
          this.form.maintenance = data.maintenance == 1 || data.maintenance === true
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
      } catch (error) {
        console.warn('Gagal load config:', error)
        this.logoLoaded = false
      }
    },

    async handleSubmit() {
      try {

        this.isLoadingImport = true
        this.animatedProgress = 10
        this.progressLevel = 10
        this.importProgress = 10

        const formData = new FormData()
        formData.append('logo', this.form.logo)
        formData.append('background', this.form.background)
        formData.append('maintenance', this.form.maintenance ? 1 : 0)
        formData.append('app', this.form.app ? 1 : 0)

        await axios.post(`${baseURL}/api/config`, formData, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        this.animatedProgress = 30
        this.progressLevel = 30
        this.importProgress = 30
        localStorage.removeItem('site_config_cache')
        this.animatedProgress = 50
        this.progressLevel = 50
        this.importProgress = 50
        const refresh = await axios.get(`${baseURL}/api/config`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        this.animatedProgress = 70
        this.progressLevel = 70
        this.importProgress = 70
        const data = refresh.data?.data
        if (data) {
          const cleanLogo = this.normalizeLogoPath(data.logo)
          this.logoSrc = `${baseURL}/storage/${cleanLogo}`
          this.form.logoName = cleanLogo.split('/').pop()
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }

        this.importProgress = 100
        // animasi ke 100
        await new Promise(resolve => {
          const interval = setInterval(() => {
            if (this.animatedProgress >= 100) {
              clearInterval(interval)
              resolve()
            } else {
              this.animatedProgress += 5
            }
          }, 30)
        })

        Swal.fire({
          icon: 'success',
          html: `Berhasil menyimpan Logo`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary'
          }
        })
      } catch (err) {
        console.error('❌ Gagal simpan konfigurasi:', err)
        this.isLoadingImport = false
        Swal.fire({
          title: 'Error',
          html: err.data?.error || 'Terjadi kesalahan saat menyimpan data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }finally {
        this.isLoadingImport = false
      }
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.getWilayahUser(),
        this.loadConfigWithCache(),
        this.handleResize(),
        window.addEventListener('resize', this.handleResize)
      ])
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      this.isLoading = false
    }
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    if (storedEmail) {
      let namePart = storedEmail.split('@')[0]
      namePart = namePart.replace(/[._]/g, ' ')
      this.username = namePart.split(' ').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ')
    } else {
      this.username = 'User'
    }
    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<style scoped>
.bg-light[style] {
  transition: background-image 0.6s ease-in-out;
}
.configuration-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}

/* Gradient Banner */
.configuration-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1.5rem 1.5rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* Card Glassmorphism */
.glass-card {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

/* Dropzone */
.dropzone {
  position: relative;
  cursor: pointer;
  border: 2px dashed #dee2e6;
  transition: all 0.3s ease;
}
.dropzone:hover {
  border-color: var(--bs-primary);
  background: #f8faff;
  transform: translateY(-2px);
}

/* Inputs */
.modern-input {
  transition: all 0.2s ease;
}
.modern-input:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Section Title */
.section-title {
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-weight: 600;
  color: #6c757d;
  border-bottom: 1px solid #dee2e6;
  padding-bottom: 0.3rem;
  margin-bottom: 1rem;
}

/* Button Gradient */
.btn-gradient {
  background: linear-gradient(135deg, #198754, var(--bs-secondary));
  border: none;
  color: white;
  transition: all 0.3s ease;
}
.btn-gradient:hover {
  background: linear-gradient(135deg, #157347, #198754);
  transform: translateY(-2px);
}
</style>
