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

    <HeaderAdmin />

    <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed,
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div class="py-4 container-fluid">

          <!-- Welcome Card -->
          <Welcome />

          <!-- =======================
               UPLOAD LOGO DESA
          ======================== -->
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold text-primary mb-3">
                <i class="fa-regular fa-images me-2"></i> Upload Logo Desa
              </h5>

              <div class="row justify-content-center">
                <!-- Preview -->
                <div class="col-12 text-center mb-3" v-if="logoSrc">
                  <img
                    :src="logoSrc"
                    alt="Logo Preview"
                    class="img-fluid rounded shadow-sm"
                    style="max-height: 100px"
                  />
                </div>

                <div class="col-12 text-center mb-3" v-else>
                  <div class="card shadow-0 p-4 mx-auto" style="width: 200px;height: 200px;">
                    <p class="text-muted mb-0">Preview</p>
                  </div>
                </div>

                <!-- Input + Button (STACKED) -->
                <div class="col-12 text-center">
                  <div class="d-flex flex-column align-items-center gap-2">
                    <input
                      type="file"
                      class="form-control"
                      style="width: 260px"
                      @change="handleFileChange($event, 'logo')"
                    />
                    <button
                      class="btn btn-success"
                      style="width: 260px"
                      @click="handleSubmit"
                    >
                      <i class="fa-solid fa-upload me-2"></i>Upload
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- =======================
               SLIDER TEXT SETTING
          ======================== -->
          <div class="card border-0 shadow-sm rounded-4 my-4">
            <div class="card-body p-4">
              <h5 class="fw-bold text-primary mb-4">
                <i class="fa-solid fa-sliders me-2"></i> Pengaturan Slider
              </h5>

              <form @submit.prevent="saveSetting">
                <div class="mb-3">
                  <label class="form-label fw-semibold">Main Title</label>
                  <input v-model="form.main_title" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Title</label>
                  <input v-model="form.title" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label fw-semibold">Description</label>
                  <textarea v-model="form.description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-4">
                  <label class="form-label fw-semibold">Sub Description</label>
                  <textarea v-model="form.subdescription" class="form-control" rows="3"></textarea>
                </div>

                <div class="text-end">
                  <button class="btn btn-primary px-4">
                    <i class="fa-solid fa-save me-2"></i>Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- =======================
               UPLOAD IMAGE
          ======================== -->
          <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
              <h5 class="fw-bold text-primary mb-3">
                <i class="fa-regular fa-images me-2"></i> Upload Gambar Slider
              </h5>

              <div class="row g-3 align-items-end">
                <div class="col-md-6">
                  <input type="file" class="form-control" @change="handleImage">
                </div>
                <div class="col-md-6">
                  <button class="btn btn-success" @click="uploadImage">
                    <i class="fa-solid fa-upload me-2"></i>Upload
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- =======================
               TABLE IMAGE
          ======================== -->
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <h5 class="fw-bold text-primary mb-3">
                <i class="fa-solid fa-table me-2"></i> Daftar Gambar Slider
              </h5>

              <div class="table-responsive">
                <table class="table table-bordered align-middle">
                  <thead class="table-light">
                    <tr>
                      <th width="60">No</th>
                      <th width="160">Preview</th>
                      <th>URL</th>
                      <th width="120">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="images.length === 0">
                      <td colspan="4" class="text-center text-muted">
                        Belum ada gambar slider
                      </td>
                    </tr>

                    <tr v-for="(img, i) in images" :key="img.id">
                      <td>{{ i + 1 }}</td>
                      <td>
                        <img :src="img.image_url" class="img-thumbnail" style="max-height:80px">
                      </td>
                      <td class="small">{{ img.image_url }}</td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-danger"
                          @click="deleteImage(img.id)">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

            </div>
          </div>

        </div>

        <CopyRight/>
      </div>
    </div>
  </div>

  <!-- Modal Success -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">Berhasil</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">{{ successMessage || 'Konfigurasi berhasil disimpan.' }}</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Error -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg rounded-4">
        <div class="modal-header bg-danger text-white rounded-top-4">
          <h5 class="modal-title">Error</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">{{ errorMessage || 'Terjadi kesalahan yang tidak diketahui.' }}</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import Welcome from '@/components/Welcome.vue'
import axios from 'axios'

