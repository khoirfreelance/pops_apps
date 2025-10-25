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
    <HeaderAdmin/>

     <div
      class="content flex-grow-1 d-flex flex-column flex-md-row"
      :class="{
        'sidebar-collapsed': isCollapsed,
        'sidebar-expanded': !isCollapsed
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"/>

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid" >

          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between">
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <h3>
                  <span class="fw-normal fs-6">Selamat datang,</span> <br />
                  {{ username }}
                </h3>
                <img
                  v-if="logoLoaded"
                  :src="logoSrc"
                  alt="Logo"
                  height="50"
                  class="mt-4"
                  @error="logoLoaded = false"
                />
                <!-- jika gagal load logo, tampilkan kelurahan -->
                <span
                  v-else
                  class="text-muted fw-bold fs-5 mt-4"
                >
                  {{ kelurahan || 'Wilayah' }}
                </span>
                <p class="small d-flex align-items-center mt-1">
                  Data terakhir diperbarui pada &nbsp;<strong>{{ today }}</strong>
                </p>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img
                  src="/banner.png"
                  alt="Welcome"
                  class="img-fluid welcome-img"
                  style="max-width: 280px"
                />
              </div>
            </div>
          </div>

          <!-- Filter -->
          <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- NIK (selalu tampil, realtime filter) -->
              <div class="col-md-12">
                <label for="nik" class="form-label">NIK</label>
                <input
                  type="text"
                  v-model="filter.nik"
                  id="nik"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK"
                />
              </div>

              <!-- Expandable section -->
              <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                <!-- Nama -->
                <div class="col-md-4">
                  <label for="nama" class="form-label">Nama</label>
                  <input type="text" v-model="advancedFilter.nama" id="nama" class="form-control" />
                </div>

                <!-- RT -->
                <div class="col-md-2">
                  <label for="rt" class="form-label">RT</label>
                  <input type="number" v-model="advancedFilter.rt" id="rt" class="form-control" />
                </div>

                <!-- RW -->
                <div class="col-md-2">
                  <label for="rw" class="form-label">RW</label>
                  <input type="number" v-model="advancedFilter.rw" id="rw" class="form-control" />
                </div>

                <!-- Kunjungan -->
                <div class="col-md-4">
                  <label for="kunjungan" class="form-label">Kunjungan</label>
                  <input
                    type="date"
                    v-model="advancedFilter.kunjungan"
                    id="kunjungan"
                    class="form-control"
                  />
                </div>

                <!-- Tombol -->
                <div class="col-md-12">
                  <button type="submit" class="btn btn-primary float-start" @click="applyFilter">
                    <i class="bi bi-search"></i> Cari
                  </button>
                  <button type="button" class="btn btn-secondary float-end" @click="resetFilter">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                  </button>
                </div>
              </div>
            </form>

            <!-- Expand/Collapse Button -->
            <div class="text-end mt-2">
              <button type="button" class="btn btn-outline-secondary btn-sm" @click="toggleExpand">
                <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
              </button>
            </div>
          </div>

          <!-- Button Group -->
          <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
              <i class="bi bi-plus-square"></i> Tambah Data
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
              <i class="bi bi-filetype-csv"></i> Import Pendampingan TPK (bumil)
            </button>
          </div>

          <!-- Alert -->
          <!-- <div class="container-fluid mt-4">
            <div class="alert alert-success shadow-sm mb-2">
              <i class="bi bi-info-circle-fill"></i>&nbsp; Sebanyak 110 data tidak tercatat di data
              keluarga
              <router-link
                to="/admin/keluarga"
                class="text-decoration-none fw-semibold text-primary-50"
              >
                Lihat Data . . .
              </router-link>
            </div>
          </div> -->

          <!-- Table -->
          <div class="container-fluid">
            <div class="card modern-card mt-4">
              <div class="card-body">
                <div class="table-responsive">
                  <EasyDataTable
                    :headers="headers"
                    :items="filteredBumil"
                    buttons-pagination
                    :rows-per-page="5"
                    table-class="table-modern"
                    theme-color="var(--bs-primary)"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>

  <!-- Modal Tambah -->
  <div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div
        class="modal-content shadow-lg border-0 rounded-4"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title fw-bold text-primary">Tambah Data Ibu Hamil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form class="row g-4" @submit.prevent="saveData">
            <!-- NIK -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form.nik"
                maxlength="16"
                @input="form.nik = form.nik.replace(/\D/g, '')"
                required
              />
            </div>

            <!-- Nama -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama</label>
              <input type="text" class="form-control shadow-sm" v-model="form.nama" required />
            </div>

            <!-- Usia -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Usia</label>
              <input
                type="number"
                min="15"
                class="form-control bg-light shadow-sm"
                v-model="form.usia"
              />
            </div>

            <!-- Anemia -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Anemia</label>
              <select class="form-select shadow-sm" v-model="form.anemia">
                <option value="-">Tidak</option>
                <option value="Ya">Ya</option>
              </select>
            </div>

            <!-- Kehamilan -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Kehamilan Beresiko</label>
              <select class="form-select shadow-sm" v-model="form.kehamilan">
                <option value="-">Tidak</option>
                <option value="Ya">Ya</option>
              </select>
            </div>

            <!-- KEK -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">KEK</label>
              <select class="form-select shadow-sm" v-model="form.kek">
                <option value="-">Tidak</option>
                <option value="Ya">Ya</option>
              </select>
            </div>

            <!-- Alamat -->
            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">Alamat</label>
              <textarea class="form-control shadow-sm" v-model="form.alamat" rows="2"></textarea>
            </div>

            <!-- RT -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">RT</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form.rt" />
            </div>

            <!-- RW -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">RW</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form.rw" />
            </div>

            <!-- Kunjungan -->
            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">Tanggal Kunjungan</label>
              <input type="date" class="form-control shadow-sm" v-model="form.kunjungan" />
            </div>
          </form>
        </div>

        <div class="modal-footer border-0 d-flex justify-content-between">
          <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i> Batal
          </button>
          <button class="btn btn-success rounded-pill px-4" @click="saveData">
            <i class="bi bi-save me-2"></i> Simpan
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Import -->
  <div class="modal fade" id="modalImport" ref="modalImport" tabindex="-1">
    <div class="modal-dialog">
      <div
        class="modal-content"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">Import File Ibu Hamil</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Import data untuk kunjungan kehamilan oleh pendampingan TPK</li>
              <li>Pastikan data yang diimport, berformat csv</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
            </ul>
          </div>
          <input type="file" class="form-control" ref="csvFile" accept=".csv" />
        </div>
        <div class="modal-footer border-0 d-flex justify-content-between">
          <button class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i> Batal
          </button>
          <button class="btn btn-success rounded-pill px-4" @click="handleImport">
            <i class="bi bi-upload me-2"></i> Unggah
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Success -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content border-0 shadow-lg rounded-4"
        :style="{
          backgroundImage: background ? `url(${background})` : 'none',
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundAttachment: 'fixed',
        }"
      >
        <div class="modal-header bg-success text-white rounded-top-4">
          <h5 class="modal-title">✅ Berhasil</h5>
          <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="modal"
            aria-label="Close"
          ></button>
        </div>
        <div class="modal-body text-center">
          <p class="mb-0">Data Anak berhasil disimpan ke <strong>localStorage</strong>.</p>
        </div>
        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-success rounded-pill px-4" data-bs-dismiss="modal">
            OK
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loader Overlay with Animated Progress -->
  <div
    v-if="isLoadingImport"
    class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-50"
    style="z-index: 2000"
  >
    <div class="w-50">
      <div class="progress" style="height: 1.8rem; border-radius: 1rem; overflow: hidden">
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          role="progressbar"
          :style="{ width: importProgress + '%' }"
          :data-progress="progressLevel"
        >
          <span class="fw-bold">{{ animatedProgress }}%</span>
        </div>
      </div>
    </div>
    <p class="text-white mt-3">Mengimpor data... {{ currentRow }}/{{ totalRows }} baris</p>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { Modal } from 'bootstrap'
