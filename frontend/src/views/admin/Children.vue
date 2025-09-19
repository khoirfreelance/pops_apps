<template>
  <div class="nutrition-wrapper">
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
                  <h2 class="fw-bold mt-3 mb-0 text-white">Data Anak</h2>
                  <small class="text-white">
                    List daftar anak yang terdaftar di dalam posyandu
                  </small>
                </div>
                <nav aria-label="breadcrumb" class="mt-auto mb-2">
                  <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                      <router-link to="/admin" class="text-decoration-none text-white-50">
                        Beranda
                      </router-link>
                    </li>
                    <li class="breadcrumb-item active text-white" aria-current="page">Data Anak</li>
                  </ol>
                </nav>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img src="/src/assets/admin.png" alt="Welcome" class="img-fluid welcome-img" />
              </div>
            </div>
          </div>

          <!-- Tab Sub Menu-->
          <div class="mt-5">
            <div class="d-flex justify-content-center">
              <ul class="nav nav-pills mb-3" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link active"
                    id="kelahiran-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#kelahiran-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="kelahiran-tab-pane"
                    aria-selected="true"
                  >
                    Data Kelahiran
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="kunjungan-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#kunjungan-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="kunjungan-tab-pane"
                    aria-selected="false"
                  >
                    Data Kunjungan Posyandu
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="intervensi-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#intervensi-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="intervensi-tab-pane"
                    aria-selected="false"
                  >
                    Data Intervensi
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="tpk-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tpk-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="tpk-tab-pane"
                    aria-selected="false"
                  >
                    Data Pendampingan TPK
                  </button>
                </li>
              </ul>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="myTabContent">
              <!-- Tab Kelahiran-->
              <div
                class="tab-pane fade show active"
                id="kelahiran-tab-pane"
                role="tabpanel"
                tabindex="0"
              >
                <!-- Filter -->
                <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
                  <form class="row g-3 align-items-end" @submit.prevent="applyFilter_anak">
                    <!-- NIK (selalu tampil, realtime filter) -->
                    <div class="col-md-12">
                      <label for="nik" class="form-label">NIK Orang Tua</label>
                      <input
                        type="text"
                        v-model="filter_anak.nik"
                        id="nik"
                        class="form-control"
                        placeholder="Cari berdasarkan NIK Orang Tua"
                      />
                    </div>

                    <!-- Expandable section -->
                    <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">

                      <!-- KIA -->
                      <div class="col-md-3">
                        <label for="kia" class="form-label">No. KIA</label>
                        <input
                          type="text"
                          class="form-control"
                          id="no_kia"
                          v-model="advancedFilter_anak.no_kia"
                          placeholder="Nomor KIA"
                        />
                      </div>

                      <!-- Nama -->
                      <div class="col-md-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input
                          type="text"
                          class="form-control"
                          id="nama"
                          v-model="advancedFilter_anak.nama"
                          placeholder="Nama Anak"
                        />
                      </div>

                      <!-- Gender -->
                      <div class="col-md-2">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select
                          class="form-select"
                          id="gender"
                          v-model="advancedFilter_anak.gender"
                        >
                          <option value="">-- L/P --</option>
                          <option value="L">Laki-laki</option>
                          <option value="P">Perempuan</option>
                        </select>
                      </div>

                      <!-- Tanggal Lahir -->
                      <div class="col-md-4">
                        <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                        <input
                          type="date"
                          class="form-control"
                          id="tgl_lahir"
                          v-model="advancedFilter_anak.lahir"
                        />
                      </div>

                      <!-- Tombol -->
                      <div class="col-md-12">
                        <button
                          type="submit"
                          class="btn btn-primary float-start"
                          @click="applyAdvancedFilter_anak"
                        >
                          <i class="bi bi-search"></i> Cari
                        </button>
                        <button
                          type="button"
                          class="btn btn-secondary float-end"
                          @click="resetFilter_anak"
                        >
                          <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                      </div>
                    </div>
                  </form>

                  <!-- Expand/Collapse Button -->
                  <div class="text-end mt-2">
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm"
                      @click="toggleExpand"
                    >
                      <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                      {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
                    </button>
                  </div>
                </div>

                <!-- Button Group -->
                <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
                  <!-- Tambah Data -->
                  <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah_kelahiran"
                  >
                    <i class="bi bi-plus-square"></i> Tambah Data
                  </button>

                  <!-- Import Group -->
                  <button
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImport_kelahiran"
                  >
                    <i class="bi bi-filetype-csv"></i> Import Data Anak
                  </button>

                </div>

                <!-- Table Section -->
                <div class="container-fluid">
                  <!-- Data Table -->
                  <div class="card modern-card mt-4">
                    <div class="card-body">
                      <div class="table-responsive">
                        <EasyDataTable
                          :headers="headers_anak"
                          :items="filteredAnak_anak"
                          :search-value="filter_anak.nik"
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

              <!-- Tab Kunjungan Posyandu -->
              <div class="tab-pane fade" id="kunjungan-tab-pane" role="tabpanel" tabindex="0">
                <!-- Filter -->
                <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
                  <form class="row g-3 align-items-end" @submit.prevent="applyFilter_kunjungan">
                    <!-- NIK (selalu tampil, realtime filter) -->
                    <div class="col-md-12">
                      <label for="nik" class="form-label">NIK Orang Tua</label>
                      <input
                        type="text"
                        v-model="filter_kunjungan.nik"
                        id="nik"
                        class="form-control"
                        placeholder="Cari berdasarkan NIK Orang Tua"
                      />
                    </div>

                    <!-- Expandable section -->
                    <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                      <div class="col-md-4">
                        <label for="nama" class="form-label">Nama</label>
                        <input
                          type="text"
                          class="form-control"
                          id="nama"
                          v-model="advancedFilter_kunjungan.nama"
                          placeholder="Nama Anak"
                        />
                      </div>

                      <!-- Usia -->
                      <div class="col-md-2">
                        <label for="usia" class="form-label">Usia</label>
                        <input
                          type="number"
                          class="form-control"
                          id="usia"
                          v-model="advancedFilter_kunjungan.usia"
                          placeholder="Bulan"
                        />
                      </div>

                      <!-- Gender -->
                      <div class="col-md-2">
                        <label for="gender" class="form-label">Jenis Kelamin</label>
                        <select
                          class="form-select"
                          id="gender"
                          v-model="advancedFilter_kunjungan.gender"
                        >
                          <option value="">-- L/P --</option>
                          <option value="L">Laki-laki</option>
                          <option value="P">Perempuan</option>
                        </select>
                      </div>

                      <!-- Tanggal Kunjungan -->
                      <div class="col-md-4">
                        <label for="tgl_kunjungan" class="form-label">Tanggal Kunjungan</label>
                        <input
                          type="date"
                          class="form-control"
                          id="tgl_kunjungan"
                          v-model="advancedFilter_kunjungan.kunjungan"
                        />
                      </div>

                      <!-- Tombol -->
                      <div class="col-md-12">
                        <button
                          type="submit"
                          class="btn btn-primary float-start"
                          @click="applyAdvancedFilter_kunjungan"
                        >
                          <i class="bi bi-search"></i> Cari
                        </button>
                        <button
                          type="button"
                          class="btn btn-secondary float-end"
                          @click="resetFilter_kunjungan"
                        >
                          <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                      </div>
                    </div>
                  </form>

                  <!-- Expand/Collapse Button -->
                  <div class="text-end mt-2">
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm"
                      @click="toggleExpand"
                    >
                      <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                      {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
                    </button>
                  </div>
                </div>

                <!-- Button Group -->
                <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
                  <!-- Tambah Data -->
                  <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah_kunjungan"
                  >
                    <i class="bi bi-plus-square"></i> Tambah Data
                  </button>

                  <!-- Import Group -->
                  <button
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#modalImport_kunjungan"
                  >
                    <i class="bi bi-filetype-csv"></i> Import Data Kunjungan
                  </button>

                </div>

                <!-- Cards Section -->
                <div class="container-fluid">
                  <!-- Data Table -->
                  <div class="card modern-card mt-4">
                    <div class="card-body">
                      <div class="table-responsive">
                        <EasyDataTable
                          :headers="headers_kunjungan"
                          :items="filteredAnak_kunjungan"
                          :search-value="filter_kunjungan.nik"
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

              <!-- Tab Intervensi -->
              <div class="tab-pane fade" id="intervensi-tab-pane" role="tabpanel" tabindex="0">
                <!-- Filter -->
                <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
                  <form class="row g-3 align-items-end" @submit.prevent="applyFilter_intervensi">
                    <!-- NIK (selalu tampil, realtime filter) -->
                    <div class="col-md-12">
                      <label for="nik" class="form-label">NIK Orang Tua</label>
                      <input
                        type="text"
                        v-model="filter_intervensi.nik"
                        id="nik"
                        class="form-control"
                        placeholder="Cari berdasarkan NIK Orang Tua"
                      />
                    </div>

                    <!-- Expandable section -->
                    <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                      <!-- Nama -->
                      <div class="col-md-4">
                        <label for="nama" class="form-label">Nama</label>
                        <input
                          type="text"
                          class="form-control"
                          id="nama"
                          v-model="advancedFilter_intervensi.nama"
                          placeholder="Nama Anak"
                        />
                      </div>

                      <div class="col-md-4">
                        <label for="jenis" class="form-label">Jenis Intervensi</label>
                        <select
                          class="form-select"
                          id="jenis"
                          v-model="advancedFilter_intervensi.jenis"
                        >
                          <option value="">-- Semua Jenis --</option>
                          <option value="imunisasi">Imunisasi</option>
                          <option value="vitamin">Pemberian Vitamin</option>
                          <option value="konsultasi">Konsultasi Gizi</option>
                        </select>
                      </div>

                      <!-- Tanggal Lahir -->
                      <div class="col-md-4">
                        <label for="tgl_kunjungan" class="form-label">Tanggal Kunjungan</label>
                        <input
                          type="date"
                          class="form-control"
                          id="tgl_kunjungan"
                          v-model="advancedFilter_intervensi.tgl_kunjungan"
                        />
                      </div>

                      <!-- Tombol -->
                      <div class="col-md-12">
                        <button
                          type="submit"
                          class="btn btn-primary float-start"
                          @click="applyAdvancedFilter_intervensi"
                        >
                          <i class="bi bi-search"></i> Cari
                        </button>
                        <button
                          type="button"
                          class="btn btn-secondary float-end"
                          @click="resetFilter_intervensi"
                        >
                          <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                      </div>
                    </div>
                  </form>

                  <!-- Expand/Collapse Button -->
                  <div class="text-end mt-2">
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm"
                      @click="toggleExpand"
                    >
                      <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                      {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
                    </button>
                  </div>
                </div>

                <!-- Button Group -->
                <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
                  <!-- Tambah Data -->
                  <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah"
                  >
                    <i class="bi bi-plus-square"></i> Tambah Data
                  </button>

                  <!-- Import Group -->
                  <a
                    class="btn btn-success"
                    href="#"
                    @click.prevent="openImport('Import Data Intervensi')"
                  >
                    <i class="bi bi-filetype-csv"></i> Import Data Intervensi
                  </a>
                </div>

                <!-- Cards Section -->
                <div class="container-fluid">
                  <!-- Data Table -->
                  <div class="card modern-card mt-4">
                    <div class="card-body">
                      <div class="table-responsive">
                        <EasyDataTable
                          :headers="headers_intervensi"
                          :items="filteredAnak_intervensi"
                          :search-value="filter_intervensi.nik"
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

              <!-- Tab Pendampingan TPK -->
              <div class="tab-pane fade" id="tpk-tab-pane" role="tabpanel" tabindex="0">
                <!-- Filter -->
                <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
                  <form class="row g-3 align-items-end" @submit.prevent="applyAdvancedFilter_pendampingan">
                    <!-- NIK (selalu tampil, realtime filter) -->
                    <div class="col-md-12">
                      <label for="nik" class="form-label">NIK Orang Tua</label>
                      <input
                        type="text"
                        v-model="filter_pendamping.nik"
                        id="nik"
                        class="form-control"
                        placeholder="Cari berdasarkan NIK Orang Tua"
                      />
                    </div>

                    <!-- Expandable section -->
                    <div v-if="isFilterOpen" class="row g-3 align-items-end mt-2">
                      <div class="col-md-4">
                        <label for="nama" class="form-label">Nama</label>
                        <input
                          type="text"
                          class="form-control"
                          id="nama"
                          v-model="advancedFilter_pendamping.nama"
                          placeholder="Nama Anak"
                        />
                      </div>

                      <!-- Usia -->
                      <div class="col-md-4">
                        <label for="usia" class="form-label">Usia</label>
                        <input
                          type="number"
                          class="form-control"
                          id="usia"
                          v-model="advancedFilter_pendamping.usia"
                        />
                      </div>

                      <!-- Tanggal Pendampingan -->
                      <div class="col-md-4">
                        <label for="tgl_pendampingan" class="form-label">Tanggal Pendampingan</label>
                        <input
                          type="date"
                          class="form-control"
                          id="tgl_pendampingan"
                          v-model="advancedFilter_pendamping.tgl_pendampingan"
                        />
                      </div>

                      <!-- Tombol -->
                      <div class="col-md-12">
                        <button
                          type="submit"
                          class="btn btn-primary float-start"
                          @click="applyAdvancedFilter_pendampingan"
                        >
                          <i class="bi bi-search"></i> Cari
                        </button>
                        <button
                          type="button"
                          class="btn btn-secondary float-end"
                          @click="resetFilter_pendampingan"
                        >
                          <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                      </div>
                    </div>
                  </form>

                  <!-- Expand/Collapse Button -->
                  <div class="text-end mt-2">
                    <button
                      type="button"
                      class="btn btn-outline-secondary btn-sm"
                      @click="toggleExpand"
                    >
                      <i :class="isFilterOpen ? 'bi bi-chevron-up' : 'bi bi-chevron-down'"></i>
                      {{ isFilterOpen ? 'Tutup Filter Lain' : 'Filter Lain' }}
                    </button>
                  </div>
                </div>

                <!-- Button Group -->
                <div class="container-fluid mt-4 d-flex flex-wrap gap-2 justify-content-end">
                  <!-- Tambah Data -->
                  <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTambah_pendampingan"
                  >
                    <i class="bi bi-plus-square"></i> Tambah Data
                  </button>

                  <!-- Import Group -->
                  <a
                    class="btn btn-success"
                    href="#"
                    @click.prevent="openImport('Import Pendampingan')"
                  >
                    <i class="bi bi-filetype-csv"></i> Import Pendampingan
                  </a>

                </div>

                <!-- Cards Section -->
                <div class="container-fluid">
                  <!-- Data Table -->
                  <div class="card modern-card mt-4">
                    <div class="card-body">
                      <div class="table-responsive">
                        <EasyDataTable
                          :headers="header_pendampingan"
                          :items="filteredAnak_pendampingan"
                          :search-value="filter_pendamping.nik"
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
            </div>
          </div>
        </div>
        <!-- Footer -->
        <CopyRight class="mt-auto" />
      </div>
    </div>
  </div>

  <!-- Modal Tambah kelahiran -->
  <div class="modal fade" id="modalTambah_kelahiran" tabindex="-1">
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
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title fw-bold text-primary">Tambah Data Anak</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form class="row g-4">
            <!-- Nama Anak -->
            <div class="col-md-12">
              <label class="form-label small fw-semibold text-secondary">Nama Anak</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama" />
            </div>

            <!-- No KIA -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">No. KIA</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_kelahiran.no_kia"
                @input="form_kelahiran.no_kia = form_kelahiran.no_kia.replace(/\D/g, '')"
                maxlength="16"
              />
            </div>

            <!-- No Akta -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">No. Akta</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_kelahiran.no_akta"
                @input="form_kelahiran.no_akta = form_kelahiran.no_akta.replace(/\D/g, '')"
                maxlength="16"
              />
            </div>

            <!-- NIK Ayah -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Ayah</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.nik_ayah">
                <option value="">NIK Ayah</option>
              </select>
            </div>

            <!-- Nama Ayah -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Ayah</label>
              <input type="text" class="form-control shadow-sm bg-light" v-model="form_kelahiran.nama_ayah" readonly />
            </div>

            <!-- NIK Ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Ibu</label>
              <input type="text" class="form-control shadow-sm bg-light" v-model="form_kelahiran.nik_ibu" readonly />
            </div>

            <!-- Nama Ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Ibu</label>
              <input type="text" class="form-control shadow-sm bg-light" v-model="form_kelahiran.nama_ibu" readonly />
            </div>

            <!-- Tempat Tanggal lahir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tempat Lahir</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_kelahiran.tmpt_lahir"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tanggal Lahir</label>
              <input
                type="date"
                class="form-control shadow-sm"
                v-model="form_kelahiran.tgl_lahir"
              />
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.gender">
                <option value="">L/P</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <!-- Jenis Kelahiran -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelahiran</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.jenis">
                <option value="">Pilih jenis</option>
                <option value="Normal">Normal</option>
                <option value="Sesar">Sesar</option>
              </select>
            </div>

            <!-- Penolong Kelahiran -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Penolong Kelahiran</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.penolong">
                <option value="">Pilih Penolong</option>
                <option value="Dokter">Dokter</option>
                <option value="Bidan">Bidan</option>
                <option value="Dukun">Dukun</option>
                <option value="Mandiri">Mandiri</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <!-- Tempat Dilahirkan -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tempat Dilahirkan</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.tmpt_dilahirkan">
                <option value="">Pilih Tempat</option>
                <option value="RS">RS</option>
                <option value="Puskesmas">Puskesmas</option>
                <option value="Rumah">Rumah</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>

            <!-- Anak ke -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Anak Ke</label>
              <input type="number" min="1" class="form-control shadow-sm" v-model="form_kelahiran.anak_ke" />
            </div>

            <!-- usia ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Usia Ibu</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.anak_ke" />
            </div>

            <!-- Jarak Kelahiran -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jarak Kelahiran (Bulan)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.jarak" />
            </div>

            <!-- BB dan PB Anak-->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Berat Badan (kg)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.bb" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Panjang Badan (cm)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.pb" />
            </div>

            <!-- Kunjungan Terakhir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Kunjungan Terakhir</label>
              <input type="date" class="form-control shadow-sm" v-model="form_kunjungan.kunjungan" />
            </div>
          </form>
        </div>

        <!-- Footer -->
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

  <!-- Modal Import kelahiran -->
  <div class="modal fade" id="modalImport_kelahiran" ref="modalImport_kelahiran" tabindex="-1">
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
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">{{ importTitle }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Fungsi import hanya untuk data kunjungan posyandu</li>
              <li>Pastikan data yang diimport, berformat CSV</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
            </ul>
          </div>
          <form @submit.prevent="handleImport">
            <input type="file" class="form-control" ref="csvFile" accept=".csv" />
          </form>
        </div>

        <!-- Footer -->
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

  <!-- Modal Tambah kunjungan -->
  <div class="modal fade" id="modalTambah_kunjungan" tabindex="-1">
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
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title fw-bold text-primary">Tambah Kunjungan Posyandu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form class="row g-4">
            <!-- No KIA -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">No. KIA</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_kelahiran.no_kia"
                @input="form_kelahiran.no_kia = form_kelahiran.no_kia.replace(/\D/g, '')"
                maxlength="16"
              />
            </div>

            <!-- Nama Anak -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Anak</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama" />
            </div>

            <!-- NIK Ayah -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Ayah</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.nik_ayah">
                <option value="">NIK Ayah</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <!-- Nama Ayah -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Ayah</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama_ayah" readonly />
            </div>

            <!-- NIK Ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Ibu</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nik_ibu" readonly />
            </div>

            <!-- Nama Ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Ibu</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama_ibu" readonly />
            </div>

            <!-- Tempat Tanggal lahir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tempat Lahir</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_kelahiran.tmpt_lahir"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tanggal Lahir</label>
              <input
                type="date"
                class="form-control shadow-sm"
                v-model="form_kelahiran.tgl_lahir"
              />
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.gender">
                <option value="">L/P</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <!-- Jenis Kelahiran, anak ke, usia ibu, jarak -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelahiran</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.jenis">
                <option value="">Pilih jenis</option>
                <option value="Normal">Normal</option>
                <option value="Sesar">Sesar</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Anak Ke</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.anak_ke" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Usia Ibu</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.anak_ke" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jarak Kelahiran</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.jarak" />
            </div>

            <!-- BB dan PB Anak-->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Berat Badan (kg)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.bb" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Panjang Badan (cm)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.pb" />
            </div>

            <!-- Kunjungan Terakhir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Kunjungan Terakhir</label>
              <input type="date" class="form-control shadow-sm" v-model="form_kunjungan.kunjungan" />
            </div>
          </form>
        </div>

        <!-- Footer -->
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

  <!-- Modal Import kunjungan -->
  <div class="modal fade" id="modalImport_kunjungan" ref="modalImport_kelahiran" tabindex="-1">
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
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">{{ importTitle }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Fungsi import hanya untuk data kunjungan posyandu</li>
              <li>Pastikan data yang diimport, berformat CSV</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
            </ul>
          </div>
          <form @submit.prevent="handleImport">
            <input type="file" class="form-control" ref="csvFile" accept=".csv" />
          </form>
        </div>

        <!-- Footer -->
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

  <!-- Modal Tambah intervensi -->
  <div class="modal fade" id="modalTambah_intervensi" tabindex="-1">
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
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title fw-bold text-primary">Tambah Data Intervensi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <form class="row g-4">
            <!-- NIK Wali -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Wali</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_intervensi.nik_wali"
                @input="form_intervensi.nik_wali = form_intervensi.nik_wali.replace(/\D/g, '')"
                maxlength="16"
              />
            </div>

            <!-- Nama Anak -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Anak</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama" />
            </div>

            <!-- NIK Ayah -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Ayah</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.nik_ayah">
                <option value="">NIK Ayah</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <!-- Nama Ayah -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Ayah</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama_ayah" readonly />
            </div>

            <!-- NIK Ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">NIK Ibu</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nik_ibu" readonly />
            </div>

            <!-- Nama Ibu -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Nama Ibu</label>
              <input type="text" class="form-control shadow-sm" v-model="form_kelahiran.nama_ibu" readonly />
            </div>

            <!-- Tempat Tanggal lahir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tempat Lahir</label>
              <input
                type="text"
                class="form-control shadow-sm"
                v-model="form_kelahiran.tmpt_lahir"
              />
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Tanggal Lahir</label>
              <input
                type="date"
                class="form-control shadow-sm"
                v-model="form_kelahiran.tgl_lahir"
              />
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelamin</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.gender">
                <option value="">L/P</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <!-- Jenis Kelahiran, anak ke, usia ibu, jarak -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jenis Kelahiran</label>
              <select class="form-select shadow-sm" v-model="form_kelahiran.jenis">
                <option value="">Pilih jenis</option>
                <option value="Normal">Normal</option>
                <option value="Sesar">Sesar</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Anak Ke</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.anak_ke" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Usia Ibu</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.anak_ke" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Jarak Kelahiran</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.jarak" />
            </div>

            <!-- BB dan PB Anak-->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Berat Badan (kg)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.bb" />
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Panjang Badan (cm)</label>
              <input type="number" min="0" class="form-control shadow-sm" v-model="form_kelahiran.pb" />
            </div>

            <!-- Kunjungan Terakhir -->
            <div class="col-md-6">
              <label class="form-label small fw-semibold text-secondary">Kunjungan Terakhir</label>
              <input type="date" class="form-control shadow-sm" v-model="form_kunjungan.kunjungan" />
            </div>
          </form>
        </div>

        <!-- Footer -->
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

  <!-- Modal Import intervensi -->
  <div class="modal fade" id="modalImport_intervensi" ref="modalImport_kelahiran" tabindex="-1">
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
        <!-- Header -->
        <div class="modal-header text-primary bg-light border-0 rounded-top-4">
          <h5 class="modal-title">{{ importTitle }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body -->
        <div class="modal-body">
          <div class="alert alert-warning p-2">
            <ul>
              <li>Fungsi import hanya untuk data kunjungan posyandu</li>
              <li>Pastikan data yang diimport, berformat CSV</li>
              <li>Pastikan data sudah lengkap sebelum di import</li>
            </ul>
          </div>
          <form @submit.prevent="handleImport">
            <input type="file" class="form-control" ref="csvFile" accept=".csv" />
          </form>
        </div>

        <!-- Footer -->
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

<style scoped>
#chartBB,
#chartTB,
#chartBBTB {
  width: 100% !important;
  max-width: 300px;
  margin: auto;
}

.nutrition-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  min-height: 100vh;
}
/* Gradient Banner */
.nutrition-banner {
  background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary));
  border-radius: 0 0 1rem 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
/* Timeline Style */
.timeline li {
  position: relative;
  padding-left: 20px;
  margin-bottom: 10px;
  font-size: 0.9rem;
}
.timeline .dot {
  position: absolute;
  left: 0;
  top: 4px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
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
.table-modern {
  border: none !important;
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
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

.progress-bar {
  transition: width 0.4s ease-in-out;
}
.progress-bar[data-progress='low'] {
  background-color: var(--bs-primary); /* biru awal */
}
.progress-bar[data-progress='mid'] {
  background-color: #ffc107; /* kuning tengah */
}
.progress-bar[data-progress='high'] {
  background-color: #198754; /* hijau akhir */
}
</style>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import EasyDataTable from 'vue3-easy-data-table'
import 'vue3-easy-data-table/dist/style.css'
import { Modal } from 'bootstrap' // <-- butuh ini untuk kontrol modal

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Children',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, EasyDataTable },
  data() {
    return {
      isCollapsed: false,
      isFilterOpen: false,
      importTitle: 'Import File', // <-- judul modal dinamis
      showAlert: false,
      showSuccessModal: false, // kontrol popup sukses
      isLoadingImport: false,
      importProgress: 0,
      animatedProgress: 0,
      currentRow: 0,
      totalRows: 0,

      // form kunjungan posyandu
      form_kunjungan: {
        nik_wali: '',
        nama_wali: '',
        hubungan: '',
        nama: '',
        gender: '',
        bb:'',
        tb:'',
        lika:'',
        alamat: '',
        tgl_lahir: '',
        usia: 0,
        status_bb:'',
        status_tb:'',
        status_gizi:'',
        posyandu: '',
        rt: '',
        rw: '',
        kunjungan: '',
      },

      // form kelahiran
      form_kelahiran: {
        nik_ayah:'',
        nama_ayah:'',
        nik_ibu:'',
        nama_ibu:'',
        no_kia:'',
        no_akta:'',
        nama:'',
        gender:'',
        tmpt_lahir:'',
        tgl_lahir:'',
        jenis:'',
        penolong:'',
        bb:'',
        pb:'',
        tmpt_dilahirkan:'',
        anak_ke:'',
        jarak:'',
      },

      //form intervensi
      form_intervensi:{
        nama: '',
        nik_wali: '',
        nama_wali: '',
        hubungan: '',
        sasaran:'anak',
        jenis:'',
        hasil:'',
        rencana_tindakan:'',
        petugas:'',
      },

      //form intervensi
      form_pendampingan:{
        jenis:'anak',
        nik_wali:'',
        nama:'',
        petugas:'',
        tgl_pendampingan:'',
        lokasi:'',
        catatan:'',
        bb:'',
        tb:'',
        lika:'',
        lila:'',
        tgl_lahir: '',
        usia: 0,
        status_bb:'',
        status_tb:'',
        status_gizi:'',
        asi:'',
        imunisasi_dasar:'',
        diasuh_oleh:'',
        vit_a:'',
      },

      // data intervensi
      intervensi: [
        {
          nama: 'Zainudin',
          nik_ayah: '1789026384957681',
          nama_ayah: 'Zainal Arifin',
          nik_ibu: '3124026384957681',
          nama_ibu: 'Dinda Fitari',
          sasaran:'anak',
          jenis:'imunisasi',
          hasil:'anak sudah terimunisasi campak',
          rencana_tindakan:'Imunisasi BCG',
          petugas:'Nirwana',
        },
        {
          nama: 'Didin',
          nik_ayah: '1543226384957681',
          nama_ayah: 'Ayah',
          nik_ibu: '3124020987657681',
          nama_ibu: 'Bunda',
          sasaran:'anak',
          jenis:'imunisasi',
          hasil:'anak sudah terimunisasi campak',
          rencana_tindakan:'Imunisasi BCG',
          petugas:'Nirwana',
        },
      ],

      // data dummy kunjungan
      kunjungan_posyandu: [
        {
          kia: '8196839208745623',
          nama: 'Ahmad Fauzi',
          nik_ibu: '3276012309870001',
          nama_ibu: 'Siti Aminah',
          nik_ayah: '1871012309870001',
          nama_ayah: 'Steven Ann',
          tgl_lahir: '2022-03-14',
          gender: 'L',
          phone: '0819978654432',
          alamat: 'Jl. Damai 3 No. 36,',
          provinsi: 'DKI Jakarta',
          kota: 'Jakarta Timur',
          kecamatan: 'Cipayung',
          desa: 'Cilangkap',
          rw: '02',
          rt: '05',
          usia: '41',
          tb:'55',
          bb:'10',
          lika:'12',
          kunjungan: '2025-08-10',
        },
        {
          kia: '543739208749806',
          nama: 'Syifa Ann',
          nik_ibu: '3276012309887601',
          nama_ibu: 'Hamidah Dwi I',
          nik_ayah: '3123012309870087',
          nama_ayah: 'Hamid D',
          tgl_lahir: '2022-03-15',
          gender: 'P',
          phone: '0821234652245',
          alamat: 'Jl. Damai 3 No. 6,',
          provinsi: 'DKI Jakarta',
          kota: 'Jakarta Timur',
          kecamatan: 'Cipayung',
          desa: 'Cilangkap',
          rw: '02',
          rt: '05',
          usia: '41',
          tb:'55',
          bb:'10',
          lika:'12',
          kunjungan: '2025-08-10',
        },
        {
          kia: '-',
          nama: 'Azzaky El Nino',
          nik_ibu: '327609876870022',
          nama_ibu: 'Ien Syani',
          nik_ayah: '1871009879870001',
          nama_ayah: 'Donny Aja',
          tgl_lahir: '2022-03-15',
          gender: 'L',
          phone: '0819909855632',
          alamat: 'Jl. Damai 3 No. 44,',
          provinsi: 'DKI Jakarta',
          kota: 'Jakarta Timur',
          kecamatan: 'Cipayung',
          desa: 'Cilangkap',
          rw: '02',
          rt: '05',
          usia: '41',
          tb:'55',
          bb:'10',
          lika:'12',
          kunjungan: '2025-08-10',
        },
      ],

      kelahiran_anak: [
        {
          no_kia: '1893456723456123',
          nama:'John Foe',
          nik_ayah:'1870965231876523',
          nama_ayah:'David John',
          nik_ibu:'3127093421874560',
          nama_ibu:'Foe Watson',
          gender:'L',
          tmpt_dilahirkan:'Jakarta',
          tgl_lahir:'2024-03-22',
          bb:'3',
          pb:'50',
          anak_ke:'1',
          usia_ibu:'23',
          jarak:'-',
          jenis:'normal',
        },
        {
          no_kia: '312783457302948',
          nama:'Danny Ang',
          nik_ayah:'1870965231871234',
          nama_ayah:'Danny Wong',
          nik_ibu:'3127093421809764',
          nama_ibu:'Angelina Ang',
          gender:'L',
          tmpt_dilahirkan:'Jakarta',
          tgl_lahir:'2024-09-22',
          jenis:'normal',
          bb:'4',
          pb:'57',
          anak_ke:'1',
          usia_ibu:'33',
          jarak:'-',
        },
        {
          no_kia: '1871022703960004',
          nama:'Gin Azza',
          nik_ayah:'1870966543971232',
          nama_ayah:'Gin Tama',
          nik_ibu:'3127654381806564',
          nama_ibu:'Azza Iyyah',
          gender:'P',
          tmpt_dilahirkan:'Jakarta',
          tgl_lahir:'2024-10-09',
          jenis:'sesar',
          bb:'3',
          pb:'47',
          anak_ke:'1',
          usia_ibu:'27',
          jarak:'-',
        },
      ],

      pendampingan:[
        {
          nik_wali:'1871031205950003',
          nama:'Agustian Pratama',
          petugas:'Dio',
          tgl_pendampingan:'2025-09-12',
          lokasi:'Rumah',
          catatan:'Catatan tambahan',
          bb:'13',
          tb:'94',
          lika:'30',
          lila:'12',
          tgl_lahir: '2022-09-12',
          usia: 55,
          status_bb:'Normal',
          status_tb:'Normal',
          status_gizi:'Gizi Baik',
          asi:true,
          imunisasi_dasar:true,
          diasuh_oleh:'Orang Tua',
          vit_a:true,
        },
      ],

      // header kunjungan table
      headers_kunjungan: [
        { text: 'KIA', value: 'kia' },
        { text: 'Nama', value: 'nama' },
        { text: 'NIK Ayah', value: 'nik_ayah' },
        { text: 'Nama Ayah', value: 'nama_ayah' },
        { text: 'NIK Ibu', value: 'nik_ibu' },
        { text: 'Nama Ibu', value: 'nama_ibu' },
        { text: 'L/P', value: 'gender' },
        { text: 'Tanggal Lahir', value: 'tgl_lahir' },
        { text: 'Usia (Bulan)', value: 'usia' },
        { text: 'Alamat', value: 'alamat' },
        { text: 'RT', value: 'rt' },
        { text: 'RW', value: 'rw' },
        { text: 'Kunjungan Terakhir', value: 'kunjungan' },
      ],

      // header intervensi
      headers_intervensi: [
        { text: 'nama', value: 'nama' },
        { text: 'NIK Ayah', value: 'nik_ayah' },
        { text: 'Nama Ayah', value: 'nama_ayah' },
        { text: 'NIK Ibu', value: 'nik_ibu' },
        { text: 'Nama Ibu', value: 'nama_ibu' },
        { text: 'Sasaran', value: 'sasaran' },
        { text: 'Jenis', value: 'jenis' },
        { text: 'Hasil', value: 'hasil' },
        { text: 'Rencana Tindakan', value: 'rencana tindakan' },
        { text: 'Petugas', value: 'petugas' },
        { text: 'Buat Jadwal', value: 'action' },
      ],

      //header kelahiran
      headers_anak: [
        { text: 'No KIA', value: 'no_kia' },
        { text: 'Nama', value: 'nama' },
        { text: 'NIK Ayah', value: 'nik_ayah' },
        { text: 'Nama Ayah', value: 'nama_ayah' },
        { text: 'NIK Ibu', value: 'nik_ibu' },
        { text: 'Nama Ibu', value: 'nama_ibu' },
        { text: 'L/P', value: 'gender' },
        { text: 'Tempat dilahirkan', value: 'tmpt_dilahirkan' },
        { text: 'Tanggal Lahir', value: 'tgl_lahir' },
        { text: 'Jenis Kelahiran', value: 'jenis' },
        { text: 'BB', value: 'bb' },
        { text: 'PB', value: 'pb' },
        { text: 'Anak ke', value: 'anak_ke' },
        { text: 'Usia Ibu', value: 'usia_ibu' },
        { text: 'Jarak Kelahiran', value: 'jarak' },
      ],

      //header pendampingan
      header_pendampingan:[
        { text: 'NIK Orang Tua', value: 'nik_wali' },
        { text: 'Nama', value: 'nama' },
        { text: 'Tanggal Pendampingan', value: 'tgl_pendampingan' },
        { text: 'Lokasi Pendampingan', value: 'lokasi' },
        { text: 'BB', value: 'bb' },
        { text: 'TB', value: 'tb' },
        { text: 'Usia', value: 'usia' },
        { text: 'Asi Esklusif', value: 'asi' },
        { text: 'Imunisasi Dasar', value: 'imunisasi_dasar' },
        { text: 'Diasuh oleh', value: 'diasuh_oleh' },
        { text: 'Pemberian Vitamin A', value: 'vit_a' },
        { text: 'Petugas TPK', value: 'petugas' },
      ],

      // filter kelahiran
      filter_anak: {
        nik: '',
      },
      advancedFilter_anak: {
        no_kia: '',
        tgl_lahir: '',
        gender: '',
        nama: '',
      },
      appliedAdvancedFilter_anak: {
        no_kia: '',
        tgl_lahir: '',
        gender: '',
        nama: '',
      },

      // filter kunjungan
      filter_kunjungan: {
        nik: '',
      },
      advancedFilter_kunjungan: {
        nama:'',
        gender:'',
        usia: '',
        kunjungan: '',
      },
      appliedAdvancedFilter_kunjungan: {
        nama:'',
        gender:'',
        usia: '',
        kunjungan: '',
      },

      // filter intervensi
      filter_intervensi: {
        nik: '',
      },
      advancedFilter_intervensi: {
        nama: '',
        jenis: '',
        tgl_kunjungan: '',
      },
      appliedAdvancedFilter_intervensi: {
        nama: '',
        jenis: '',
        tgl_kunjungan: '',
      },

      // filter pendampingan
      filter_pendamping: {
        nik: '',
      },
      advancedFilter_pendamping: {
        nama: '',
        usia:'',
        tgl_pendampingan: '',
      },
      appliedAdvancedFilter_pendamping: {
        nama: '',
        usia:'',
        tgl_pendampingan: '',
      },
    }
  },
  computed: {
    background() {
      const config = JSON.parse(localStorage.getItem('siteConfig'))
      return config && config.background ? config.background : null
    },
    filteredAnak_kunjungan() {
      return this.kunjungan_posyandu.filter((item) => {
        const af = this.appliedAdvancedFilter_kunjungan
        const nikFilter = this.filter_kunjungan.nik

        return (
          // filter NIK gabungan (ayah OR ibu)
          (nikFilter === '' ||
            item.nik_ayah.includes(nikFilter) ||
            item.nik_ibu.includes(nikFilter)) &&
          // filter lanjutan
          (af.nama === '' || item.nama === af.nama) &&
          (af.usia === '' || item.usia === af.usia) &&
          (af.gender === '' || item.gender === af.gender) &&
          (af.kunjungan === '' || item.kunjungan === af.kunjungan)
        )
      })
    },
    filteredAnak_anak() {
      return this.kelahiran_anak.filter((item) => {
        const af = this.appliedAdvancedFilter_anak
        const nikFilter = this.filter_anak.nik

        return (
          // filter NIK gabungan (ayah OR ibu)
          (nikFilter === '' ||
            item.nik_ayah.includes(nikFilter) ||
            item.nik_ibu.includes(nikFilter)) &&
          // filter lanjutan
          (af.nama === '' || item.nama === af.nama) &&
          (af.tgl_lahir === '' || item.tgl_lahir === af.tgl_lahir) &&
          (af.gender === '' || item.gender === af.gender) &&
          (af.no_kia === '' || item.no_kia === af.no_kia)
        )
      })
    },
    filteredAnak_intervensi() {
      return this.intervensi.filter((item) => {
        const af = this.appliedAdvancedFilter_intervensi
        const nikFilter = this.filter_intervensi.nik

        return (
          // filter NIK gabungan (ayah OR ibu)
          (nikFilter === '' ||
            item.nik_ayah.includes(nikFilter) ||
            item.nik_ibu.includes(nikFilter)) &&
          // filter lanjutan
          (af.nama === '' || item.nama === af.nama) &&
          (af.tgl_kunjungan === '' || item.tgl_kunjungan === af.tgl_kunjungan)
        )
      })
    },
    filteredAnak_pendampingan() {
      return this.pendampingan.filter((item) => {
        const af = this.appliedAdvancedFilter_pendamping
        const nikFilter = this.filter_pendamping.nik

        return (
          // filter NIK gabungan (ayah OR ibu)
          (nikFilter === '' ||
            item.nik_wali.includes(nikFilter)) &&
          // filter lanjutan
          (af.nama === '' || item.nama === af.nama) &&
          (af.usia === '' || item.usia === af.usia) &&
          (af.tgl_pendampingan === '' || item.tgl_pendampingan === af.tgl_pendampingan)
        )
      })
    },
    progressLevel() {
      if (this.importProgress < 40) return 'low'
      if (this.importProgress < 80) return 'mid'
      return 'high'
    },
  },
  methods: {
    // helper close modal
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

          this.kunjungan_posyandu.push({ ...this.form_kunjungan })
          this.showAlert = true
          setTimeout(() => (this.showAlert = false), 3000)

          // reset form
          this.form_kunjungan = {
            nik: '',
            nama: '',
            gender: 'L',
            alamat: '',
            tgl_lahir: '',
            usia: 0,
            status_bb: '',
            status_tb: '',
            status_bb_tb: '',
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
      // refresh chart kalau ada
      if (typeof this.refreshCharts === 'function') {
        this.refreshCharts()
      }
    },
    openImport(title) {
      this.importTitle = title
      const el = this.$refs.modalImport
      const instance = Modal.getOrCreateInstance(el)
      instance.show()
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    toggleExpand() {
      this.isFilterOpen = !this.isFilterOpen
    },
    hitungUsia() {
      if (!this.form_kunjungan.tgl_lahir) {
        this.form_kunjungan.usia = 0
        return
      }
      const lahir = new Date(this.form_kunjungan.tgl_lahir)
      const today = new Date()

      let usia = (today.getFullYear() - lahir.getFullYear()) * 12
      usia += today.getMonth() - lahir.getMonth()

      // kalau tanggal hari ini < tanggal lahir, kurangi 1 bulan
      if (today.getDate() < lahir.getDate()) {
        usia -= 1
      }
      this.form_kunjungan.usia = usia >= 0 ? usia : 0
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

          this.kunjungan_posyandu.push({
            nik: obj.nik || '',
            nama: obj.nama || '',
            gender: obj.gender || '',
            alamat: obj.alamat || '',
            tgl_lahir: obj.tgl_lahir || '',
            usia: obj.usia || '',
            status_bb: obj.status_bb || '',
            status_tb: obj.status_tb || '',
            status_bb_tb: obj.status_bb_tb || '',
            rt: obj.rt || '',
            rw: obj.rw || '',
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
    applyAdvancedFilter_kunjungan() {
      this.appliedAdvancedFilter_kunjungan = { ...this.advancedFilter_kunjungan }
    },
    applyAdvancedFilter_anak() {
      // Salin isi input advancedFilter ke appliedAdvancedFilter
      this.appliedAdvancedFilter_anak = { ...this.advancedFilter_anak }
    },
    applyAdvancedFilter_intervensi() {
      // Salin isi input advancedFilter ke appliedAdvancedFilter
      this.appliedAdvancedFilter_intervensi = { ...this.advancedFilter_intervensi }
    },
    applyAdvancedFilter_pendampingan() {
      // Salin isi input advancedFilter ke appliedAdvancedFilter
      this.appliedAdvancedFilter_pendamping = { ...this.advancedFilter_pendamping }
    },
    resetFilter_kunjungan() {
    this.advancedFilter_kunjungan = {
      nama: '',
      gender: '',
      usia: '',
      kunjungan: '',
    }
    this.appliedAdvancedFilter_kunjungan = { ...this.advancedFilter_kunjungan }
  },
    resetFilter_anak() {
      this.advancedFilter_anak = {
        nama: '',
        no_kia: '',
        gender: '',
        tgl_lahir: '',
        kunjungan: '',
      }
      this.appliedAdvancedFilter_anak = { ...this.advancedFilter_anak }
    },
    resetFilter_intervensi() {
      this.advancedFilter_intervensi = {
        nama: '',
        tgl_kunjungan: '',
      }
      this.appliedAdvancedFilter_intervensi = { ...this.advancedFilter_intervensi }
    },
    resetFilter_pendampingan() {
      this.advancedFilter_pendamping = {
        nama: '',
        usia:'',
        tgl_pendampingan: '',
      }
      this.appliedAdvancedFilter_pendamping = { ...this.advancedFilter_pendamping }
    },
  },
  watch: {
    anak: {
      handler() {
        this.refreshCharts()
      },
      deep: true,
    },
  },
}
</script>
