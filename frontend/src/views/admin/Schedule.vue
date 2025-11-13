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
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"   />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Welcome Card -->
          <Welcome />

          <div class="container-fluid my-2 d-flex justify-content-center">
            <ul
              class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-100"
              id="myTab"
              role="tablist"
              style="max-width: 800px;"
            >
              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link active w-100 text-truncate"
                  id="anak-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#anak-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="anak-tab-pane"
                  aria-selected="true"
                  @click="menu('anak')"
                >
                  Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button
                  class="nav-link w-100 text-truncate"
                  id="bumil-tab"
                  data-bs-toggle="tab"
                  data-bs-target="#bumil-tab-pane"
                  type="button"
                  role="tab"
                  aria-controls="bumil-tab-pane"
                  aria-selected="false"
                  @click="menu('bumil')"
                >
                  Ibu Hamil
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <!-- Import Anak -->
            <div class="tab-pane fade show active" id="anak-tab-pane" role="tabpanel" tabindex="0">
              <div class="card shadow-sm">
                <div class="card-body">
                  <label class="form-label fw-semibold">Upload Intervensi Anak</label>
              <div class="alert alert-success">
                <ul>
                  <li>Pastikan data yang diimport, berformat csv</li>
                  <li>Pastikan data sudah lengkap sebelum di import</li>
                  <li>Silahkan unduh contoh dengan klik <a href="/example_intervensi_anak.csv">Example.csv</a></li>
                </ul>
              </div>
              <div
                class="dropzone-full position-relative p-4 rounded-3 border text-center"
                :class="{
                  'border-primary bg-light': isDataDrag,
                  'border-danger': fileError
                }"
                @click="triggerFileDialog"
                @dragover.prevent="onDragOver"
                @dragleave.prevent="onDragLeave"
                @drop.prevent="handleDrop($event)"
                role="button"
                tabindex="0"
                @keydown.enter.prevent="triggerFileDialog"
                @keydown.space.prevent="triggerFileDialog"
              >
                <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                <p class="mb-1 fw-medium">Drag & drop file CSV di sini</p>
                <small class="text-muted">atau klik untuk pilih file</small>

                <!-- Invisible input (terikat ke parent relatif) -->
                <input
                  ref="fileInput"
                  type="file"
                  accept=".csv,text/csv"
                  class="position-absolute w-100 h-100 top-0 start-0 opacity-0"
                  @change="handleFileChange($event)"
                />
              </div>

              <!-- Preview / status -->
              <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div v-if="file">
                  <div><strong>Nama:</strong> {{ fileName }}</div>
                  <div><strong>Ukuran:</strong> {{ humanFileSize(fileSize) }}</div>
                  <div v-if="filePreviewLines" class="text-muted small">Contoh baris pertama: <code>{{ filePreviewLines }}</code></div>
                </div>

                <div v-else class="text-muted small">Belum ada file dipilih</div>

                <div class="d-flex gap-2">
                  <button
                    v-if="file && !uploading"
                    class="btn btn-outline-danger btn-sm"
                    @click="removeFile"
                    type="button"
                  >
                    <i class="bi bi-trash me-1"></i> Hapus
                  </button>

                  <button
                    v-if="file && !uploading"
                    class="btn btn-success btn-sm"
                    @click="uploadCSV"
                    type="button"
                  >
                    <i class="bi bi-upload me-1"></i> Upload
                  </button>

                  <div v-if="uploading" class="d-flex align-items-center gap-2">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <small class="text-muted">Mengunggah... {{ uploadProgress }}%</small>
                  </div>
                </div>
              </div>

              <!-- Error message -->
              <div v-if="fileError" class="mt-2 text-danger small">
                {{ fileError }}
              </div>
                </div>
              </div>
            </div>
            <!-- Import Bumil -->
            <div class="tab-pane fade" id="bumil-tab-pane" role="tabpanel" tabindex="0">
              <div class="card shadow-sm">
                <div class="card-body">
                  <label class="form-label fw-semibold">Upload Intervensi Ibu Hamil (CSV)</label>
                  <div class="alert alert-success">
                    <ul>
                      <li>Pastikan data yang diimport, berformat csv</li>
                      <li>Pastikan data sudah lengkap sebelum di import</li>
                      <li>Silahkan unduh contoh dengan klik <a href="/example_intervensi_bumil.csv">Example.csv</a></li>
                    </ul>
                  </div>
                  <div
                    class="dropzone-full position-relative p-4 rounded-3 border text-center"
                    :class="{
                      'border-primary bg-light': isDataDrag,
                      'border-danger': fileError
                    }"
                    @click="triggerFileDialog"
                    @dragover.prevent="onDragOver"
                    @dragleave.prevent="onDragLeave"
                    @drop.prevent="handleDrop($event)"
                    role="button"
                    tabindex="0"
                    @keydown.enter.prevent="triggerFileDialog"
                    @keydown.space.prevent="triggerFileDialog"
                  >
                    <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                    <p class="mb-1 fw-medium">Drag & drop file CSV di sini</p>
                    <small class="text-muted">atau klik untuk pilih file</small>

                    <!-- Invisible input (terikat ke parent relatif) -->
                    <input
                      ref="fileInput"
                      type="file"
                      accept=".csv,text/csv"
                      class="position-absolute w-100 h-100 top-0 start-0 opacity-0"
                      @change="handleFileChange($event)"
                    />
                  </div>

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="file">
                      <div><strong>Nama:</strong> {{ fileName }}</div>
                      <div><strong>Ukuran:</strong> {{ humanFileSize(fileSize) }}</div>
                      <div v-if="filePreviewLines" class="text-muted small">Contoh baris pertama: <code>{{ filePreviewLines }}</code></div>
                    </div>

                    <div v-else class="text-muted small">Belum ada file dipilih</div>

                    <div class="d-flex gap-2">
                      <button
                        v-if="file && !uploading"
                        class="btn btn-outline-danger btn-sm"
                        @click="removeFile"
                        type="button"
                      >
                        <i class="bi bi-trash me-1"></i> Hapus
                      </button>

                      <button
                        v-if="file && !uploading"
                        class="btn btn-success btn-sm"
                        @click="uploadCSV"
                        type="button"
                      >
                        <i class="bi bi-upload me-1"></i> Upload
                      </button>

                      <div v-if="uploading" class="d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <small class="text-muted">Mengunggah... {{ uploadProgress }}%</small>
                      </div>
                    </div>
                  </div>

                  <!-- Error message -->
                  <div v-if="fileError" class="mt-2 text-danger small">
                    {{ fileError }}
                  </div>
                </div>
              </div>
            </div>
            <!-- Import Catin -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              <div class="card shadow-sm">
                <div class="card-body">
                  <label class="form-label fw-semibold">Upload Data Calon Pengantin (CSV)</label>
                  <div class="alert alert-success">
                    <ul>
                      <li>Pastikan data yang diimport, berformat csv</li>
                      <li>Pastikan data sudah lengkap sebelum di import</li>
                      <li>Silahkan unduh contoh dengan klik <a href="/ex_catin.csv">Example.csv</a></li>
                    </ul>
                  </div>
                  <div
                    class="dropzone-full position-relative p-4 rounded-3 border text-center"
                    :class="{
                      'border-primary bg-light': isDataDrag,
                      'border-danger': fileError
                    }"
                    @click="triggerFileDialog"
                    @dragover.prevent="onDragOver"
                    @dragleave.prevent="onDragLeave"
                    @drop.prevent="handleDrop($event)"
                    role="button"
                    tabindex="0"
                    @keydown.enter.prevent="triggerFileDialog"
                    @keydown.space.prevent="triggerFileDialog"
                  >
                    <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                    <p class="mb-1 fw-medium">Drag & drop file CSV di sini</p>
                    <small class="text-muted">atau klik untuk pilih file</small>

                    <!-- Invisible input (terikat ke parent relatif) -->
                    <input
                      ref="fileInput"
                      type="file"
                      accept=".csv,text/csv"
                      class="position-absolute w-100 h-100 top-0 start-0 opacity-0"
                      @change="handleFileChange($event)"
                    />
                  </div>

                  <!-- Preview / status -->
                  <div class="mt-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div v-if="file">
                      <div><strong>Nama:</strong> {{ fileName }}</div>
                      <div><strong>Ukuran:</strong> {{ humanFileSize(fileSize) }}</div>
                      <div v-if="filePreviewLines" class="text-muted small">Contoh baris pertama: <code>{{ filePreviewLines }}</code></div>
                    </div>

                    <div v-else class="text-muted small">Belum ada file dipilih</div>

                    <div class="d-flex gap-2">
                      <button
                        v-if="file && !uploading"
                        class="btn btn-outline-danger btn-sm"
                        @click="removeFile"
                        type="button"
                      >
                        <i class="bi bi-trash me-1"></i> Hapus
                      </button>

                      <button
                        v-if="file && !uploading"
                        class="btn btn-success btn-sm"
                        @click="uploadCSV"
                        type="button"
                      >
                        <i class="bi bi-upload me-1"></i> Upload
                      </button>

                      <div v-if="uploading" class="d-flex align-items-center gap-2">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <small class="text-muted">Mengunggah... {{ uploadProgress }}%</small>
                      </div>
                    </div>
                  </div>

                  <!-- Error message -->
                  <div v-if="fileError" class="mt-2 text-danger small">
                    {{ fileError }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Table -->

        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Schedule',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, Welcome },
  data() {
    return {
      activeMenu: 'anak', // default tampilan awal
      isCollapsed: false,
      file: null,
      fileName: '',
      fileSize: 0,
      filePreviewLines: '',
      fileError: '',
      uploading: false,
      uploadProgress: 0,
      // config
      ACCEPTED_EXT: ['csv'],
      ACCEPTED_MIME: ['text/csv', 'application/vnd.ms-excel', 'text/plain'],
      MAX_FILE_SIZE: 5 * 1024 * 1024, // 5 MB
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
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    menu(type) {
      this.activeMenu = type
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
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
    },
    setFile(file) {
      this.fileError = ''
      // validasi
      const valid = this.validateFile(file)
      if (!valid.valid) {
        this.file = null
        this.fileName = ''
        this.fileSize = 0
        this.filePreviewLines = ''
        this.fileError = valid.message
        return
      }

      this.file = file
      this.fileName = file.name
      this.fileSize = file.size
      this.fileError = ''

      // baca beberapa byte pertama untuk preview (opsional)
      this.previewFileContent(file)
    },
    validateFile(file) {
      // ext
      const nameParts = (file.name || '').split('.')
      const ext = nameParts.length > 1 ? nameParts.pop().toLowerCase() : ''
      if (!this.ACCEPTED_EXT.includes(ext)) {
        return { valid: false, message: 'Format file tidak didukung. Hanya .csv yang diperbolehkan.' }
      }

      // mime (beberapa browser pakai text/plain)
      if (this.ACCEPTED_MIME.length && !this.ACCEPTED_MIME.includes(file.type) && file.type !== '') {
        // dimungkinkan file.type kosong di beberapa OS, jadi jangan terlalu strict
        return { valid: false, message: 'Tipe file tidak valid (MIME mismatch).' }
      }

      if (file.size > this.MAX_FILE_SIZE) {
        return { valid: false, message: `Ukuran file terlalu besar. Maks ${this.humanFileSize(this.MAX_FILE_SIZE)}.` }
      }

      return { valid: true }
    },
    previewFileContent(file) {
      const reader = new FileReader()
      reader.onload = (ev) => {
        const text = (ev.target.result || '').toString()
        // ambil 1-2 baris pertama untuk preview, sanitasi
        const lines = text.split(/\r?\n/).filter(Boolean)
        this.filePreviewLines = lines.length ? lines.slice(0,2).join(' | ') : ''
      }
      // baca sebagian saja untuk efisiensi (readAsText membaca seluruh file — acceptable untuk CSV kecil)
      reader.readAsText(file.slice(0, 2000))
    },
    async uploadCSV() {
      if (!this.file) {
        this.fileError = 'Tidak ada file untuk di-upload.'
        return
      }

      // 🧭 Tentukan endpoint sesuai menu aktif
      let UPLOAD_URL = ''
      if (this.activeMenu === 'anak') {
        UPLOAD_URL = `${baseURL}/api/children/import_intervensi`
      } else if (this.activeMenu === 'bumil') {
        UPLOAD_URL = `${baseURL}/api/pregnancy/import_intervensi`
      } else {
        this.fileError = 'Menu tidak dikenal. Pastikan kamu memilih menu yang benar.'
        return
      }

      const formData = new FormData()
      formData.append('file', this.file)

      try {
        this.uploading = true
        this.uploadProgress = 0
        this.fileError = ''

        await axios.post(UPLOAD_URL, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          },
          onUploadProgress: (progressEvent) => {
            if (progressEvent.lengthComputable) {
              this.uploadProgress = Math.round(
                (progressEvent.loaded * 100) / progressEvent.total
              )
            }
          }
        })

        // ✅ Respons sukses
        if (this.$bvToast) {
          this.$bvToast.toast('Upload CSV berhasil diproses.', {
            variant: 'success',
            solid: true
          })
        }

        // 🔄 Reset input file setelah upload
        this.removeFile()
      } catch (err) {
        console.error('Upload error:', err)
        this.fileError =
          (err.response && err.response.data && err.response.data.message) ||
          'Gagal upload file. Periksa format CSV atau koneksi server.'
      } finally {
        this.uploading = false
        this.uploadProgress = 0
      }
    },
    triggerFileDialog() {
      this.$refs.fileInput.click()
    },
    handleFileChange(e) {
      const file = e.target.files[0]
      this.loadFilePreview(file)
    },
    handleDrop(e) {
      const file = e.dataTransfer.files[0]
      this.loadFilePreview(file)
      this.isDataDrag = false
    },
    onDragOver() {
      this.isDataDrag = true
    },
    onDragLeave() {
      this.isDataDrag = false
    },
    removeFile() {
      this.file = null
      this.fileName = ''
      this.fileSize = 0
      this.filePreviewLines = ''
      this.$refs.fileInput.value = ''
    },
    humanFileSize(size) {
      const i = Math.floor(Math.log(size) / Math.log(1024))
      return (
        (size / Math.pow(1024, i)).toFixed(2) * 1 +
        ' ' +
        ['B', 'kB', 'MB', 'GB', 'TB'][i]
      )
    },
    async loadFilePreview(file) {
      if (!file) return
      if (!file.name.endsWith('.csv')) {
        this.fileError = 'Hanya file CSV yang diperbolehkan.'
        return
      }

      this.file = file
      this.fileName = file.name
      this.fileSize = file.size
      this.fileError = ''

      const text = await file.text()
      const lines = text.split('\n').slice(0, 2).join(' | ')
      this.filePreviewLines = lines
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        //this.loadConfigWithCache(),
        //this.loadBride(),
        this.getWilayahUser(),
        this.handleResize(),

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

<style scoped>

</style>
