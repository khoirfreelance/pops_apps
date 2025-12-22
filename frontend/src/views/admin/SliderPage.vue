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

        <CopyRight class="mt-5"/>
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
      isLoading: false,
      isCollapsed: false,
      selectedImage: null,
      images: [],
      form: {
        main_title: '',
        title: '',
        description: '',
        subdescription: '',
      },
    }
  },

  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },

    handleImage(e) {
      this.selectedImage = e.target.files[0]
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
        const res = await axios.get(`${baseURL}/api/slider-images`)
        this.images = res.data.data || []
      } catch (err) {
        console.error('Gagal load images:', err)
        this.images = []
      }
    },

    async uploadImage() {
      if (!this.selectedImage) return alert('Pilih image')

      const fd = new FormData()
      fd.append('image', this.selectedImage)

      await axios.post(`${baseURL}/api/slider-images`, fd, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
      })

      this.selectedImage = null
      this.loadImages()
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
      console.error('Error load slider page:', error)
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
