<template>
  <div class="cadre-wrapper">
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
          <!-- Welcome Card -->
          <div class="card welcome-card shadow-sm mb-4 border-0">
            <div
              class="card-body d-flex flex-column flex-md-row align-items-start py-0 justify-content-between"
            >
              <!-- Kiri: Teks Welcome -->
              <div class="text-start">
                <div class="my-3">
                  <h2 class="fw-bold mt-3 mb-0 text-white">Admin / Kader</h2>
                  <small class="text-white">
                    List daftar pengguna yang terdaftar sebagai admin untuk mengelola data
                  </small>
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Kader</li>
                  </ol>
                </nav>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img src="/src/assets/admin.png" alt="Welcome" class="img-fluid welcome-img" />
              </div>
            </div>
          </div>

          <!-- Filter -->
          <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- NIK (selalu tampil, realtime filter) -->
              <div class="col-md-6">
                <label for="nik" class="form-label">NIK</label>
                <input
                  type="text"
                  v-model="filter.nik"
                  id="nik"
                  class="form-control"
                  placeholder="Cari berdasarkan NIK"
                />
              </div>
              <div class="col-md-6">
                <label for="no_tpk" class="form-label">No. TPK</label>
                <input
                  type="text"
                  v-model="filter.no_tpk"
                  id="no_tpk"
                  class="form-control"
                  placeholder="Cari berdasarkan No. Kartu Keluarga"
                />
              </div>

              <!-- Expandable section -->
              <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                <!-- Nama -->
                <div class="col-md-6">
                  <label for="nama" class="form-label">Nama </label>
                  <input type="text" v-model="advancedFilter.nama" id="nama" class="form-control" />
                </div>

                <!-- Status -->
                <div class="col-md-3">
                  <label for="status" class="form-label">Status</label>
                  <select class="form-select shadow-sm" v-model="advancedFilter.status">
                    <option value="">Pilih</option>
                    <option value="Active">Active</option>
                    <option value="Suspended">Suspended</option>
                  </select>
                </div>

                <!-- Role-->
                <div class="col-md-3">
                  <label for="role" class="form-label">Role</label>
                  <select class="form-select shadow-sm" v-model="advancedFilter.role">
                    <option value="">Pilih</option>
                    <option value="Admin">Admin</option>
                    <option value="Bidan">Bidan</option>
                    <option value="Kader PKK">Kader PKK</option>
                    <option value="Kader KB">Kader KB</option>
                  </select>
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

          <!-- Form -->
          <div class="container-fluid mt-4">
            <!-- Expand/Collapse Button -->
            <div class="text-end mb-3">
              <button
                type="button"
                class="btn btn-primary mx-3"
                @click="toggleExpandForm"
              >
                <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
              </button>
              <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="bi bi-filetype-csv"></i> Import Data Pengguna
              </button>
            </div>

            <!-- Collapsible Form -->
            <div v-if="isFormOpen" id="formData" class="card shadow-sm">
              <div class="card-body">
                <form class="row g-4">
                  <!-- No TPK -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">No. TPK <small class="text-additional2 fw-normal">(kosongkan jika pengguna bukan anggota TPK)</small></label>
                    <input type="number" min="0" class="form-control shadow-sm" v-model="form.no_tpk" />
                  </div>
                  <div class="col-md-6"></div>
                  <!-- NIK -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">NIK</label>
                    <input
                      type="text"
                      class="form-control shadow-sm"
                      v-model="form.nik"
                      @input="form.nik = form.nik.replace(/\D/g, '')"
                      maxlength="16"
                    />
                  </div>

                  <!-- Nama Lengkap -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                    <input type="text" class="form-control shadow-sm" v-model="form.nama" />
                  </div>

                  <!-- Email -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Email</label>
                    <input type="text" class="form-control shadow-sm" v-model="form.email" />
                  </div>

                  <!-- Phone -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">No Telepon</label>
                    <input type="text" class="form-control shadow-sm" v-model="form.phone" />
                  </div>

                  <!-- Password -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                      <input
                        :type="showPassword ? 'text' : 'password'"
                        class="form-control shadow-sm"
                        v-model="form.password"
                      />
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="toggle('password')"
                      >
                        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Konfirmasi Password -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Konfirmasi Password</label>
                    <div class="input-group">
                      <input
                        :type="showConfirm ? 'text' : 'password'"
                        class="form-control shadow-sm"
                        v-model="form.confirm_password"
                      />
                      <button
                        type="button"
                        class="btn btn-outline-secondary"
                        @click="toggle('confirm')"
                      >
                        <i :class="showConfirm ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
                      </button>
                    </div>
                    <!-- Pesan error -->
                    <small v-if="passwordMismatch" class="text-danger">
                      Konfirmasi password tidak sama dengan password
                    </small>
                    <small v-else-if="passwordMatch" class="text-success">
                      Password cocok ✔
                    </small>
                  </div>

                  <!-- Role & Unit Posyandu -->
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Role</label>
                    <select class="form-select shadow-sm" v-model="form.role">
                      <option value="">Pilih</option>
                      <option value="Admin">Admin</option>
                      <option value="Bidan">Bidan</option>
                      <option value="Kader PKK">Kader PKK</option>
                      <option value="Kader KB">Kader KB</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Unit Posyandu</label>
                    <template v-if="form.unit_posyandu === '__new__'">
                      <input
                        type="text"
                        class="form-control shadow-sm"
                        v-model="form.unit_posyandu_new"
                        placeholder="Tambah unit posyandu baru"
                      />
                    </template>
                    <template v-else>
                      <select
                        class="form-select shadow-sm"
                        v-model="form.unit_posyandu"
                        @change="loadPosyandu"
                      >
                        <option value="">Pilih</option>
                        <option v-for="item in posyanduList" :key="item.nama" :value="item.nama">
                          {{ item.nama }}
                        </option>
                        <option value="__new__">+ Tambah baru</option>
                      </select>
                    </template>
                  </div>

                  <!-- Provinsi -->
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Provinsi</label>
                    <template v-if="form.provinsi === '__new__'">
                      <input
                        type="text"
                        class="form-control shadow-sm"
                        v-model="form.provinsi_new"
                        placeholder="Tambah provinsi baru"
                      />
                    </template>
                    <template v-else>
                      <select
                        class="form-select shadow-sm"
                        v-model="form.provinsi"
                        @change="loadKota"
                        :readonly="isKKChecked"
                      >
                        <option value="">Pilih</option>
                        <option v-for="item in provinsiList" :key="item.nama" :value="item.nama">
                          {{ item.nama }}
                        </option>
                        <option value="__new__">+ Tambah baru</option>
                      </select>
                    </template>
                  </div>

                  <!-- Kota -->
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Kota</label>
                    <template v-if="form.kota === '__new__'">
                      <input
                        type="text"
                        class="form-control shadow-sm"
                        v-model="form.kota_new"
                        placeholder="Tambah kota baru"
                      />
                    </template>
                    <template v-else>
                      <select
                        class="form-select shadow-sm"
                        v-model="form.kota"
                        @change="loadKecamatan"
                        :readonly="isKKChecked"
                      >
                        <option value="">Pilih</option>
                        <option v-for="item in kotaList" :key="item.nama" :value="item.nama">
                          {{ item.nama }}
                        </option>
                        <option value="__new__">+ Tambah baru</option>
                      </select>
                    </template>
                  </div>

                  <!-- Kecamatan -->
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                    <template v-if="form.kecamatan === '__new__'">
                      <input
                        type="text"
                        class="form-control shadow-sm"
                        v-model="form.kecamatan_new"
                        placeholder="Tambah kecamatan baru"
                      />
                    </template>
                    <template v-else>
                      <select
                        class="form-select shadow-sm"
                        v-model="form.kecamatan"
                        @change="loadKelurahan"
                      >
                        <option value="">Pilih</option>
                        <option v-for="item in kecamatanList" :key="item.nama" :value="item.nama">
                          {{ item.nama }}
                        </option>
                        <option value="__new__">+ Tambah baru</option>
                      </select>
                    </template>
                  </div>

                  <!-- Kelurahan -->
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Kelurahan</label>
                    <template v-if="form.kelurahan === '__new__'">
                      <input
                        type="text"
                        class="form-control shadow-sm"
                        v-model="form.kelurahan_new"
                        placeholder="Tambah kelurahan baru"
                      />
                    </template>
                    <template v-else>
                      <select
                        class="form-select shadow-sm"
                        v-model="form.kelurahan"
                        :readonly="isKKChecked"
                      >
                        <option value="">Pilih</option>
                        <option v-for="item in kelurahanList" :key="item.nama" :value="item.nama">
                          {{ item.nama }}
                        </option>
                        <option value="__new__">+ Tambah baru</option>
                      </select>
                    </template>
                  </div>
                </form>
              </div>

              <!-- Actions -->
              <div class="card-footer bg-white d-flex justify-content-between">
                <button
                  class="btn btn-light border rounded-pill px-4"
                  @click="resetForm"
                >
                  <i class="bi bi-x-circle me-2"></i> Batal
                </button>
                <button
                  class="btn btn-success rounded-pill px-4"
                  @click="modalMode === 'add' ? saveData() : updateData()"
                >
                  <i class="bi bi-save me-2"></i> {{ modalMode === 'add' ? 'Simpan' : 'Ubah' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="container-fluid">
            <div class="card modern-card mt-4" v-if="isPendingOpen">
              <div class="card-body bg-additional rounded">
                <div class="d-flex justify-content-between">
                  <h5 class="fw-bold mb-2 text-white">Data Pending</h5>
                  <button @click="toggleExpandPending" class="btn-close"></button>
                </div>

                <EasyDataTable
                  :headers="headers_pending"
                  :items="cadrePending"
                >
                  <template #item-action="{ id,tipe }">
                    <button
                      class="btn btn-secondary m-2"
                      @click="updateCadre(id,tipe)"
                      style="font-size: small;"
                    >
                      <i class="fa fa-pen"></i>
                    </button>
                  </template>
                </EasyDataTable>
              </div>
            </div>
            <div class="card modern-card mt-4">
              <div class="card-body">
                <div class="table-responsive">
                  <EasyDataTable
                    :headers="headers"
                    :items="filteredCadre"
                    buttons-pagination
                    :rows-per-page="5"
                    table-class="table-modern"
                    theme-color="var(--bs-primary)"
                  >
                    <!-- Render kolom action sebagai HTML -->
                    <template #item-action="item">
                      <a
                        v-if="item && item.no_tpk"
                        :href="`?no_tpk=${item.no_tpk}`"
                        class="btn btn-secondary"
                      >
                        <i class="fa fa-pencil"></i>
                      </a>
                      <span v-else>-</span>
                    </template>
                  </EasyDataTable>
                </div>
              </div>
            </div>
          </div>
        </div>
        <CopyRight class="mt-auto" />
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
          <h5 class="modal-title">Import File TPK</h5>
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
  name: 'Cadre',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable },
  data() {
    return {
      showPassword: false,
      showConfirm: false,
      provinsiList: [],
      kotaList: [],
      kecamatanList: [],
      kelurahanList: [],
      posyanduList: [],
      modalMode: "add",
      isFormOpen: false,
      isPendingOpen: false,
      isCollapsed: false,
      isFilterOpen: false,
      importTitle: 'Import File',
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      form: {
        nik: '',
        no_tpk: '',
        nama: '',
        email: '',
        phone: '',
        role: '',
        unit_posyandu: '',
        unit_posyandu_new: '',
        password: '',
        confirm_password: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kelurahan_new: '',
        kecamatan_new: '',
        kota_new: '',
        provinsi_new: '',
        status: 1,
      },
      cadre: [],
      cadrePending: [],
      headers: [
        { text: 'NIK', value: 'nik' },
        { text: 'No TPK', value: 'no_tpk' },
        { text: 'Nama', value: 'nama' },
        { text: 'Status', value: 'status' },
        { text: 'No Telepon', value: 'phone' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'role' },
        { text: 'Unit Posyandu', value: 'unit_posyandu' },
        { text: 'Action', value: 'action' },
      ],
      headers_pending: [
        { text: 'NIK', value: 'nik' },
        { text: 'Nama', value: 'nama' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'role' },
        { text: 'Tipe Pending', value: 'tipe' },
        { text: 'Action', value: 'action' },
      ],
      // filter
      filter: {
        nik: '',
        no_tpk: '',
      },
      advancedFilter: {
        nama: '',
        status: '',
        role: '',
      },
      appliedFilter: {}, // hasil filter simpan di sini
    }
  },
  computed: {
    pendingCount() {
      return this.cadrePending.length
    },
    filteredCadre() {
      return this.cadre.filter((item) => {
        return (
          // NIK realtime
          (!this.filter.nik || item.nik.includes(this.filter.nik)) &&
          (!this.filter.no_tpk || item.no_tpk.includes(this.filter.no_tpk)) &&
          // Advanced filter hanya aktif setelah "Cari"
          (!this.appliedFilter.nama ||
            item.nama.toLowerCase().includes(this.appliedFilter.nama.toLowerCase())) &&
          (!this.appliedFilter.status || item.status === this.appliedFilter.status) &&
          (!this.appliedFilter.role || item.role === this.appliedFilter.role)
        )
      })
    },
    passwordMismatch() {
      return (
        this.form.confirm_password !== '' &&
        this.form.confirm_password !== this.form.password
      );
    },
    passwordMatch(){
      return (
        this.form.password !== '' &&
        this.form.confirm_password !== '' &&
        this.form.confirm_password === this.form.password
      );
    },
  },
  methods: {
    toggle(field) {
      if (field === 'password') {
        this.showPassword = !this.showPassword;
        // optional: fokus kembali ke field
        this.$nextTick(() => {
          const el = document.querySelector('input[aria-describedby="btnTogglePassword"]');
          if (el) el.focus();
        });
      } else if (field === 'confirm') {
        this.showConfirm = !this.showConfirm;
        this.$nextTick(() => {
          const el = document.querySelector('input[aria-describedby="btnToggleConfirm"]');
          if (el) el.focus();
        });
      }
    },
    async loadPosyandu() {
      try {
        const res = await axios.get("http://localhost:8000/api/posyandu",{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // isi list dari API
        this.posyanduList = res.data;

      } catch (err) {
        console.error("Error load posyandu:", err);
      }
    },
    async loadCadre() {
      try {
        const res = await axios.get('http://localhost:8000/api/cadre',{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.cadre = res.data
        console.log('kader:'+ this.cadre);
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    async loadProvinsi() {
      try {
        const res = await axios.get("http://localhost:8000/api/region/provinsi");

        // isi list dari API
        this.provinsiList = res.data;

      } catch (err) {
        console.error("Error load provinsi:", err);
      }
    },
    async loadKota() {
      if (this.form.provinsi && this.form.provinsi !== "__new__") {
        const res = await axios.get("http://localhost:8000/api/region/kota", {
          params: { provinsi: this.form.provinsi }
        });
        this.kotaList = res.data;
        this.kecamatanList = [];
        this.kelurahanList = [];
        this.form.kota = "";
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKecamatan() {
      if (this.form.kota && this.form.kota !== "__new__") {
        const res = await axios.get("http://localhost:8000/api/region/kecamatan", {
          params: { provinsi: this.form.provinsi, kota: this.form.kota }
        });
        this.kecamatanList = res.data;
        this.kelurahanList = [];
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKelurahan() {
      if (this.form.kecamatan && this.form.kecamatan !== "__new__") {
        const res = await axios.get("http://localhost:8000/api/region/kelurahan", {
          params: {
            provinsi: this.form.provinsi,
            kota: this.form.kota,
            kecamatan: this.form.kecamatan
          }
        });
        this.kelurahanList = res.data;
        this.form.kelurahan = "";
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
    toggleExpandForm() {
      this.isFormOpen = !this.isFormOpen
    },
    toggleExpandPending() {
      this.isPendingOpen = !this.isPendingOpen
    },
    openTambah() {
      this.modalMode = "add"
      this.form = {} // reset form
      this.isFormOpen = true
    },
    resetForm() {
      this.form = {
        id: null,
        nik: '',
        no_tpk: '',
        nama: '',
        email: '',
        phone: '',
        role: '',
        unit_posyandu: '',
        password: '',
        confirm_password: '',
        kelurahan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kelurahan_new: '',
        kecamatan_new: '',
        kota_new: '',
        provinsi_new: '',
        status: '',
      },
      this.isFormOpen = false
    },
    applyFilter() {
      // copy isi advancedFilter ke appliedFilter saat tombol Cari ditekan
      this.appliedFilter = { ...this.advancedFilter }
    },
    resetFilter() {
      this.filter.nik = ''
      this.filter.no_tpk = ''
      this.advancedFilter = {
        nama: '',
        status: '',
        role: '',
      }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    async saveData() {
      this.isLoadingImport = true
      this.importProgress = 0
      this.animatedProgress = 0

      try {
        console.log("Payload sebelum dikirim:", this.form) // 👈 cek dulu isi form

        this.form.provinsi = this.form.provinsi === "__new__" ? this.form.provinsi_new : this.form.provinsi;
        this.form.kota = this.form.kota === "__new__" ? this.form.kota_new : this.form.kota;
        this.form.kecamatan = this.form.kecamatan === "__new__" ? this.form.kecamatan_new : this.form.kecamatan;
        this.form.kelurahan= this.form.kelurahan === "__new__" ? this.form.kelurahan_new : this.form.kelurahan;
        this.form.unit_posyandu= this.form.unit_posyandu === "__new__" ? this.form.unit_posyandu_new : this.form.unit_posyandu;

        // simpan ke backend
        await axios.post('http://localhost:8000/api/cadre', this.form,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        // refresh table
        //await this.getPendingData()
        await this.resetForm()
        await this.loadCadre()

        setTimeout(() => (this.showAlert = false), 3000)

      } catch (e) {
        console.error('Gagal simpan data:', e)
      }finally{
        this.isLoadingImport = false
      }
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

          this.cadre.push({
            no_tpk: obj.no_tpk || '',
            nik: obj.nik || '',
            nama: obj.nama || '',
            email: obj.email || '',
            phone: obj.phone || '',
            role: obj.role || '',
            unit_posyandu: obj.unit_posyandu || '',
            status: obj.status || 'Active',
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
    /* async getPendingData() {
      try {
        const res = await axios.get("http://localhost:8000/api/cadre/pending",{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.cadrePending = res.data
        //console.log("Pending data:", this.familyPending)
      } catch (err) {
        console.error("Gagal fetch pending data:", err)
      }
    }, */

  },
  mounted() {
    this.loadCadre()
    this.loadProvinsi()
    this.loadPosyandu()
    //this.getPendingData()
  },
}
</script>

<style scoped>
.cadre-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.cadre-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
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
