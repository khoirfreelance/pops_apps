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
              <!-- <button
                class="btn btn-sm btn-light position-absolute top-50 end-0 translate-middle-y me-3 shadow-sm"
                data-bs-toggle="modal"
                data-bs-target="#coverModal"
              >
                <i class="bi bi-image me-1"></i> Change Cover
              </button> -->
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
                <!-- <button
                  class="btn btn-sm btn-primary rounded-pill position-absolute bottom-0 start-50 translate-middle-x shadow-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#avatarModal"
                >
                  <i class="bi bi-pencil"></i>
                </button> -->
              </div>

              <!-- Info -->
              <div class="flex-grow-1 text-center text-md-start mt-4 mt-md-0 bg-transparent">
                <h4 class="fw-bold mb-1">{{ user?.name }}</h4>
                <p class="text-muted mb-1">{{ user?.role }}</p>
                <p class="text-muted small mb-0">
                  <i class="bi bi-envelope me-1"></i> {{ user?.email }}
                </p>
              </div>

              <!-- Action -->
              <div class="mt-3 mt-md-0">
                <button
                  class="btn btn-primary btn-sm me-2 shadow-sm"
                  data-bs-toggle="modal"
                  data-bs-target="#editProfileModal"
                  @click="openEditProfile"
                >
                  <i class="bi bi-pencil-square me-1"></i> Edit Profile
                </button>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist">
            <li class="nav-item">
              <button class="nav-link active rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#info">
                 Information
              </button>
            </li>
            <!-- <li class="nav-item">
              <button class="nav-link" data-bs-toggle="tab" data-bs-target="#dampingan">
                Dampingan TPK
              </button>
            </li> -->
            <li class="nav-item">
              <button class="nav-link rounded-bottom-0" data-bs-toggle="tab" data-bs-target="#settings">
                Settings
              </button>
            </li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            <!-- Information -->
            <div class="tab-pane fade show active" id="info">
              <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">

                  <!-- Header -->
                  <h3 class="fw-bold mb-3">Personal Information</h3>
                  <!-- Info Grid -->
                  <div class="row g-4">

                    <ul class="list-group list-group-flush">
                      <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">NIK</span>
                        <span class="text-primary">{{ user?.nik }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Email</span>
                        <span class="text-primary">{{ user?.email }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Phone</span>
                        <span class="text-primary">{{ user?.phone || '-' }}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Role</span>
                        <span class="text-primary">{{ user?.role || '-'}}</span>
                      </li>
                      <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Status</span>
                        <span
                          class="badge rounded-pill px-3"
                          :class="user?.status === 1
                            ? 'bg-success'
                            : 'bg-danger'"
                        >
                          {{ user?.status === 1 ? 'Active':'Deactive'}}
                        </span>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Dampingan -->
            <!-- <div class="tab-pane fade" id="dampingan">
              <div class="card border-0 shadow-sm p-3 mb-4">

              </div>
            </div> -->

            <!-- Settings -->
            <div class="tab-pane fade" id="settings">
              <div class="card border-0 shadow-sm p-3 mb-4">
                <h3 class="fw-bold mb-3">Security Settings</h3>
                <button
                  class="btn btn-outline-secondary btn-sm mb-2"
                  @click="openChangePassword"
                >
                  <i class="bi bi-key me-1"></i> Change Password
                </button>
              </div>
            </div>
          </div>
        </div>
        <!-- Footer -->
        <CopyRight class="mt-auto" />
      </div>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Edit Profile</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input
                type="text"
                class="form-control"
                v-model="editForm.name"
              />
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input
                type="email"
                class="form-control"
                :value="user?.email"
                disabled
              />
            </div>

            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input
                type="text"
                class="form-control"
                v-model="editForm.phone"
              />
            </div>
          </div>

          <div class="modal-footer">
            <button
              class="btn btn-secondary btn-sm"
              data-bs-dismiss="modal"
            >
              Batal
            </button>

            <button
              class="btn btn-primary btn-sm"
              :disabled="savingProfile"
              @click="submitEditProfile"
            >
              <span
                v-if="savingProfile"
                class="spinner-border spinner-border-sm me-1"
              ></span>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- CHANGE PASSWORD MODAL -->
    <div
      class="modal fade"
      id="changePasswordModal"
      tabindex="-1"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">Change Password</h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
            ></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Password Lama</label>
              <input
                type="password"
                class="form-control"
                v-model="passwordForm.current_password"
              />
            </div>

            <div class="mb-3">
              <label class="form-label">Password Baru</label>
              <input
                type="password"
                class="form-control"
                v-model="passwordForm.password"
              />
            </div>

            <div class="mb-3">
              <label class="form-label">Konfirmasi Password Baru</label>
              <input
                type="password"
                class="form-control"
                v-model="passwordForm.password_confirmation"
              />
            </div>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary btn-sm"
              data-bs-dismiss="modal"
            >
              Batal
            </button>

            <button
              type="button"
              class="btn btn-primary btn-sm"
              :disabled="changingPassword"
              @click="submitChangePassword"
            >
              <span
                v-if="changingPassword"
                class="spinner-border spinner-border-sm me-1"
              ></span>
              Simpan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Success -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-success text-white rounded-top-4">
            <h5 class="modal-title">Berhasil</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <h5 class="mb-0">{{ successMessage || 'Konfigurasi berhasil disimpan.' }}</h5>
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
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <h5 class="mb-0">{{ errorMessage || 'Terjadi kesalahan yang tidak diketahui.' }}</h5>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
              OK
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Warning -->
    <div class="modal fade" id="warningModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header bg-secondary rounded-top-4">
            <h5 class="modal-title">Konfirmasi</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <h5 class="mb-0">{{ confirmMessage || 'Apakah anda yakin?' }}</h5>
          </div>
          <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
              OK
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal: Change Avatar -->
    <!-- <div class="modal fade" id="avatarModal" tabindex="-1">
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
    </div> -->

    <!-- Modal: Change Cover -->
    <!-- <div class="modal fade" id="coverModal" tabindex="-1">
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
    </div> -->
  </div>
</template>

<style scoped>
.info-item {
  line-height: 1.4;
}

.info-item small {
  font-size: 12px;
  letter-spacing: .3px;
}

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
import * as bootstrap from 'bootstrap'


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
      passwordForm: {
        current_password: '',
        password: '',
        password_confirmation: '',
      },
      successMessage: '',
      errorMessage: '',
      changingPassword: false,
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
      user: null,
      editForm: {
        name: '',
        phone: '',
      },
      confirmMessage:'',
      savingProfile: false,
      loadingUser: false
    }
  },
  created() {
    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  methods: {
    showModal(modalId) {
      this.$nextTick(() => {
        const el = document.getElementById(modalId)
        if (!el) return

        const modal =
          bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)

        modal.show()
      })
    },
    hideModal(modalId, callback) {
      const el = document.getElementById(modalId)
      if (!el) return

      const modal = bootstrap.Modal.getInstance(el)
      if (!modal) return

      // sekali saja
      el.addEventListener(
        'hidden.bs.modal',
        () => {
          // cleanup extra safety
          document.body.classList.remove('modal-open')
          document.body.style.removeProperty('overflow')

          document
            .querySelectorAll('.modal-backdrop')
            .forEach(b => b.remove())

          callback && callback()
        },
        { once: true }
      )

      modal.hide()
    },
    openChangePassword() {
      this.passwordForm = {
        current_password: '',
        password: '',
        password_confirmation: '',
      }

      this.$nextTick(() => {
        const modalEl = document.getElementById('changePasswordModal')
        if (!modalEl) return

        const modal = new bootstrap.Modal(modalEl, {
          backdrop: 'static',
          keyboard: false,
        })

        modal.show()
      })
    },
    async submitChangePassword() {
      this.changingPassword = true

      try {
        await axios.put(
          `${baseURL}/api/user/change-password`,
          this.passwordForm,
          {
            headers: {
              Accept: 'application/json',
              Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
          }
        )

        // tutup modal password
        this.hideModal('changePasswordModal')

        setTimeout(() => {
          this.successMessage = 'Password berhasil diubah'
          this.showModal('successModal')
        }, 300)
      } catch (err) {
        console.error(err)

        this.errorMessage =
          err.response?.data?.message || 'Gagal mengubah password'

        this.showModal('errorModal')

      } finally {
        this.changingPassword = false
      }
    },
    openEditProfile() {
      this.editForm.name  = this.user.name
      this.editForm.phone = this.user.phone
    },
    async submitEditProfile() {
      this.savingProfile = true

      try {
        const res = await axios.put(
          `${baseURL}/api/user/profile`,
          this.editForm,
          {
            headers: {
              Accept: 'application/json',
              Authorization: `Bearer ${localStorage.getItem('token')}`,
            },
          }
        )

        this.user = res.data.data

        // TUTUP MODAL EDIT → TUNGGU BENAR-BENAR TERTUTUP
        this.hideModal('editProfileModal', () => {
          this.successMessage = 'Profil berhasil diperbarui'
          this.showModal('successModal')
        })

      } catch (err) {
        console.error(err)

        this.errorMessage =
          err.response?.data?.message || 'Gagal menyimpan perubahan'

        this.showModal('errorModal')

      } finally {
        this.savingProfile = false
      }
    },
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
    /* async getWilayahUser() {
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
    }, */
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
    async loginUser() {
      this.loadingUser = true
      try {
        const res = await axios.get(`${baseURL}/api/user`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        this.user = res.data.data

      } catch (error) {
        console.error('Gagal ambil data user:', error)
      } finally {
        this.loadingUser = false
      }
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        //this.getWilayahUser(),
        this.loginUser(),
        //this.handleResize(),
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