// PORT backend kamu
const API_PORT = 8000

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Slider',
  components: { CopyRight, HeaderAdmin, NavbarAdmin, Welcome },

  data() {
    return {
      logoSrc:null,
      isLoading: false,
      isCollapsed: false,
      selectedImage: null,
      images: [],
      form: {
        logo: null,
        main_title: '',
        title: '',
        description: '',
        subdescription: '',
      },
      errorMessage: '',
      successMessage: '',
    }
  },

  methods: {
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
    },
    setFile(file) {
      const reader = new FileReader()
      reader.onload = (ev) => {
        this.logoSrc = ev.target.result
        this.form.logoName = file.name
      }
      reader.readAsDataURL(file)
      this.form.logo = file
    },
    normalizeLogoPath(path) {
      if (!path) return null
      // Kalau sudah mengandung http (sudah absolute URL), langsung return
      if (path.startsWith('http')) return path

      // Kalau masih relative, pastikan tanpa "storage/" dobel
      return path.replace(/^storage\//, '')
    },
    async handleSubmit() {
      try {

        const formData = new FormData()
        formData.append('logo', this.form.logo)

        const res = await axios.post(`${baseURL}/api/config`, formData, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        console.log('✅ Config saved:', res.data)

        localStorage.removeItem('site_config_cache')
        const refresh = await axios.get(`${baseURL}/api/config`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        const data = refresh.data?.data
        if (data) {
          const cleanLogo = this.normalizeLogoPath(data.logo)
          this.logoSrc = `${baseURL}/storage/${cleanLogo}`
          this.form.logoName = cleanLogo.split('/').pop()
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
        this.showSuccess('Konfigurasi berhasil disimpan & disinkronkan')
      } catch (err) {
        console.error('❌ Gagal simpan konfigurasi:', err)
        this.showError('Gagal menyimpan konfigurasi. Periksa koneksi atau token Anda.')
      }
    },

    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },

    handleImage(e) {
      this.selectedImage = e.target.files[0]
    },

    // --- Modal helper ---
    showError(message) {
      this.errorMessage = message || 'Terjadi kesalahan.'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('errorModal'))
      modal.show()
    },
    showSuccess(message) {
      this.successMessage = message || 'Berhasil tersimpan.'
      // eslint-disable-next-line no-undef
      const modal = new bootstrap.Modal(document.getElementById('successModal'))
      modal.show()
    },

    async loadSetting() {
      try {
        const res = await axios.get(`${baseURL}/api/slider-setting`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json',
          },
        })

        if (res.data?.data) {
          this.form = res.data.data
        }
      } catch (err) {
        console.error('Gagal load setting:', err)
      }
    },

    async saveSetting() {
      await axios.post(`${baseURL}/api/slider-setting`, this.form, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      })
      alert('Pengaturan slider disimpan')
    },

    async loadImages() {
      try {
        const res = await axios.get(`${baseURL}/api/slider-images`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        this.images = res.data?.data || []
      } catch (err) {
        console.error('Gagal load images:', err)
        this.images = []
      }
    },

    async uploadImage() {
      if (!this.selectedImage) {
        this.showError('Pilih image terlebih dahulu')
        return
      }

      try {
        const fd = new FormData()
        fd.append('image', this.selectedImage)

        await axios.post(`${baseURL}/api/slider-images`, fd, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
          },
        })

        this.selectedImage = null
        this.$refs.fileInput.value = null
        await this.loadImages()

        this.showSuccess('Upload berhasil')
      } catch (error) {
        console.error('Upload gagal:', error)
        this.showError('Upload gagal')
      }
    },

    async deleteImage(id) {
      if (!confirm('Hapus gambar ini?')) return
      await axios.delete(`${baseURL}/api/slider-images/${id}`, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      })
      this.loadImages()
    },
  },

  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadSetting(),
        this.loadImages()
      ])
    } catch (error) {
      this.showError('Error load slider page:', error)
    } finally {
      this.isLoading = false
    }
  },
}
</script>

<style scoped>
.spinner-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.8);
  z-index: 9999;
  backdrop-filter: blur(2px);
  transition: opacity 0.3s ease;
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Samakan background content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
}

/* Empty state */
.empty-state {
  max-width: 520px;
  margin: 0 auto;
}

.empty-icon {
  font-size: 3rem;
  color: #d1d5db;
}
</style>
