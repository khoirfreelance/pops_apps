<template>
  <div class="profile-wrapper">
    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <div
          class="flex-grow-1 p-4 bg-light container-fluid"
          :style="{
            backgroundImage: background ? `url(${background})` : 'none',
            backgroundSize: 'cover',
            backgroundPosition: 'center',
            backgroundAttachment: 'fixed',
          }"
        >
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
.profile-wrapper {
  /* padding-top: 56px; tinggi navbar bootstrap default */
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
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

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Profile',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
      isCollapsed: false,
    }
  },
  methods: {
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
}
</script>
