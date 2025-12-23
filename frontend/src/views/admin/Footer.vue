<template>
  <div class="wrapper">

    <!-- Loading -->
    <transition name="fade">
      <div v-if="isLoading" class="spinner-overlay d-flex justify-content-center align-items-center">
        <div class="spinner-border text-primary" style="width:4rem;height:4rem"></div>
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

          <!-- ======================
               FOOTER LOGO
          ======================= -->
          <div class="card border-0 shadow-sm rounded-4 mb-4">
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

        <CopyRight class="mt-5"/>
      </div>
    </div>
  </div>
</template>

<style scoped>
.logo-preview {
  padding: 16px;
  border-radius: 8px;
  background:
    linear-gradient(45deg, #e5e7eb 25%, transparent 25%),
    linear-gradient(-45deg, #e5e7eb 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, #e5e7eb 75%),
    linear-gradient(-45deg, transparent 75%, #e5e7eb 75%);
  background-size: 16px 16px;
  background-position: 0 0, 0 8px, 8px -8px, -8px 0px;
  display: inline-block;
}

.logo-img {
  max-height: 80px;
  max-width: 200px;
}
</style>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'

const baseURL = 'http://localhost:8000'

export default {
  name: 'FooterAdmin',
  components: { CopyRight, HeaderAdmin, NavbarAdmin, Welcome },

  data() {
    return {
      isLoading: false,
      isCollapsed: false,

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
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
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
    }

  },

  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadFooter(),
        this.loadLinks()
      ])
    } finally {
      this.isLoading = false
    }
  }
}
</script>