import axios from 'axios'

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Pregnancy',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
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
      isFilterOpen: false,
      importTitle: 'Import File',
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1, // default 1 agar tidak bagi 0
      form: {
        nik: '',
        nama: '',
        anemia: '',
        kehamilan: '',
        kek: '',
        alamat: '',
        rt: '',
        rw: '',
        usia: '',
        kunjungan: '',
      },
      bumil: [
        {
          nik: '3276012309870001',
          nama: 'Dummyah',
          anemia: '-',
          kehamilan: '-',
          kek: '-',
          alamat: 'Jl. Damai 3 No. 36',
          rt: '5',
          rw: '2',
          usia: '28',
          kunjungan: '2025-08-10',
        },
        {
          nik: '1266792309870001',
          nama: 'Dina K',
          anemia: '-',
          kehamilan: '-',
          kek: '-',
          alamat: 'Jl. Damai 3 No. 42',
          rt: '5',
          rw: '2',
          usia: '21',
          kunjungan: '2025-08-10',
        },
      ],
      headers: [
        { text: 'NIK', value: 'nik' },
        { text: 'Nama', value: 'nama' },
        { text: 'Anemia', value: 'anemia' },
        { text: 'Kehamilan Beresiko', value: 'kehamilan' },
        { text: 'KEK', value: 'kek' },
        { text: 'Usia', value: 'usia' },
        { text: 'Alamat', value: 'alamat' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Kunjungan Terakhir', value: 'kunjungan' },
      ],
      // filter
      filter: {
        nik: '',
      },
      advancedFilter: {
        nama: '',
        rt: '',
        rw: '',
        kunjungan: '',
      },
      appliedFilter: {}, // hasil filter simpan di sini
    }
  },
  computed: {
    background() {
      try {
        const config = JSON.parse(localStorage.getItem('siteConfig'))
        return config?.background || null
      } catch {
        return null
      }
    },
    filteredBumil() {
      return this.bumil.filter((item) => {
        return (
          // NIK realtime
          (!this.filter.nik || item.nik.includes(this.filter.nik)) &&
          // Advanced filter hanya aktif setelah "Cari"
          (!this.appliedFilter.nama ||
            item.nama.toLowerCase().includes(this.appliedFilter.nama.toLowerCase())) &&
          (!this.appliedFilter.rt || item.rt === this.appliedFilter.rt) &&
          (!this.appliedFilter.rw || item.rw === this.appliedFilter.rw) &&
          (!this.appliedFilter.kunjungan || item.kunjungan === this.appliedFilter.kunjungan)
        )
      })
    },
  },
  methods: {
    async getWilayahUser() {
      try {
        const res = await axios.get('http://localhost:8000/api/user/region', {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const wilayah = res.data
        this.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        await this.fetchPosyanduByWilayah(this.id_wilayah)
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
    closeModal(id) {
      const el = document.getElementById(id)
      if (el) {
        const instance = Modal.getOrCreateInstance(el)
        instance.hide()
      }

      // jaga-jaga kalau backdrop masih nyangkut
      setTimeout(() => {
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove())
        document.body.classList.remove('modal-open')
        document.body.style.removeProperty('overflow')
        document.body.style.removeProperty('padding-right')
      }, 300) // delay biar nunggu animasi fade
    },
    updateProgressBar(percent, row, total) {
      this.importProgress = percent
      this.currentRow = row
      this.totalRows = total

      const start = this.animatedProgress
      const end = percent
      const step = (end - start) / 10
      let i = 0
      const interval = setInterval(() => {
        this.animatedProgress = Math.min(end, Math.round(start + step * i))
        i++
        if (this.animatedProgress >= end) clearInterval(interval)
      }, 30)
    },
    toggleExpand() {
      this.isFilterOpen = !this.isFilterOpen
    },
    applyFilter() {
      // copy isi advancedFilter ke appliedFilter saat tombol Cari ditekan
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter.nik = ''
      this.advancedFilter = {
        nama: '',
        rt: '',
        rw: '',
        kunjungan: '',
      }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    saveData() {
      this.closeModal('modalTambah')
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0
      this.currentRow = 0
      this.totalRows = 1 // hanya 1 record, bisa disesuaikan kalau batch

      // simulasi progress bertahap
      let step = 0
      const interval = setInterval(() => {
        step += 10
        this.importProgress = Math.min(step, 100)
        this.animatedProgress = this.importProgress
        this.currentRow = Math.round((this.totalRows * this.importProgress) / 100)

        if (this.importProgress >= 100) {
          clearInterval(interval)

          // lanjut simpan data

          this.bumil.push({ ...this.form })
          this.showAlert = true
          setTimeout(() => (this.showAlert = false), 3000)

          // reset form
          this.form = {
            nik: '',
            nama: '',
            usia: 0,
            anemia: '',
            kehamilan: '',
            kek: '',
            rt: '',
            rw: '',
            kunjungan: '',
          }

          this.$nextTick(() => {
            const el = document.getElementById('successModal')
            if (el) {
              const instance = Modal.getOrCreateInstance(el)
              instance.show()
            }
          })

          this.isLoadingImport = false
        }
      }, 150) // jeda antar progress
    },
    handleImport() {
      this.closeModal('modalImport')

      const fileInput = this.$refs.csvFile
      if (!fileInput || !fileInput.files.length) return

      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      const file = fileInput.files[0]
      const reader = new FileReader()

      reader.onload = (e) => {
        const text = e.target.result
        const rows = text
          .split('\n')
          .map((r) => r.trim())
          .filter((r) => r)
        const headers = rows[0].split(',').map((h) => h.trim())
        const total = rows.length - 1
        this.totalRows = total

        rows.slice(1).forEach((row, idx) => {
          const values = row.split(',').map((v) => v.trim())
          const obj = {}
          headers.forEach((h, i) => {
            obj[h] = values[i] || ''
          })

          this.bumil.push({
            nik: obj.nik || '',
            nama: obj.nama || '',
            anemia: obj.anemia || '',
            kehamilan: obj.kehamilan || '',
            kek: obj.kek || '',
            alamat: obj.alamat || '',
            rt: obj.rt || '',
            rw: obj.rw || '',
            usia: obj.usia || '',
            kunjungan: obj.kunjungan || '',
          })

          const percent = Math.round(((idx + 1) / total) * 100)
          this.updateProgressBar(percent, idx + 1, total)
        })

        setTimeout(() => {
          this.isLoadingImport = false
          const el = document.getElementById('successModal')
          const instance = Modal.getOrCreateInstance(el)
          instance.show()
        }, 500)
      }

      reader.readAsText(file)
    },
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
  async mounted() {
    this.isLoading = true

    try {
      // Ambil data dulu
      await this.getWilayahUser()
      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('Error loading data:', err)
    } finally {
      // Kasih sedikit delay biar animasi fade spinner kelihatan
      setTimeout(() => {
        this.isLoading = false
      }, 300)
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
}
</script>

<style scoped>

.filter-wrapper {
  position: relative; /* biar ikut alur layout */
  z-index: 0; /* pastikan di bawah sidebar */
  margin-top: -30px !important;
  width: 97%;
  border-radius: 0.75rem;
}
/* Hilangkan garis pemisah antara sidebar dan content */
.flex-grow-1 {
  border-left: none !important;
  background-color: #f9f9fb;
}
.breadcrumb-item + .breadcrumb-item::before {
  color: rgba(255, 255, 255, 0.7);
}
/* Smooth Apple-like Modal */
.modern-modal {
  border-radius: 1.5rem;
  border: 1px solid #eaeaea;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  background: #fff;
  transition: all 0.3s ease-in-out;
}

/* Form lebih clean */
.form-control-modern,
.form-select.form-control-modern {
  border-radius: 0.75rem;
  border: 1px solid #ddd;
  padding: 0.6rem 1rem;
  transition:
    border-color 0.2s,
    box-shadow 0.2s;
}

.form-control-modern:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2);
}

/* Animasi modal lebih halus */
.modal.fade .modal-dialog {
  transform: translateY(20px);
  transition:
    transform 0.3s ease-out,
    opacity 0.3s ease-out;
}

.modal.fade.show .modal-dialog {
  transform: translateY(0);
  opacity: 1;
}
.modern-card {
  border-radius: 1rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  border: none;
}
/* Header */
.table-modern th {
  background-color: var(--bs-primary) !important; /* primary */
  color: #fff !important;
  font-weight: 600;
  padding: 0.75rem;
  text-align: left;
}

/* Cell */
.table-modern td {
  vertical-align: middle;
  padding: 0.65rem 0.75rem;
  border-bottom: 1px solid #f1f1f1;
}

/* Row hover */
.table-modern tr:hover {
  background-color: rgba(13, 110, 253, 0.08) !important;
  transition: background 0.2s ease-in-out;
}

/* Pagination & footer */
.table-modern .pagination {
  margin-top: 1rem;
}

.table-modern .pagination .page-link {
  border-radius: 0.5rem;
  color: var(--bs-primary);
}

.table-modern .pagination .active .page-link {
  background-color: #6c757d; /* secondary */
  border-color: #6c757d;
  color: #fff;
}
</style>
