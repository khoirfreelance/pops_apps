<template>
  <div class="cadre-wrapper">
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
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar"  />

      <!-- Main Content -->
      <div class="flex-grow-1 d-flex flex-column overflow-hidden" style="background: white !important;">

        <!-- Content -->
        <div class="py-4 container-fluid">

          <!-- Welcome Card -->
          <Welcome />

          <!-- Form -->
          <div v-if="isFormOpen" id="formData" class="card p-3 my-3 shadow-sm">
            <div class="card-body">
              <form class="row g-4">
                <!-- No TPK -->
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">No. TPK <small class="text-additional2 fw-normal">(kosongkan jika pengguna bukan anggota TPK)</small></label>
                  <select
                    class="form-select shadow-sm"
                    v-model="form.no_tpk"
                    @change="loadTPK"
                  >
                    <option value="">Pilih</option>
                    <option v-for="item in tpkList" :key="item.no_tpk" :value="item.no_tpk">
                      {{ item.no_tpk }}
                    </option>
                  </select>
                  <!-- <template  v-if="form.no_tpk === '__new__'">
                    <input
                      type="number"
                      min="0"
                      class="form-control shadow-sm"
                      v-model="form.no_tpk_new"
                      placeholder="Tambah No. TPK baru"
                    />
                  </template>
                  <template v-else>
                    <select
                      class="form-select shadow-sm"
                      v-model="form.no_tpk"
                      @change="loadTPK"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in tpkList" :key="item.no_tpk" :value="item.no_tpk">
                        {{ item.no_tpk }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template> -->
                  <!-- <input type="number" min="0" class="form-control shadow-sm" v-model="form.no_tpk" /> -->
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
                  <label class="form-label small fw-semibold text-secondary">No. Telepon</label>
                  <input
                    type="text"
                    class="form-control shadow-sm"
                    v-model="form.phone"
                    @input="form.phone = form.phone.replace(/[^0-9]/g, '').slice(0, 15)"
                    placeholder="Nomor Telepon"
                  />
                </div>

                <!-- Password -->
                <div class="col-md-6" v-if="modalMode !== 'update'">
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
                <div class="col-md-6" v-if="modalMode !== 'update'">
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

                <!-- Provinsi -->
                <div class="col-md-3">
                  <label class="form-label small fw-semibold text-secondary">Provinsi</label>
                  <select
                    class="form-select shadow-sm"
                    v-model="form.provinsi"
                    @change="loadKota"
                  >
                    <option value="">Pilih</option>
                    <option v-for="item in provinsiList" :key="item.nama" :value="item.nama">
                      {{ item.nama }}
                    </option>
                  </select>
                  <!-- <template v-if="form.provinsi === '__new__'">
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

                    >
                      <option value="">Pilih</option>
                      <option v-for="item in provinsiList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template> -->
                </div>

                <!-- Kota -->
                <div class="col-md-3">
                  <label class="form-label small fw-semibold text-secondary">Kota</label>
                  <select
                      class="form-select shadow-sm"
                      v-model="form.kota"
                      @change="loadKecamatan"
                      :disabled="kotaDisabled"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kotaList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                    </select>
                  <!-- <template v-if="form.kota === '__new__'">
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
                      :disabled="kotaDisabled"

                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kotaList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template> -->
                </div>

                <!-- Kecamatan -->
                <div class="col-md-3">
                  <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                  <select
                    class="form-select shadow-sm"
                    v-model="form.kecamatan"
                    @change="loadKelurahan"
                    :disabled="kecamatanDisabled"
                  >
                    <option value="">Pilih</option>
                    <option v-for="item in kecamatanList" :key="item.nama" :value="item.nama">
                      {{ item.nama }}
                    </option>
                  </select>
                  <!-- <template v-if="form.kecamatan === '__new__'">
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
                      :disabled="kecamatanDisabled"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kecamatanList" :key="item.nama" :value="item.nama">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template> -->
                </div>

                <!-- Kelurahan -->
                <div class="col-md-3">
                  <label class="form-label small fw-semibold text-secondary">Kelurahan</label>
                  <select
                    class="form-select shadow-sm"
                    v-model="form.kelurahan"
                    @change="handleRegionChange"
                    :disabled="kelurahanDisabled"
                  >
                    <option value="">Pilih</option>
                    <option v-for="item in kelurahanList" :key="item.idWilayah" :value="item.idWilayah">
                      {{ item.nama }}
                    </option>
                  </select>
                  <!-- <template v-if="form.kelurahan === '__new__'">
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
                      @change="handleRegionChange"
                      :disabled="kelurahanDisabled"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in kelurahanList" :key="item.idWilayah" :value="item.idWilayah">
                        {{ item.nama }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template> -->
                </div>

                <!-- Role & Unit Posyandu -->
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
                      :disabled="posyanduDisabled"
                    >
                      <option value="">Pilih</option>
                      <option v-for="item in posyanduList" :key="item.nama_posyandu" :value="item.nama_posyandu">
                        {{ item.nama_posyandu }}
                      </option>
                      <option value="__new__">+ Tambah baru</option>
                    </select>
                  </template>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-semibold text-secondary">Role</label>
                  <select class="form-select shadow-sm" v-model="form.role">
                    <option value="">Pilih</option>
                    <option value="Super Admin">Super Admin</option>
                    <option value="Admin">Admin</option>
                    <option value="Bidan">Bidan</option>
                    <option value="Kader PKK">Kader PKK</option>
                    <option value="Kader KB">Kader KB</option>
                  </select>
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

          <!-- Table -->
          <div class="card my-3 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between gap-2 my-2">
                <h3 class="text-primary"> Data Admin</h3>
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <input
                    type="text"
                    class="form-control form-control-sm "
                    style="width: 220px;"
                    placeholder="Ketik Nama atau NIK"
                    v-model="searchQuery"
                  >
                  <select
                    class="form-select form-select-sm "
                    style="width: 220px;"
                    v-model="filterStatus"
                  >
                    <option value="">Pilih Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="non aktif">Non Aktif</option>
                  </select>
                  <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    @click="toggleExpandForm"
                  >
                    <i :class="isFormOpen ? 'bi bi-dash-square' : 'bi bi-plus-square'"></i>
                    {{ isFormOpen ? 'Tutup Form' : 'Tambah Data' }}
                  </button>
                </div>
              </div>
              <div class="table-responsive">
                <easy-data-table
                  :headers="headers"
                  :items="items"
                  :sortable="true"
                  :rows-per-page="perPage"
                  :rows-items="perPageOptions"
                  :rows-per-page-text="'Rows per page'"
                  header-text-direction="center"
                  table-class-name="my-custom-table"
                  header-class-name="my-custom-header"
                  show-index alternating
                  border-cell
                >
                  <template #item-index="{ index }">
                    <div class="text-center fw-semibold">
                      {{ index }}
                    </div>
                  </template>

                  <template #item-action="{ id, email,nama, status }">
                    <!-- Kalau user aktif -->
                    <template v-if="status === 'Aktif'">
                      <div class="text-center">
                        <button
                          class="btn btn-primary m-2"
                          @click="updateCadre(id)"
                          style="font-size: small;"
                          data-bs-toggle="tooltip"
                          title="Update Data Kader"
                        >
                          <i class="fa fa-pen"></i>
                        </button>
                        <button
                          class="btn btn-secondary m-2"
                          @click="deactive(email,nama)"
                          style="font-size: small;"
                          data-bs-toggle="tooltip"
                          title="Nonaktif Kader"
                        >
                          <i class="fa fa-eye-slash"></i>
                        </button>
                      </div>
                    </template>

                    <!-- Kalau user nonaktif -->
                    <template v-else>
                      <div class="text-center">
                        <button
                          class="btn btn-primary m-2"
                          @click="active(email,nama)"
                          style="font-size: small;"
                          data-bs-toggle="tooltip"
                          title="Aktifkan Kader"
                        >
                          <i class="fa fa-eye"></i>
                        </button>
                        <button
                          class="btn btn-danger m-2"
                          @click="deleteUser(email,nama)"
                          style="font-size: small;"
                          data-bs-toggle="tooltip"
                          title="Hapus Kader"
                        >
                          <i class="fa fa-trash"></i>
                        </button>
                      </div>
                    </template>
                  </template>
                </easy-data-table>

              </div>
            </div>
          </div>
        </div>
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>

  <!-- Modal Success -->
  <!-- <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div
        class="modal-content border-0 shadow-lg rounded-4"
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
  </div> -->

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
import axios from 'axios'
import Welcome from '@/components/Welcome.vue'
import Swal from 'sweetalert2'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Cadre',
  components: { CopyRight, NavbarAdmin, HeaderAdmin, EasyDataTable, Welcome },
  data() {
    return {
      searchQuery:'',
      filterStatus:'',
      currentPage: 1,
      perPage: 10,
      perPageOptions: [5, 10, 25, 50],
      progressLevel:null,
      roleList: [],
      tpkList:[],
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
      showPassword: false,
      showConfirm: false,
      provinsiList: [],
      kotaList: [],
      kecamatanList: [],
      kelurahanList: [],
      posyanduList: [],
      modalMode: "add",
      isFormOpen: false,
      //isPendingOpen: false,
      isFilterOpen: false,
      showAlert: false,
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 1,
      kotaDisabled: true,
      kecamatanDisabled: true,
      kelurahanDisabled: true,
      posyanduDisabled: true,
      form: {
        id: null,
        idTPK: null,
        nik: '',
        no_tpk_new: '',
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
      /* headers_pending: [
        { text: 'NIK', value: 'nik' },
        { text: 'Nama', value: 'nama' },
        { text: 'Email', value: 'email' },
        { text: 'Role', value: 'role' },
        { text: 'Action', value: 'action' },
      ], */
      // filter
      filter: {
        nik: '',
        no_tpk: '',
        status: 'Aktif',
      },
      advancedFilter: {
        nama: '',
        role: '',
      },
      appliedFilter: {}, // hasil filter simpan di sini
    }
  },
  created() {

    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  computed: {
    pendingCount() {
      return this.cadrePending.length
    },
    filteredCadre() {
      if (!this.cadre) return []

      const arr = Array.isArray(this.cadre)
        ? this.cadre
        : Object.values(this.cadre)

      const q = (this.searchQuery ?? "").toLowerCase().trim()
      const s = (this.filterStatus ?? "").toLowerCase().trim()

      return arr.filter(item => {
        const nik = (item.nik ?? "").toLowerCase()
        const nama = (item.nama ?? "").toLowerCase()
        const status = (item.status ?? "").toLowerCase()

        // SEARCH (OR)
        const matchSearch =
          !q ||
          nik.includes(q) ||
          nama.includes(q)

        // FILTER STATUS (AND)
        const matchStatus =
          !s || status === s

        return matchSearch && matchStatus
      })
    },
    items() {
      return Object.values(this.filteredCadre)
        .sort((a, b) => a.id - b.id)
        .map(item => ({
          id: item.id,
          nik: item.nik,
          nama: item.nama,
          status: item.status,
          phone: item.phone,
          role: item.role,
          email: item.email,
          no_tpk: item.no_tpk,
          unit_posyandu: item.unit_posyandu,
          action: { ...item }
        }))
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
    getKelurahanNameById(id) {
      const found = this.kelurahanList.find(
        item => item.idWilayah === id
      )

      if (found) {
        this.form.idWilayah = found.idWilayah
        return found.nama
      }

      return ''
    },
    handleRegionChange() {
      const idWilayah = this.form.kelurahan
      //console.log(idWilayah);
      this.posyanduDisabled = false
      // 🔁 DEFAULT / ALL
      this.fetchPosyanduByWilayah(idWilayah)
    },
    async fetchPosyanduByWilayah(id_wilayah) {
      if (!id_wilayah) {
        console.warn('ID wilayah kosong, tidak bisa fetch posyandu')
        return
      }

      try {
        const res = await axios.get(`${baseURL}/api/posyandu/${id_wilayah}/wilayah`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        //console.log(res.data);

        this.posyanduList = res.data?.data || res.data || []
        //console.log("Posyandu list:", this.posyanduList);
      } catch (error) {
        console.error('Gagal mengambil data posyandu:', error)
        this.posyanduList = []
      }
    },
    async loadTPK(){
      try {
        const res = await axios.get(`${baseURL}/api/member/tpk`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        //console.log('data: '+res.data);

        this.tpkList = res.data
      } catch (e) {
        console.error('Gagal ambil data:', e)
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
    handleResize() {
      this.windowWidth = window.innerWidth
      if (this.windowWidth < 992) {
        this.isCollapsed = true // auto collapse di tablet/mobile
      } else {
        this.isCollapsed = false // normal lagi di desktop
      }
    },
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
    /* async loadPosyandu() {
      try {
        const res = await axios.get(`${baseURL}/api/posyandu`,{
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
    }, */
    async loadCadre() {
      try {
        const res = await axios.get(`${baseURL}/api/cadre`,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })
        this.cadre = res.data
        //console.log('kader:'+ this.cadre);
      } catch (e) {
        console.error('Gagal ambil data:', e)
      }
    },
    async loadProvinsi() {
      try {
        const res = await axios.get(`${baseURL}/api/region/provinsi`);

        // isi list dari API
        this.provinsiList = res.data;

      } catch (err) {
        console.error("Error load provinsi:", err);
      }
    },
    async loadKota() {
      this.kotaDisabled = false
      if (this.form.provinsi && this.form.provinsi !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kota`, {
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
      this.kecamatanDisabled = false
      if (this.form.kota && this.form.kota !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kecamatan`, {
          params: { provinsi: this.form.provinsi, kota: this.form.kota }
        });
        this.kecamatanList = res.data;
        this.kelurahanList = [];
        this.form.kecamatan = "";
        this.form.kelurahan = "";
      }
    },
    async loadKelurahan() {
      this.kelurahanDisabled = false
      if (this.form.kecamatan && this.form.kecamatan !== "__new__") {
        const res = await axios.get(`${baseURL}/api/region/kelurahan`, {
          params: {
            provinsi: this.form.provinsi,
            kota: this.form.kota,
            kecamatan: this.form.kecamatan
          }
        });
        //console.log(res.data);

        this.kelurahanList = res.data;
        this.form.kelurahan = "";
      }
    },
    /* closeModal(id) {
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
    }, */
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
      if (!this.isFormOpen) this.resetForm()
    },
    /* toggleExpandPending() {
      this.isPendingOpen = !this.isPendingOpen
    }, */
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
        idWilayah:'',
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
        role: '',
      }
      this.appliedFilter = {}
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    normalizeFormPayload(form) {
      const normalized = { ...form }

      // convert kelurahan ID ke nama
      if (normalized.kelurahan && normalized.kelurahan !== '__new__') {
        normalized.kelurahan = this.getKelurahanNameById(normalized.kelurahan)
      }

      Object.keys(normalized).forEach(key => {
        if (key.endsWith('_new')) {
          const baseKey = key.replace('_new', '')
          const value = normalized[key]

          if (value !== null && value !== '' && value !== undefined) {
            normalized[baseKey] = value
          }

          delete normalized[key]
        }
      })

      return normalized
    },
    capitalizeName(name) {
      return (name || '')
        .toLowerCase()
        .replace(/\b\w/g, c => c.toUpperCase())
    },
    async saveData() {
      this.isLoadingImport = true
      this.importProgress = 10
      this.animatedProgress = 10

      try {
        const payload = this.normalizeFormPayload(this.form)
        const nama = payload.nama
        console.log("Payload sebelum dikirim:", payload)
        // simpan ke backend
        const res = await axios.post(`${baseURL}/api/cadre`, payload,{
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        })

        this.importProgress = 70
        this.animatedProgress = 70
        await this.loadCadre()
        this.importProgress = 90
        this.animatedProgress = 90
        this.resetForm()
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

        // matikan loading
        this.isLoadingImport = false
        console.log(res.data.message);
        Swal.fire({
          icon: 'success',
          html: `Data <b>${this.capitalizeName(nama)}</b> berhasil ditambahkan`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })
      } catch (e) {
        this.isLoadingImport = false
        this.importProgress = 0
        this.animatedProgress = 0
        console.error('Gagal simpan data:', e)
        Swal.fire({
          title: 'Error',
          html: e.data?.error || 'Terjadi kesalahan input data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }finally{
        this.isLoadingImport = false
      }
    },
    async updateCadre(id) {
      this.modalMode = "update";
      this.isFormOpen = true
      try {
        const res = await axios.get(`${baseURL}/api/cadre/${id}`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        const data = res.data;
        console.log('data show: '+data);

        // mapping ke form sesuai struktur
        this.form = {
          statusUser: data.isActive,
          idTPK: data.idTPK,
          id: data.id,
          nik: data.nik,
          no_tpk: data.no_tpk,
          nama: data.nama,
          email: data.email,
          phone: data.phone,
          role: data.role,
          unit_posyandu: data.unit_posyandu,
          password: '',
          confirm_password: '',
          kelurahan: data.kelurahan,
          kecamatan: data.kecamatan,
          kota: data.kota,
          provinsi: data.provinsi,
        };

        // pastikan listnya ke-load dulu sebelum set value
        await this.loadKota()
        this.form.kota = data.kota

        await this.loadKecamatan()
        this.form.kecamatan = data.kecamatan

        await this.loadKelurahan()

        const selectedKel = this.kelurahanList.find(
          k => k.nama === data.kelurahan
        )

        this.form.kelurahan = selectedKel ? selectedKel.idWilayah : ''

        //this.form.kelurahan = data.kelurahan
        console.log('data: ',data);

        this.fetchPosyanduByWilayah(selectedKel.idWilayah)

        this.isFormOpen = true
      } catch (err) {
        console.error("Gagal load data kader:", err);
      }
    },
    async updateData() {
      try {
        this.isLoadingImport = true
        this.importProgress = 10
        this.animatedProgress = 10

        const payload = this.normalizeFormPayload(this.form)
        const nama = this.form.nama
        const res = await axios.put(
          `${baseURL}/api/cadre/${this.form.id}`,
          payload, // data body
          {
            headers: {
              Accept: 'application/json',
              Authorization: `Bearer ${localStorage.getItem('token')}`
            }
          }
        )

        this.importProgress = 70
        this.animatedProgress = 70
        this.isFormOpen = false
        this.loadCadre()
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

        // matikan loading
        this.isLoadingImport = false
        console.log(res.data.message);
        Swal.fire({
          icon: 'success',
          html: `Data <b>${this.capitalizeName(nama)}</b> berhasil diperbarui`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })

      } catch (err) {
        console.error("Gagal update:", err)

        this.isLoadingImport = false
        this.importProgress = 0
        this.animatedProgress = 0
        Swal.fire({
          title: 'Error',
          html: err.data?.error || 'Terjadi kesalahan input data',
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }
    },
    async deactive(email,nama) {
      try {
        this.isLoadingImport = true
        this.importProgress = 10
        this.animatedProgress = 10

        await axios.put(
          `${baseURL}/api/cadre/deactive/${email}`,
          {}, // body kosong
          {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
          }
        );

        this.importProgress = 70
        this.animatedProgress = 70
        this.isFormOpen = false
        this.loadCadre()
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

        // matikan loading
        this.isLoadingImport = false
        Swal.fire({
          icon: 'success',
          html: `Akun <strong>${nama}</strong> berhasil dinon-aktifkan`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })
        //this.getPendingData()

      } catch (err) {
        console.error("Gagal deactive data kader:", err.response?.data || err);
        this.isLoadingImport = false
        this.importProgress = 0
        this.animatedProgress = 0
        Swal.fire({
          title: 'Error',
          html: `Gagal menon-akifkan <strong>${nama}</strong>`,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }
    },
    async active(email,nama) {
      try {
        this.isLoadingImport = true
        this.importProgress = 10
        this.animatedProgress = 10

        await axios.put(
          `${baseURL}/api/cadre/active/${email}`,
          {}, // body kosong
          {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
          }
        );

        this.importProgress = 70
        this.animatedProgress = 70
        this.isFormOpen = false
        this.loadCadre()
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

        // matikan loading
        this.isLoadingImport = false
        Swal.fire({
          icon: 'success',
          html: `Akun <strong>${nama}</strong> berhasil diaktifkan`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })
      } catch (err) {
        console.error("Gagal deactive data kader:", err.response?.data || err);
        this.isLoadingImport = false
        this.importProgress = 0
        this.animatedProgress = 0
        Swal.fire({
          title: 'Error',
          html: `Gagal mengakifkan <strong>${nama}</strong>`,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }
    },
    async deleteUser(email,nama) {
      try {
        this.isLoadingImport = true
        this.importProgress = 10
        this.animatedProgress = 10
        await axios.put(
          `${baseURL}/api/cadre/delete/${email}`,
          {}, // body kosong
          {
            headers: {
              Accept: "application/json",
              Authorization: `Bearer ${localStorage.getItem("token")}`,
            },
          }
        );

        this.importProgress = 70
        this.animatedProgress = 70
        this.isFormOpen = false
        this.loadCadre()
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

        // matikan loading
        this.isLoadingImport = false
        Swal.fire({
          icon: 'success',
          html: `Akun <strong>${nama}</strong> berhasil dihapus`,
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1'
          }
        })
      } catch (err) {
        console.error("Gagal menghapus data kader:", err.response?.data || err);
        this.isLoadingImport = false
        this.importProgress = 0
        this.animatedProgress = 0
        Swal.fire({
          title: 'Error',
          html: `Gagal menghapus <strong>${nama}</strong>`,
          icon: 'error',
          confirmButtonText: 'OK',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-danger mx-1',
          }
        })
      }
    },

  },
  async mounted() {
    this.isLoading = true
    try {
      await Promise.all([
        this.loadConfigWithCache(),
        this.loadCadre(),
        this.loadProvinsi(),
        //this.loadPosyandu(),
        this.loadTPK(),
        //this.getPendingData(),
        //this.getWilayahUser(),
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
.table-scroll-x {
  overflow-x: auto !important;
  overflow-y: hidden !important;
  width: 100% !important;
  display: block !important;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.25s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
  transform: translateY(-5px);
}

.action-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
}

.my-custom-table {
  --easy-table-header-background-color: #b4dbc6;
}

.my-custom-table .col-action {
  text-align: center !important;
}

.my-custom-table td.col-action {
  display: flex !important;
  justify-content: center !important;
  align-items: center !important;
}
</style>
