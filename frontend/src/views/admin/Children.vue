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
                <div class="text-white my-0">
                  <ul>
                    <li>Anda memiiki <strong>1</strong> jadwal intervensi hari ini.</li>
                    <li v-if="pendingCount > 0">
                      Anda memiliki
                      <a
                        href="javascript:void(0)"
                        class="fw-bold text-white text-decoration-none"
                        @click="toggleExpandPending"
                      >
                        {{ pendingCount }} data pending
                      </a>
                      belum terkirim.
                    </li>
                  </ul>
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
                    class="nav-link"
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
                    class="nav-link active"
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
                class="tab-pane fade"
                id="kelahiran-tab-pane"
                role="tabpanel"
                tabindex="0"
              >

                <!-- Button Group -->
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
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport_kelahiran">
                      <i class="bi bi-filetype-csv"></i> Import Data Keluarga
                    </button>
                  </div>

                  <!-- Collapsible Form -->
                  <div v-if="isFormOpen" id="formData" class="card shadow-sm">
                    <div class="card-body">
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

                <!-- Filter -->
                <h5 class="fw-bold text-success">Data Anak</h5>
                <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
                  <form class="row g-3" @submit.prevent="applyFilter_anak">
                    <!-- Baris utama filter -->
                    <div class="row mt-2">
                      <!-- Status Gizi -->
                      <div class="col-md-4">
                        <label class="form-label fw-semibold">Status Gizi</label>
                        <select class="form-select mb-2" v-model="advancedFilter_anak.status_gizi">
                          <option value="">-- Pilih Status Gizi --</option>
                          <option value="stunting">Stunting</option>
                          <option value="wasting">Wasting</option>
                          <option value="bb_stagnan">BB Stagnan</option>
                          <option value="normal">Normal</option>
                          <option value="underweight">Underweight</option>
                          <option value="overweight">Overweight</option>
                        </select>

                        <div class="row">
                          <div
                            class="col-md-4"
                            v-for="item in ['Stunting','Wasting','BB Stagnan','Normal','Underweight','Overweight']"
                            :key="item"
                          >
                            <div class="form-check mb-3">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                :id="item"
                                :value="item"
                                v-model="advancedFilter_anak.status_list"
                              />
                              <label class="form-check-label" :for="item">{{ item }}</label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Lokasi -->
                      <div class="col-md-4">
                        <label class="form-label fw-semibold">Lokasi</label>
                        <select class="form-select mb-2" v-model="advancedFilter_anak.lokasi">
                          <option value="">-- Pilih Lokasi --</option>
                          <option value="desa">Desa</option>
                          <option value="rw">RW</option>
                          <option value="rt">RT</option>
                          <option value="posyandu">Posyandu</option>
                        </select>

                        <div class="row">
                          <div
                            class="col-md-4"
                            v-for="item in ['Desa','Posyandu','RT','RW']"
                            :key="item"
                          >
                            <div class="form-check mb-3">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                :id="'lokasi_'+item"
                                :value="item"
                                v-model="advancedFilter_anak.lokasi_list"
                              />
                              <label class="form-check-label" :for="'lokasi_'+item">{{ item }}</label>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Intervensi -->
                      <div class="col-md-4">
                        <label class="form-label fw-semibold">Intervensi</label>
                        <select class="form-select mb-2" v-model="advancedFilter_anak.intervensi">
                          <option value="">-- Pilih Intervensi --</option>
                          <option value="belum_pmt">Belum PMT</option>
                          <option value="belum_dikunjungi">Belum dikunjungi</option>
                          <option value="lengkap">Lengkap</option>
                        </select>

                        <div class="row">
                          <div
                            class="col-md-4"
                            v-for="item in ['Belum PMT','Belum dikunjungi','Lengkap']"
                            :key="item"
                          >
                            <div class="form-check mb-3">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                :id="'inter_'+item"
                                :value="item"
                                v-model="advancedFilter_anak.intervensi_list"
                              />
                              <label class="form-check-label" :for="'inter_'+item">{{ item }}</label>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Catatan -->
                    <div class="col-12 text-end mt-2">
                      <small class="text-muted fst-italic">*Bisa pilih lebih dari 1</small>
                    </div>

                    <!-- Tombol -->
                    <div class="col-md-12 mt-3">
                      <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Terapkan Filter
                      </button>
                      <button type="button" class="btn btn-secondary float-end" @click="resetFilter_anak">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                      </button>
                    </div>
                  </form>
                </div>

                <!-- Table Section -->
                <div class="container-fluid">
                  <div v-if="isPendingOpen" id="dataPending" class="card modern-card mt-4">
                    <div class="card-body bg-additional rounded">
                      <div class="d-flex justify-content-between">
                        <h5 class="fw-bold mb-2 text-white">Data Pending</h5>
                        <button @click="toggleExpandPending" class="btn-close"></button>
                      </div>

                      <EasyDataTable
                        :headers="headers_pending"
                        :items="kelahiranPending"
                      >
                        <template #item-action="{ id,tipe }">
                          <button
                            class="btn btn-secondary m-2"
                            @click="updateKelahiran(id,tipe)"
                            style="font-size: small;"
                          >
                            <i class="fa fa-pen"></i>
                          </button>
                        </template>
                      </EasyDataTable>
                    </div>
                  </div>
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
              <div class="tab-pane fade show active" id="kunjungan-tab-pane" role="tabpanel" tabindex="0">
                <!-- Form -->
                <div class="container-fluid py-3">
                  <!-- Header -->
                  <h5 class="fw-bold mb-3">Tambahkan Data</h5>

                  <!-- Input Pencarian -->
                  <div class="d-flex align-items-center gap-2 mb-3">
                    <input
                      type="text"
                      class="form-control w-50"
                      placeholder="Masukkan NIK"
                      v-model="searchNIK"
                    />
                    <button class="btn btn-gradient px-5" @click="cariData"><i class="fa fa-search"></i> Cari</button>
                    <button
                      class="btn btn-outline-primary ms-auto"
                      data-bs-toggle="modal"
                      data-bs-target="#modalImport_kunjungan"
                    >
                      <i class="bi bi-filetype-csv"></i> Unggah Data
                    </button>
                  </div>

                  <!-- Alert jika data ditemukan / tidak -->
                  <div v-if="found" class="text-danger fw-semibold mb-3">Data Ditemukan</div>
                  <div v-else-if="notFound" class="text-danger small mb-3">
                    Data dengan NIK tersebut tidak ditemukan.
                  </div>

                  <!-- FORM: tampil hanya jika data ditemukan -->
                  <transition name="fade">
                    <form v-if="showForm" class="row g-4 border-top pt-3">
                      <!-- No KIA -->
                      <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">No. KIA</label>
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form_kunjungan.kia"
                          readonly
                        />
                      </div>

                      <!-- Nama Anak -->
                      <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nama Anak</label>
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form_kunjungan.nama"
                          readonly
                        />
                      </div>

                      <!-- Nama Ayah -->
                      <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nama Ayah</label>
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form_kunjungan.nama_ayah"
                          readonly
                        />
                      </div>

                      <!-- Nama Ibu -->
                      <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nama Ibu</label>
                        <input
                          type="text"
                          class="form-control shadow-sm"
                          v-model="form_kunjungan.nama_ibu"
                          readonly
                        />
                      </div>

                      <!-- RW & RT -->
                      <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">RW</label>
                        <input type="text" class="form-control shadow-sm" v-model="form_kunjungan.rw" readonly />
                      </div>
                      <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">RT</label>
                        <input type="text" class="form-control shadow-sm" v-model="form_kunjungan.rt" readonly />
                      </div>

                      <!-- Tinggi Badan -->
                      <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Tinggi Badan (cm)</label>
                        <input type="number" class="form-control shadow-sm" v-model="form_kunjungan.tb" />
                      </div>

                      <!-- Berat Badan -->
                      <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Berat Badan (kg)</label>
                        <input type="number" class="form-control shadow-sm" v-model="form_kunjungan.bb" />
                      </div>

                      <!-- Lingkar Kepala -->
                      <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Lingkar Kepala (cm)</label>
                        <input type="number" class="form-control shadow-sm" v-model="form_kunjungan.lika" />
                      </div>

                      <!-- Tanggal Kunjungan -->
                      <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Kunjungan</label>
                        <input type="date" class="form-control shadow-sm" v-model="form_kunjungan.kunjungan" />
                      </div>

                      <!-- Tombol Submit -->
                      <div class="col-12 text-end">
                        <small class="text-muted me-3">Pastikan data telah sesuai sebelum simpan</small>
                        <button type="button" class="btn btn-success" @click="kirimData">Kirim</button>
                      </div>
                    </form>
                  </transition>
                </div>

                <!-- Data Pending -->
                <div class="container-fluid mt-4">
                  <div class="table-responsive">
                    <EasyDataTable
                      :headers="pending_kunjungan"
                      :items="itemsPending"
                      table-class="my-striped align-middle text-center"
                      header-text-direction="center"
                      body-text-direction="center"
                      alternating
                    >
                      <!-- Slot Nama -->
                      <template #item-nama="{ nama }">
                        <span class="fw-semibold">{{ nama }}</span>
                      </template>

                      <!-- Slot Jenis Kelamin -->
                      <template #item-gender="{ gender }">
                        <span>{{ gender }}</span>
                      </template>

                      <!-- Slot BB -->
                      <template #item-bb="{ bb }">
                        <span>{{ bb ? bb + ' kg' : '-' }}</span>
                      </template>

                      <!-- Slot TB -->
                      <template #item-tb="{ tb }">
                        <span>{{ tb ? tb + ' cm' : '-' }}</span>
                      </template>

                      <!-- Slot Edit -->
                      <template #item-edit="{ row }">
                        <a href="#" class="text-primary text-decoration-none fw-semibold" @click.prevent="editRow(row)">
                          Edit
                        </a>
                      </template>
                    </EasyDataTable>
                  </div>
                </div>

                <!-- Filter -->
                <div class="container-fluid mt-4">
                  <h5 class="fw-bold text-success">Data Anak</h5>
                  <div class="filter-wrapper bg-light rounded shadow-sm p-3 mt-3 container-fluid">
                    <form class="row g-3" @submit.prevent="applyFilter_anak">
                      <!-- Baris utama filter -->
                      <div class="row mt-2">
                        <!-- Status Gizi -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Status Gizi</label>
                          <select class="form-select mb-2" v-model="advancedFilter_anak.status_gizi">
                            <option value="">-- Pilih Status Gizi --</option>
                            <option value="stunting">Stunting</option>
                            <option value="wasting">Wasting</option>
                            <option value="bb_stagnan">BB Stagnan</option>
                            <option value="normal">Normal</option>
                            <option value="underweight">Underweight</option>
                            <option value="overweight">Overweight</option>
                          </select>

                          <div class="row">
                            <div
                              class="col-md-6"
                              v-for="item in ['Stunting','Wasting','BB Stagnan','Normal','Underweight','Overweight']"
                              :key="item"
                            >
                              <div class="form-check mb-3">
                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  :id="item"
                                  :value="item"
                                  v-model="advancedFilter_anak.status_list"
                                />
                                <label class="form-check-label" :for="item">{{ item }}</label>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Lokasi -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Lokasi</label>
                          <select class="form-select mb-2" v-model="advancedFilter_anak.lokasi">
                            <option value="">-- Pilih Lokasi --</option>
                            <option value="desa">Desa</option>
                            <option value="rw">RW</option>
                            <option value="rt">RT</option>
                            <option value="posyandu">Posyandu</option>
                          </select>

                          <div class="row">
                            <div
                              class="col-md-6"
                              v-for="item in ['Desa','Posyandu','RT','RW']"
                              :key="item"
                            >
                              <div class="form-check mb-3">
                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  :id="'lokasi_'+item"
                                  :value="item"
                                  v-model="advancedFilter_anak.lokasi_list"
                                />
                                <label class="form-check-label" :for="'lokasi_'+item">{{ item }}</label>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Intervensi -->
                        <div class="col-md-4">
                          <label class="form-label fw-semibold">Intervensi</label>
                          <select class="form-select mb-2" v-model="advancedFilter_anak.intervensi">
                            <option value="">-- Pilih Intervensi --</option>
                            <option value="belum_pmt">Belum PMT</option>
                            <option value="belum_dikunjungi">Belum dikunjungi</option>
                            <option value="lengkap">Lengkap</option>
                          </select>

                          <div class="row">
                            <div
                              class="col-md-6"
                              v-for="item in ['Belum PMT','Belum dikunjungi','Lengkap']"
                              :key="item"
                            >
                              <div class="form-check mb-3">
                                <input
                                  class="form-check-input"
                                  type="checkbox"
                                  :id="'inter_'+item"
                                  :value="item"
                                  v-model="advancedFilter_anak.intervensi_list"
                                />
                                <label class="form-check-label" :for="'inter_'+item">{{ item }}</label>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Catatan -->
                      <div class="col-12 text-end mt-2">
                        <small class="text-muted fst-italic">*Bisa pilih lebih dari 1</small>
                      </div>
                    </form>
                  </div>
                </div>

                <!-- Data Kunjungan -->
                <div class="container-fluid mt-4">
                  <div class="row mt-4">
                    <div class="col-md-8">
                      <div class="table-responsive">
                        <EasyDataTable
                          :headers="headers_kunjungan"
                          :items="filteredKunjungan"
                          table-class="table table-striped align-middle text-center"
                          header-text-direction="center"
                          body-text-direction="center"
                        >
                          <!-- NIK -->
                          <template #item-nik="props">
                            <a
                              href="#"
                              @click.prevent="showDetail(props)"
                              class="fw-semibold text-decoration-none text-primary"
                            >
                              {{ props.nik }}
                            </a>
                          </template>


                          <!-- INTERVENSI -->
                          <template #item-intervensi="{ intervensi }">
                            <span>{{ intervensi || '-' }}</span>
                          </template>

                          <!-- STATUS GIZI -->
                          <template #item-status_gizi="{ status_gizi }">
                            <span
                              class="badge px-3 py-2 text-white"
                              :class="{
                                'bg-danger': status_gizi === 'Stunting',
                                'bg-warning text-dark': status_gizi === 'Wasting' || status_gizi === 'Underweight',
                                'bg-success': status_gizi === 'Normal'
                              }"
                            >
                              {{ status_gizi }}
                            </span>
                          </template>
                        </EasyDataTable>
                      </div>
                    </div>

                    <!-- DETAIL DATA -->
                    <div class="col-md-4">
                      <div v-if="selectedAnak" class="card shadow-sm p-4 text-center">
                        <!-- Nama dan Identitas -->
                        <h5 class="fw-bold text-dark mb-1">{{ selectedAnak.nama }}</h5>
                        <p class="text-muted mb-0">
                          {{ selectedAnak.gender === 'L' ? 'Laki-laki' : selectedAnak.gender === 'P' ? 'Perempuan' : selectedAnak.gender }}
                        </p>

                        <p class="text-muted mb-0">{{ selectedAnak.alamat || 'Desa Wonosari, Kec. Bojong Gede' }}</p>
                        <p class="text-muted">{{ selectedAnak.posyandu || 'Posyandu Mawar' }}</p>

                        <!-- Badge Status Gizi -->
                        <div class="mb-3">
                          <span
                            class="badge px-3 py-2 fs-6"
                            :class="{
                              'bg-danger': selectedAnak.status_gizi === 'Stunting',
                              'bg-warning text-dark': selectedAnak.status_gizi === 'Wasting',
                              'bg-success': selectedAnak.status_gizi === 'Normal'
                            }"
                          >
                            {{ selectedAnak.status_gizi }} {{ selectedAnak.status_gizi_kategori || 'BB/U' }}
                          </span>
                        </div>

                        <!-- Riwayat Penimbangan -->
                        <h6 class="fw-bold text-start text-secondary mt-2">Riwayat Penimbangan</h6>
                        <table class="table table-bordered table-sm align-middle text-center">
                          <thead class="table-light">
                            <tr>
                              <th>Tanggal</th>
                              <th>Status BB/U</th>
                              <th>Status TB/U</th>
                              <th>Status BB/TB</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(r, i) in selectedAnak.riwayat_penimbangan || []" :key="i">
                              <td>{{ r.tanggal }}</td>
                              <td>{{ r.bb_u }}</td>
                              <td>{{ r.tb_u }}</td>
                              <td>
                                <span
                                  class="badge"
                                  :class="{
                                    'bg-danger': r.bb_tb === 'Stunting',
                                    'bg-warning text-dark': r.bb_tb === 'Wasting',
                                    'bg-success': r.bb_tb === 'Normal'
                                  }"
                                >
                                  {{ r.bb_tb }}
                                </span>
                              </td>
                            </tr>
                          </tbody>
                        </table>

                        <!-- Riwayat Intervensi -->
                        <h6 class="fw-bold text-start text-secondary mt-3">Riwayat Intervensi / Bantuan</h6>
                        <table class="table table-bordered table-sm align-middle text-center">
                          <thead class="table-light">
                            <tr>
                              <th>Tanggal</th>
                              <th>Kader</th>
                              <th>Intervensi</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="(i, idx) in selectedAnak.riwayat_intervensi || []" :key="idx">
                              <td>{{ i.tanggal }}</td>
                              <td>{{ i.kader }}</td>
                              <td>{{ i.intervensi }}</td>
                            </tr>
                          </tbody>
                        </table>

                        <!-- Tombol Download -->
                        <button class="btn btn-primary rounded-pill px-4 mt-2 fw-semibold" @click="downloadRiwayat">
                          Download Riwayat
                        </button>
                      </div>

                      <!-- Pesan Default -->
                      <div v-else class="card shadow-sm p-4 text-center">
                        <p class="text-muted fst-italic">Klik NIK untuk melihat detail anak.</p>
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
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
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
/* di <style scoped> */
.my-striped tr:nth-child(even) {
  background-color: #f8f9fa !important;
}
.my-striped tr:nth-child(odd) {
  background-color: #ffffff !important;
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
      searchNIK: '',
      showForm: false,
      found: false,
      notFound: false,
      modalMode: "add",
      isFormOpen: false,
      isPendingOpen: false,
      isCollapsed: false,
      isFilterOpen: false,
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

      //kunjungan_posyandu: [], // array utama
      advancedFilter_anak: {
        status_gizi: '',
        lokasi: '',
        intervensi: '',
        status_list: [],
        lokasi_list: [],
        intervensi_list: []
      },
      selectedAnak: {
        nama: 'Aluna Daneen Azqiara',
        gender: 'Perempuan',
        alamat: 'Desa Wonosari, Kec. Bojong Gede',
        posyandu: 'Posyandu Mawar',
        status_gizi: 'Stunting',
        status_gizi_kategori: 'BB/U',
        riwayat_penimbangan: [
          { tanggal: '22/05/2025', bb_u: '0.35', tb_u: '0.85', bb_tb: 'Stunting' },
          { tanggal: '18/06/2025', bb_u: '0.22', tb_u: '0.89', bb_tb: 'Stunting' },
          { tanggal: '20/07/2025', bb_u: '0.25', tb_u: '0.92', bb_tb: 'Stunting' },
        ],
        riwayat_intervensi: [
          { tanggal: '22/05/2025', kader: 'Siti R.', intervensi: 'PMT' },
          { tanggal: '18/06/2025', kader: 'Siti R.', intervensi: 'PMT' },
          { tanggal: '20/07/2025', kader: 'Siti R.', intervensi: 'PMT' },
        ]
      },
      //selectedAnak: null,
      headers_kunjungan: [
        { text: 'Nama', value: 'nama' },
        { text: 'NIK', value: 'nik' },
        { text: 'Usia (bln)', value: 'usia' },
        { text: 'JK', value: 'gender' },
        { text: 'Tgl Ukur Terakhir', value: 'tgl_ukur' },
        { text: 'Intervensi', value: 'intervensi' },
        { text: 'Status Gizi', value: 'status_gizi' },
        { text: 'BB/U', value: 'bb_u' },
        { text: 'TB/U', value: 'tb_u' },
        { text: 'BB/TB', value: 'bb_tb' },
      ],
      kunjungan_posyandu: [
        { nama: 'Aluna Daneen Azqiara', nik: '3403012012930002', usia: 24, gender: 'P', tgl_ukur: '20-07-25', intervensi: 'PMT', status_gizi: 'Stunting', bb_u: 3.3, tb_u: 2.3, bb_tb: 2.3 },
        { nama: 'Arkhansa Raffasya Pamulat', nik: '3403010508980002', usia: 26, gender: 'L', tgl_ukur: '20-07-25', intervensi: 'PMT', status_gizi: 'Stunting', bb_u: 1.5, tb_u: 1.5, bb_tb: 1.5 },
        { nama: 'Askara Gedhe Manah Sinatrya', nik: '3403011105950001', usia: 20, gender: 'L', tgl_ukur: '20-07-25', intervensi: 'BLT', status_gizi: 'Wasting', bb_u: 2.3, tb_u: 2.3, bb_tb: 2.3 },
        { nama: 'Azka Maulana Fadil', nik: '3403011212980002', usia: 23, gender: 'L', tgl_ukur: '20-07-25', intervensi: 'PKH', status_gizi: 'Underweight', bb_u: 1.8, tb_u: 1.8, bb_tb: 1.8 },
        { nama: 'Irshad Ghani Arvarizi', nik: '3403012507930001', usia: 28, gender: 'L', tgl_ukur: '20-07-25', intervensi: '-', status_gizi: 'Normal', bb_u: 6.5, tb_u: 6.5, bb_tb: 6.5 },
        { nama: 'Syiffa Azahra', nik: '3403012806910002', usia: 24, gender: 'P', tgl_ukur: '20-07-25', intervensi: '-', status_gizi: 'Normal', bb_u: 5.5, tb_u: 5.5, bb_tb: 5.5 },
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

      headers_pending:[
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
      kelahiranPending: [
        {
          no_kia: '',
          nama:'Monkey D. Luffy',
          nik_ayah:'1870965231876523',
          nama_ayah:'David John',
          nik_ibu:'3127093421874560',
          nama_ibu:'Sakura',
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
          no_kia: '',
          nama:'Nani W',
          nik_ayah:'',
          nama_ayah:'Shikamaru',
          nik_ibu:'',
          nama_ibu:'Temari',
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

      pending_kunjungan: [
        { text: "Nama", value: "nama", sortable: true },
        { text: "NIK", value: "nik", sortable: false },
        { text: "Jenis Kelamin", value: "gender", sortable: false },
        { text: "Tanggal Lahir", value: "tgl_lahir", sortable: true },
        { text: "Nama Ibu", value: "nama_ibu", sortable: false },
        { text: "BB", value: "bb", sortable: false },
        { text: "TB", value: "tb", sortable: false },
        { text: "Tanggal Kunjungan", value: "kunjungan", sortable: true },
        { text: "Edit", value: "edit", sortable: false },
      ],
      itemsPending: [
        {
          nama: "Hadrika Satrio Aji",
          nik: "",
          gender: "Laki Laki",
          tgl_lahir: "26-05-2022",
          nama_ibu: "Annisa Hardaningtyas",
          bb: "5.25",
          tb: "55",
          kunjungan: "20-08-2025",
        },
        {
          nama: "Nasrina Ningtyas",
          nik: "",
          gender: "Perempuan",
          tgl_lahir: "20-01-2023",
          nama_ibu: "Erni Sulistyowati",
          bb: "",
          tb: "",
          kunjungan: "20-08-2025",
        },
      ],
      // filter kelahiran
      filter_anak: {
        nik: '',
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
    filteredKunjungan() {
      if (!this.kunjungan_posyandu) return []

      return this.kunjungan_posyandu.filter(item => {
        const matchGiziSelect = this.advancedFilter_anak?.status_gizi
          ? item.status_gizi?.toLowerCase() === this.advancedFilter_anak.status_gizi.toLowerCase()
          : true

        const matchGiziCheckbox =
          (this.advancedFilter_anak?.status_list?.length || 0) > 0
            ? this.advancedFilter_anak.status_list.some(
                s => item.status_gizi?.toLowerCase() === s.toLowerCase()
              )
            : true

        const matchLokasiSelect = this.advancedFilter_anak?.lokasi
          ? item.lokasi?.toLowerCase() === this.advancedFilter_anak.lokasi.toLowerCase()
          : true

        const matchLokasiCheckbox =
          (this.advancedFilter_anak?.lokasi_list?.length || 0) > 0
            ? this.advancedFilter_anak.lokasi_list.some(
                l => item.lokasi?.toLowerCase() === l.toLowerCase()
              )
            : true

        const matchIntervensiSelect = this.advancedFilter_anak?.intervensi
          ? item.intervensi?.toLowerCase() === this.advancedFilter_anak.intervensi.toLowerCase()
          : true

        const matchIntervensiCheckbox =
          (this.advancedFilter_anak?.intervensi_list?.length || 0) > 0
            ? this.advancedFilter_anak.intervensi_list.some(
                i => item.intervensi?.toLowerCase() === i.toLowerCase()
              )
            : true

        return (
          matchGiziSelect &&
          matchGiziCheckbox &&
          matchLokasiSelect &&
          matchLokasiCheckbox &&
          matchIntervensiSelect &&
          matchIntervensiCheckbox
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
    pendingCount() {
      return this.kelahiranPending.length
    },
  },
  methods: {
    methods: {
  applyFilter_anak() {
    // computed already updates automatically
  },

  showDetail(row) {
    this.selectedAnak = row
  },

  downloadRiwayat() {
      if (!this.selectedAnak) {
        alert('Silakan pilih anak terlebih dahulu.')
        return
      }

      // Data utama anak
      const anak = this.selectedAnak
      let csvContent = `Data Anak\n`
      csvContent += `Nama,${anak.nama}\n`
      csvContent += `NIK,${anak.nik}\n`
      csvContent += `Jenis Kelamin,${anak.gender === 'L' ? 'Laki-laki' : anak.gender === 'P' ? 'Perempuan' : anak.gender}\n`
      csvContent += `Usia (${anak.usia} bulan),${anak.usia}\n`
      csvContent += `Tgl Ukur Terakhir,${anak.tgl_ukur}\n`
      csvContent += `Status Gizi,${anak.status_gizi}\n`
      csvContent += `Intervensi,${anak.intervensi}\n\n`

      // Riwayat penimbangan (jika ada)
      if (anak.riwayat_penimbangan && anak.riwayat_penimbangan.length) {
        csvContent += `Riwayat Penimbangan\nTanggal,Status BB/U,Status TB/U,Status BB/TB\n`
        anak.riwayat_penimbangan.forEach(r => {
          csvContent += `${r.tanggal},${r.bb_u},${r.tb_u},${r.bb_tb}\n`
        })
        csvContent += `\n`
      }

      // Riwayat intervensi/bantuan (jika ada)
      if (anak.riwayat_intervensi && anak.riwayat_intervensi.length) {
        csvContent += `Riwayat Intervensi / Bantuan\nTanggal,Kader,Intervensi\n`
        anak.riwayat_intervensi.forEach(r => {
          csvContent += `${r.tanggal},${r.kader},${r.intervensi}\n`
        })
        csvContent += `\n`
      }

      // Konversi ke blob dan download
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `Riwayat_${anak.nama.replace(/\s+/g, '_')}.csv`)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }
  },
    applyFilter_anak() {
      // computed already updates automatically
    },
    showDetail(props) {
      console.log('Klik props:', props)
      this.selectedAnak = props
    },
    cariData() {
      this.notFound = false
      this.found = false
      const hasil = this.kunjungan_posyandu.find(
        (d) => d.nik_ibu === this.searchNIK || d.nik_ayah === this.searchNIK
      )

      if (hasil) {
        this.form_kunjungan = { ...hasil }
        this.showForm = true
        this.found = true
      } else {
        this.showForm = false
        this.notFound = true
      }
    },
    kirimData() {
      alert('Data berhasil dikirim!')
      console.log('Data dikirim:', this.form_kunjungan)
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
    /* applyAdvancedFilter_anak() {
      // Salin isi input advancedFilter ke appliedAdvancedFilter
      this.appliedAdvancedFilter_anak = { ...this.advancedFilter_anak }
    }, */
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

    // Reset semua filter
    resetFilter_anak() {
      this.filter_anak.nik = ''
      this.advancedFilter_anak = {
        status_gizi: '',
        lokasi: '',
        intervensi: '',
        status_list: [],
        lokasi_list: [],
        intervensi_list: [],
      }
      console.log('♻️ Filter direset')
      // Bisa juga langsung reload data default
      // this.loadAnak()
    },
    /* resetFilter_anak() {
      this.advancedFilter_anak = {
        nama: '',
        no_kia: '',
        gender: '',
        tgl_lahir: '',
        kunjungan: '',
      }
      this.appliedAdvancedFilter_anak = { ...this.advancedFilter_anak }
    }, */
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
