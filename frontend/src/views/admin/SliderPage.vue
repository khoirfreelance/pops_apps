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
    <!-- <HeaderAdmin :kelurahan="userStore.kelurahan" /> -->

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
          <!-- <Welcome :kelurahan="userStore.kelurahan"/> -->

          <ul class="nav nav-tabs mt-3" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-bottom-0 active" id="logoDesa-tab" data-bs-toggle="tab" data-bs-target="#logoDesa" type="button" role="tab" aria-controls="logoDesa" aria-selected="true">Logo Desa</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-bottom-0" id="slider-tab" data-bs-toggle="tab" data-bs-target="#slider" type="button" role="tab" aria-controls="slider" aria-selected="false">Slider</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link rounded-bottom-0" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer" type="button" role="tab" aria-controls="footer" aria-selected="false">Footer</button>
            </li>
          </ul>
          <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="logoDesa" role="tabpanel" aria-labelledby="logoDesa-tab">
              <!-- =======================
               UPLOAD LOGO DESA
              ======================== -->
              <div class="card border-0 rounded-top-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-bold text-primary mb-3">
                    <i class="fa-regular fa-images me-2"></i> Upload Logo Desa
                  </h5>

                  <div class="row justify-content-center">
                    <!-- Preview -->
                    <div class="col-12 text-center mb-3" v-if="logoLoaded">
                      <img
                        :src="logoSrc"
                        alt="Logo Preview"
                        class="img-fluid rounded shadow-sm"
                        style="max-height: 100px"
                      />
                    </div>
                    <!-- <img
                        v-if="logoLoaded"
                        :src="logoSrc"
                        alt="Logo"
                        height="50"
                        @error="logoLoaded = false"
                      />
                      <img
                        v-else
                        :src="logoSrc"
                        alt="Logo Preview"
                        class="img-fluid rounded shadow-sm"
                        style="max-height: 100px"
                      /> -->
                    <!-- <div class="col-12 text-center mb-3" v-if="logoSrc">
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
                    </div> -->

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
            </div>
            <div class="tab-pane fade" id="slider" role="tabpanel" aria-labelledby="slider-tab">
              <!-- =======================
               SLIDER TEXT SETTING
              ======================== -->
              <div class="card rounded-top-0 border-0 shadow-sm rounded-4 my-4">
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
            <div class="tab-pane fade" id="footer" role="tabpanel" aria-labelledby="footer-tab">
              <!-- ======================
                  FOOTER LOGO
              ======================= -->
              <div class="card border-0 rounded-top-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                  <h5 class="fw-bold text-primary mb-3">
                    <i class="bi bi-image me-2"></i> Logo Footer
                  </h5>

                  <div class="row g-3 align-items-center">
                    <div class="col-md-6">
                      <input type="file" class="form-control" @change="handleLogo">
                    </div>
                    <div class="col-md-6">
                      <button class="btn btn-primary" @click="saveLogo">
                        <i class="fa-solid fa-save me-2"></i> Simpan Logo
                      </button>
                    </div>
                  </div>

                  <div v-if="footer.logo_url" class="mt-4">
                    <label class="fw-semibold mb-2 d-block">Preview</label>

                    <div
                      class="p-3 rounded"
                      style="background:#1f2937; display:inline-block"
                    >
                      <img
                        :src="footer.logo_url"
                        style="max-height:80px; max-width:200px"
                      >
                    </div>
                  </div>

                </div>
              </div>

              <!-- ======================
                  SOCIAL LINKS
              ======================= -->
              <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">

                  <h5 class="fw-bold text-primary mb-3">
                    <i class="fa-solid fa-share-nodes me-2"></i> Social Links
                  </h5>

                  <!-- TABLE -->
                  <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                      <thead class="table-light">
                        <tr>
                          <th>Type</th>
                          <th>Label</th>
                          <th>URL</th>
                          <th>Status</th>
                          <th width="120">Aksi</th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr v-for="link in links" :key="link.id">
                          <td class="fw-semibold text-capitalize">{{ link.type }}</td>
                          <td>{{ link.label }}</td>

                          <!-- EDIT URL -->
                          <td>
                            <input
                              v-model="link.url"
                              class="form-control form-control-sm"
                            />
                          </td>

                          <!-- TOGGLE ACTIVE -->
                          <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                v-model="link.is_active"
                              />
                            </div>
                          </td>

                          <!-- SAVE -->
                          <td class="text-center">
                            <button
                              class="btn btn-sm btn-primary"
                              @click="updateLink(link)"
                            >
                              <i class="fa-solid fa-save"></i>
                            </button>
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
const API_PORT = 8001

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8001"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Slider',
  components: { CopyRight, HeaderAdmin, NavbarAdmin, Welcome },

  data() {
    return {
      logoLoaded: true,
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
      footer: {
        logo_url: null,
      },
      logoFile: null,

      links: [],
      newLink: {
        type: '',
        label: '',
        url: '',
      },

      types: ['call','email','web','twitter','instagram','facebook','linkedin'],
    }
  },

  methods: {
    async loadConfigWithCache() {
      try {
        const cached = localStorage.getItem(this.configCacheKey)
        if (cached) {
          const parsed = JSON.parse(cached)
          this.logoSrc = parsed.logo || null
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
          this.logoSrc = data.logo || null
          localStorage.setItem(this.configCacheKey, JSON.stringify(data))
        }
      } catch (err) {
        console.warn('Gagal load config:', err)
        this.logoLoaded = false
      }
    },
    handleLogo(e) {
      this.logoFile = e.target.files[0]
    },
    /* =========================
       LOAD FOOTER (ADMIN)
    ========================== */
    async loadFooter() {
      try {
        const res = await axios.get(`${baseURL}/api/footer`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        this.footer = res.data.data || {}
      } catch (e) {
        console.error('Gagal load footer:', e)
      }
    },

    /* =========================
       SAVE LOGO
    ========================== */
    async saveLogo() {
      if (!this.logoFile) return alert('Pilih logo dulu')

      const fd = new FormData()
      fd.append('logo', this.logoFile)

      try {
        await axios.post(`${baseURL}/api/footer`, fd, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        this.logoFile = null
        this.loadFooter()
      } catch (e) {
        console.error('Gagal simpan logo:', e)
      }
    },

    /* =========================
       LOAD SOCIAL LINKS
    ========================== */
    async loadLinks() {
      try {
        const res = await axios.get(`${baseURL}/api/footer-social`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        this.links = (res.data.data || []).map(item => ({
          ...item,
          is_active: item.is_active === 1 // ⬅️ PENTING
        }))
      } catch (e) {
        console.error('Gagal load social links:', e)
        this.links = []
      }
    },

    /* =========================
       ADD SOCIAL LINK
    ========================== */
    async addLink() {
      try {
        await axios.post(`${baseURL}/api/footer-social`, this.newLink, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        this.newLink = { type:'', label:'', url:'' }
        this.loadLinks()
      } catch (e) {
        console.error('Gagal tambah link:', e)
      }
    },

    /* =========================
       DELETE SOCIAL LINK
    ========================== */
    async deleteLink(id) {
      if (!confirm('Hapus link ini?')) return

      try {
        await axios.delete(`${baseURL}/api/footer-social/${id}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            Accept: 'application/json'
          }
        })

        this.loadLinks()
      } catch (e) {
        console.error('Gagal hapus link:', e)
      }
    },

    async updateLink(link) {
      try {
        await axios.put(
          `${baseURL}/api/footer-social/${link.id}`,
          {
            url: link.url,
            is_active: link.is_active ? 1 : 0,
          },
          {
            headers: {
              Authorization: `Bearer ${localStorage.getItem('token')}`,
              Accept: 'application/json',
            }
          }
        )

        this.$toast?.success?.('Link disimpan') || alert('Link disimpan')
      } catch (e) {
        console.error('Gagal update link:', e)
        alert('Gagal menyimpan perubahan')
      }
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
        this.loadImages(),
        this.loadConfigWithCache(),
        this.loadFooter(),
        this.loadLinks()
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
