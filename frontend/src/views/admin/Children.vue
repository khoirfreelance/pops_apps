<template>
  <div class="wrapper">
    <!-- 🔄 Spinner Overlay -->
    <transition name="fade">
      <div
        v-if="isLoading"
        class="spinner-overlay d-flex justify-content-center align-items-center"
      >
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem">
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
        'sidebar-expanded': !isCollapsed,
      }"
    >
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Content -->
        <div class="py-4 container-fluid">
          <!-- Welcome Card -->
          <Welcome />

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <div class="title mb-0 text-capitalize fw-bold" style="font-size: 23px">
                Laporan Status Gizi Desa {{ this.kelurahan }} Periode {{ periodeTitle }}
                <!-- Laporan Status Gizi Desa {{ this.kelurahan }} Periode {{ this.filters.periodeAwal.replace('+', ' ') }} - {{ this.filters.periodeAkhir.replace('+', ' ') }} -->
              </div>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 mt-0 d-none d-md-block">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- === BBU === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20 position-relative">
                <label class="form-label text-primary">Filter Status Gizi:</label>
                <div class="dropdown w-100">
                  <button class="form-select text-start text-truncate" data-bs-toggle="dropdown">
                    <span v-if="filters.bbu.length === 0" class="text-muted"
                      >Pilih Underweight</span
                    >
                    <span v-else class="selected-text" :title="underDisplayText">{{
                      underDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in bbuOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-bbu-' + item"
                        :value="item"
                        v-model="filters.bbu"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-bbu-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_bbu"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_bbu"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === TBU === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button class="form-select text-start text-truncate" data-bs-toggle="dropdown">
                    <span v-if="filters.tbu.length === 0" class="text-muted">Pilih Stunting</span>
                    <span v-else class="selected-text" :title="StuntingDisplayText">{{
                      StuntingDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in tbuOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-tbu-' + item"
                        :value="item"
                        v-model="filters.tbu"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-tbu-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_tbu"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_tbu"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === BBTB === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button class="form-select text-start text-truncate" data-bs-toggle="dropdown">
                    <span v-if="filters.bbtb.length === 0" class="text-muted">Pilih Wasting</span>
                    <span v-else class="selected-text" :title="WastingDisplayText">{{
                      WastingDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in bbtbOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-bbtb-' + item"
                        :value="item"
                        v-model="filters.bbtb"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-bbtb-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_bbtb"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_bbtb"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === STAGNAN === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button class="form-select text-start text-truncate" data-bs-toggle="dropdown">
                    <span v-if="filters.stagnan.length === 0" class="text-muted"
                      >Pilih BB Stagnan</span
                    >
                    <span v-else class="selected-text" :title="stagnanDisplayText">{{
                      stagnanDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in stagnanOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-stag-' + item"
                        :value="item"
                        v-model="filters.stagnan"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-stag-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_stagnan"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_stagnan"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === INTERVENSI === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button class="form-select text-start text-truncate" data-bs-toggle="dropdown">
                    <span v-if="filters.intervensi.length === 0" class="text-muted"
                      >Pilih Intervensi</span
                    >
                    <span v-else class="selected-text" :title="intervesiDisplayText">{{
                      intervesiDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in intervensiOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-int-' + item"
                        :value="item"
                        v-model="filters.intervensi"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-int-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_intervensi"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_intervensi"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === POSYANDU === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <label class="form-label text-primary">Filter Lokasi</label>
                <select
                  v-model="filters.posyandu"
                  class="form-select text-muted"
                  @change="handlePosyanduChange"
                >
                  <option value="">Pilih Posyandu</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- === RW === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- === RT === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- === PERIODE === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <label class="form-label text-primary">Pilih Periode:</label>
                <select v-model="filters.periodeAwal" class="form-select text-muted">
                  <option value="">Start from</option>
                  <option v-for="periode in periodeOptions" :key="periode">{{ periode }}</option>
                </select>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <label class="form-label">&nbsp;</label>
                <select v-model="filters.periodeAkhir" class="form-select text-muted">
                  <option value="">To</option>
                  <option v-for="periode in periodeOptions" :key="periode">{{ periode }}</option>
                </select>
              </div>

              <!-- === BUTTONS === -->
              <div class="col-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-gradient fw-semibold me-2">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <!-- Mobile Filter -->
          <!-- Floating Button -->
          <button
            class="btn btn-primary filter-float-btn rounded-pill shadow-lg px-4 py-2"
            @click="mobileFilterOpen = true"
          >
            <i class="bi bi-funnel"></i> Filter
          </button>

          <!-- FILTER MOBILE SLIDE PANEL -->
          <div class="filter-mobile-panel d-md-none" :class="{ open: mobileFilterOpen }">
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold">Filter</h5>
              <button class="btn btn-light" @click="mobileFilterOpen = false">
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <!-- === FORM FILTER MOBILE === -->
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- === BBU === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20 position-relative">
                <label class="form-label text-primary">Filter Status Gizi:</label>
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start text-truncate"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span v-if="filters.bbu.length === 0" class="text-muted"
                      >Pilih Underweight</span
                    >
                    <span v-else class="selected-text" :title="underDisplayText">{{
                      underDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in bbuOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-bbu-' + item"
                        :value="item"
                        v-model="filters.bbu"
                      />
                      <label class="form-check-label w-100 text-truncate" :for="'chk-bbu-' + item">
                        {{ item }}
                      </label>
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_bbu"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_bbu"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === TBU === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start text-truncate"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span v-if="filters.tbu.length === 0" class="text-muted">Pilih Stunting</span>
                    <span v-else class="selected-text" :title="StuntingDisplayText">{{
                      StuntingDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in tbuOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-tbu-' + item"
                        :value="item"
                        v-model="filters.tbu"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-tbu-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_tbu"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_tbu"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === BBTB === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start text-truncate"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span v-if="filters.bbtb.length === 0" class="text-muted">Pilih Wasting</span>
                    <span v-else class="selected-text" :title="WastingDisplayText">{{
                      WastingDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in bbtbOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-bbtb-' + item"
                        :value="item"
                        v-model="filters.bbtb"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-bbtb-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_bbtb"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_bbtb"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === STAGNAN === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start text-truncate"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span v-if="filters.stagnan.length === 0" class="text-muted"
                      >Pilih BB Stagnan</span
                    >
                    <span v-else class="selected-text" :title="stagnanDisplayText">{{
                      stagnanDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in stagnanOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-stag-' + item"
                        :value="item"
                        v-model="filters.stagnan"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-stag-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_stagnan"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_stagnan"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === INTERVENSI === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <div class="dropdown w-100">
                  <button
                    class="form-select text-start text-truncate"
                    data-bs-toggle="dropdown"
                    data-bs-display="static"
                  >
                    <span v-if="filters.intervensi.length === 0" class="text-muted"
                      >Pilih Intervensi</span
                    >
                    <span v-else class="selected-text" :title="intervesiDisplayText">{{
                      intervesiDisplayText
                    }}</span>
                  </button>

                  <ul class="dropdown-menu" style="max-height: 260px; overflow-y: auto">
                    <li
                      v-for="item in intervensiOptions"
                      :key="item"
                      class="dropdown-item d-flex align-items-center"
                    >
                      <input
                        type="checkbox"
                        class="form-check-input me-2"
                        :id="'chk-int-' + item"
                        :value="item"
                        v-model="filters.intervensi"
                      />
                      <label
                        class="form-check-label w-100 text-truncate"
                        :for="'chk-int-' + item"
                        >{{ item }}</label
                      >
                    </li>
                    <li><hr class="dropdown-divider" /></li>
                    <li class="d-flex justify-content-between px-2">
                      <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        @click="selectAll_intervensi"
                      >
                        Pilih Semua
                      </button>
                      <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        @click="clearAll_intervensi"
                      >
                        Hapus Semua
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- === POSYANDU === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <label class="form-label text-primary">Filter Lokasi</label>
                <select
                  v-model="filters.posyandu"
                  class="form-select text-muted"
                  @change="handlePosyanduChange"
                >
                  <option value="">Pilih Posyandu</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- === RW === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <select v-model="filters.rw" class="form-select text-muted" :disabled="rwReadonly">
                  <option value="">Pilih RW</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- === RT === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <select v-model="filters.rt" class="form-select text-muted" :disabled="rtReadonly">
                  <option value="">Pilih RT</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- === PERIODE === -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <label class="form-label text-primary">Pilih Periode:</label>
                <select v-model="filters.periodeAwal" class="form-select text-muted">
                  <option value="">Awal</option>
                  <option v-for="periode in periodeOptions" :key="periode">{{ periode }}</option>
                </select>
              </div>

              <div class="col-12 col-sm-6 col-md-4 col-lg-auto custom-20">
                <label class="form-label">&nbsp;</label>
                <select v-model="filters.periodeAkhir" class="form-select text-muted">
                  <option value="">Akhir</option>
                  <option v-for="periode in periodeOptions" :key="periode">{{ periode }}</option>
                </select>
              </div>

              <!-- BUTTONS -->
              <div class="col-12 d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-gradient fw-semibold me-2">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-secondary fw-semibold" @click="resetFilter">
                  <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
              </div>
            </form>
          </div>

          <div class="text-center mt-3">
            <div class="ringkasan-header fw-bold text-success mb-3">
              Ringkasan Statistik
            </div>
          </div>

          <!-- Ringkasan Statistik-->
          <div class="container-fluid my-4">
            <div class="row">
              <div class="col-xl-10 col-sm-12">
                <div class="row justify-content-center">
                  <div
                    v-for="(item, index) in gizi"
                    :key="index"
                    class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-12 mb-3 no-col-padding"
                  >
                    <div
                      class="card border-0 rounded-3 overflow-hidden custom-card-size shadow"
                      :class="`border-start border-4 border-${item.color}`"
                      style="width: 108%"
                    >
                      <div class="card-body position-relative">
                        <!-- TITLE -->
                        <h5 class="fw-bold mb-1" style="font-size: 16px;">{{ item.title }}</h5>

                        <!-- VALUE -->
                        <h3 class="fw-bold mb-0" :class="`text-${item.color}`" style="font-size: 20px;">
                          {{ item.value }}
                        </h3>

                        <!-- PERCENT -->
                        <p
                          class="position-absolute bottom-0 end-0 mb-1 me-2 small"
                          :class="`text-${item.color}`" style="font-size: 16px;"
                        >
                          {{ item.percent }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- TOTAL ANAK -->
              <div class="col-xl-2 col-sm-12">
                <div
                  class="card border-0 rounded-3 overflow-hidden custom-card-size shadow text-center"
                  style="width: 108%"
                >
                  <h3 class="text-muted fw-bold">Total Anak Balita</h3>
                  <div class="flex-grow-1 d-flex flex-column justify-content-center">
                    <h1 class="fw-bold text-success mb-0">{{ totalAnak }}</h1>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Table and detail Section -->
          <div class="container-fluid mt-4">
            <h5 class="table-name text-success mb-3">Data Anak</h5>
            <div class="row mt-4">
              <div :class="selectedAnak ? 'col-md-8' : 'col-md-12'">
                <div class="card bg-light p-2">
                  <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                      <thead class="table-success">
                        <tr>
                          <th
                            @click="sortBy('no')"
                            class="cursor-pointer align-middle text-center frozen-column th-font"
                            rowspan="2"
                          >
                            No <SortIcon :field="'no'" />
                          </th>
                          <th
                            @click="sortBy('nama')"
                            class="cursor-pointer align-middle text-center frozen-column th-font"
                            rowspan="2"
                          >
                            Nama <SortIcon :field="'nama'" />
                          </th>
                          <th
                            @click="sortBy('posyandu')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            Posyandu <SortIcon :field="'posyandu'" />
                          </th>
                          <th
                            style="width: 100px"
                            @click="sortBy('usia')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            Usia (bln) <SortIcon :field="'usia'" />
                          </th>
                          <th
                            style="width: 60px"
                            @click="sortBy('gender')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            JK <SortIcon :field="'gender'" />
                          </th>
                          <th
                            @click="sortBy('tgl_ukur')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            Tgl Ukur Terakhir <SortIcon :field="'tgl_ukur'" />
                          </th>
                          <th
                            @click="sortBy('intervensi')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            Intervensi <SortIcon :field="'intervensi'" />
                          </th>
                          <th colspan="3" class="text-center th-font">Status</th>
                          <th
                            @click="sortBy('rw')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            RW <SortIcon :field="'rw'" />
                          </th>
                          <th
                            @click="sortBy('rt')"
                            class="cursor-pointer align-middle text-center th-font"
                            rowspan="2"
                          >
                            RT <SortIcon :field="'rt'" />
                          </th>
                        </tr>
                        <tr>
                          <th @click="sortBy('tbu')" class="cursor-pointer text-center th-font">
                            TB/U <SortIcon :field="'tbu'" />
                          </th>
                          <th @click="sortBy('bbu')" class="cursor-pointer text-center th-font">
                            BB/U <SortIcon :field="'bbu'" />
                          </th>
                          <th @click="sortBy('bbtb')" class="cursor-pointer text-center th-font">
                            BB/TB <SortIcon :field="'bbtb'" />
                          </th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr v-for="(anak,i) in paginatedData" :key="anak.id">
                          <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
                          <td class="text-start frozen-column td-font">
                            <a
                              href="#"
                              @click.prevent="showDetail(anak)"
                              class="fw-semibold text-decoration-underline text-primary td-font"
                            >
                              {{ anak.nama }}
                            </a>
                          </td>
                          <td class="td-font">{{ anak.posyandu }}</td>
                          <td class="td-font">{{ anak.usia }}</td>
                          <td class="td-font">{{ anak.gender }}</td>
                          <td class="td-font">{{ anak.tgl_ukur }}</td>
                          <td class="td-font">{{ anak.intervensi || '-' }}</td>
                          <td class="td-font">
                            <span class="td-font"
                              :class="{
                                'badge px-3 py-2 bg-danger': anak.tbu === 'Severely Stunted',
                                'badge px-3 py-2 bg-warning text-dark': anak.tbu === 'Stunted',
                                'text-dark': anak.tbu === 'Normal',
                              }"
                            >
                              {{ anak.tbu }}
                            </span>
                          </td>
                          <td>
                            <span class="td-font"
                              :class="{
                                'badge px-3 py-2 bg-danger': anak.bbu === 'Severely Underweight',
                                'badge px-3 py-2 bg-warning text-dark': anak.bbu === 'Underweight',
                                'text-dark': anak.bbu === 'Normal',
                              }"
                            >
                              {{ anak.bbu }}
                            </span>
                          </td>
                          <td>
                            <span class="td-font"
                              :class="{
                                'badge px-3 py-2 bg-danger': anak.bbtb === 'Severely Wasted',
                                'badge px-3 py-2 bg-warning text-dark': anak.bbtb === 'Wasted',
                                'text-dark': anak.bbtb === 'Normal',
                              }"
                            >
                              {{ anak.bbtb }}
                            </span>
                          </td>
                          <td class="td-font">{{ anak.rw }}</td>
                          <td class="td-font">{{ anak.rt }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Pagination -->
                  <nav>
                    <ul class="pagination justify-content-center">
                      <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)"
                          >Prev</a
                        >
                      </li>

                      <li
                        class="page-item"
                        v-for="page in visiblePages"
                        :key="page"
                        :class="{ active: currentPage === page, disabled: page === '...' }"
                      >
                        <a
                          v-if="page !== '...'"
                          class="page-link"
                          href="#"
                          @click.prevent="changePage(page)"
                          >{{ page }}</a
                        >
                        <span v-else class="page-link">...</span>
                      </li>

                      <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)"
                          >Next</a
                        >
                      </li>
                    </ul>
                  </nav>
                </div>
              </div>

              <!-- DETAIL DATA -->
              <div class="col-md-4" v-if="selectedAnak">
                <div
                  v-if="selectedAnak"
                  class="card shadow-sm p-4 text-center position-relative"
                >
                  <!-- Tombol Close -->
                  <button
                    type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedAnak = null"
                  ></button>

                  <!-- Nama dan Identitas -->
                  <h2 class="fw-bold text-dark mb-1">{{ selectedAnak.nama }}</h2>
                  <p class="text-muted mb-0" style="font-size: 14px">
                    {{
                      selectedAnak.gender === 'L'
                        ? 'Laki-laki'
                        : selectedAnak.gender === 'P'
                          ? 'Perempuan'
                          : selectedAnak.gender
                    }}
                  </p>

                  <p class="text-muted mb-0 text-capitalize" style="font-size: 14px">
                    {{ selectedAnak.alamat || 'Desa Wonosari, Kec. Bojong Gede' }}
                  </p>
                  <p class="text-muted" style="font-size: 14px">{{ selectedAnak.posyandu || 'Posyandu Mawar' }}</p>

                  <!-- Badge Status Gizi -->
                  <div class="mb-3">
                    <span
                      class="badge px-3 py-2 small"
                      :class="{
                        'bg-danger': ['Severely Wasted', 'Obesitas'].includes(
                          selectedAnak.status_gizi,
                        ),
                        'bg-warning text-dark': [
                          'Wasted',
                          'Possible risk of Overweight',
                          'Overweight',
                        ].includes(selectedAnak.status_gizi),
                        'bg-success': selectedAnak.status_gizi === 'Normal',
                      }"
                    >
                      {{ selectedAnak.status_gizi }}
                    </span>
                  </div>

                  <!-- Riwayat Penimbangan -->
                  <h2 class="fw-bold text-start text-secondary mt-2">Riwayat Penimbangan</h2>
                  <div class="table-responsive">
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
                        <tr
                          v-for="(r, i) in (selectedAnak.riwayat_penimbangan || []).slice(-3)"
                          :key="i"
                        >
                          <td>{{ this.formatDate(r.tanggal) }}</td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.bbu === 'Severely Underweight',
                                'bg-warning text-dark': ['Risiko BB Lebih', 'Underweight'].includes(
                                  r.bbu,
                                ),
                                'bg-success': r.bbu === 'Normal',
                              }"
                            >
                              {{ r.bbu }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.tbu === 'Severely Stunted',
                                'bg-warning text-dark': r.tbu === 'Stunted',
                                'bg-success': r.tbu === 'Normal',
                              }"
                            >
                              {{ r.tbu }}
                            </span>
                          </td>
                          <td>
                            <span
                              class="badge"
                              :class="{
                                'bg-danger': r.bbtb === 'Severely Wasted',
                                'bg-warning text-dark': [
                                  'Wasted',
                                  'Possible risk of Overweight',
                                  'Overweight',
                                ].includes(r.bbtb),
                                'bg-success': r.bbtb === 'Normal',
                              }"
                            >
                              {{ r.bbtb }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Riwayat Intervensi -->
                  <h2 class="fw-bold text-start text-secondary mt-3">
                    Riwayat Intervensi / Bantuan
                  </h2>
                  <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle text-center">
                      <thead class="table-light">
                        <tr>
                          <th>Tanggal</th>
                          <th>Kader</th>
                          <th>Intervensi</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(i, idx) in (selectedAnak.riwayat_intervensi || []).slice(-3)" :key="idx">
                          <td>{{ this.formatDate(i.tanggal) }}</td>
                          <td>{{ i.kader }}</td>
                          <td>{{ i.intervensi }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Tombol Download -->
                  <button
                    style="background-color: #0d8cff"
                    class="btn btn-primary rounded-pill px-4 mt-2 fw-semibold"
                    @click="downloadRiwayat"
                  >
                    Download Riwayat
                  </button>
                </div>
              </div>

              <!-- Detail Riwayat Anak -->
              <div class="col-md-12 mt-4" v-if="selectedAnak" id="detailSection">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative">
                  <!-- Tombol Close -->
                  <button
                    class="btn-close position-absolute top-0 end-0 m-3"
                    aria-label="Close"
                    @click="selectedAnak = null"
                  ></button>

                  <!-- Header -->
                  <div class="bg-danger text-white p-4 text-center rounded-top">
                    <h4 class="fw-bold mb-0">{{ selectedAnak.nama }}</h4>
                    <p class="text-white mb-0 small">
                      NIK: {{ selectedAnak.nik }} •
                      <span class="text-capitalize">
                        {{ selectedAnak.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                      </span>
                      Usia: {{ selectedAnak.usia }} bln
                    </p>
                  </div>

                  <!-- Tabs -->
                  <div class="p-3">
                    <ul
                      class="nav nav-pills justify-content-center mb-4 flex-wrap gap-2"
                      id="anakDetailTab"
                      role="tablist"
                    >
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link active"
                          id="tab-profile-anak"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-profile-anak"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-person-badge me-1"></i> Profile Anak
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-kelahiran"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-kelahiran"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-clipboard-heart me-1"></i> Data Kelahiran
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-kunjungan"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-kunjungan"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-hospital me-1"></i> Kunjungan Posyandu
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-intervensi"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-intervensi"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-activity me-1"></i> Intervensi
                        </button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button
                          class="nav-link"
                          id="tab-tpk"
                          data-bs-toggle="tab"
                          data-bs-target="#tab-pane-tpk"
                          type="button"
                          role="tab"
                        >
                          <i class="bi bi-clipboard2-check me-1"></i> Pendampingan TPK
                        </button>
                      </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="anakDetailTabContent">
                      <!-- Profile Anak -->
                      <div
                        class="tab-pane fade show active"
                        id="tab-pane-profile-anak"
                        role="tabpanel"
                      >
                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm p-3 h-100">
                              <h6 class="tab-pane-sub-title mb-3 text-danger">Identitas Anak</h6>
                              <p class="mb-1"><strong>Nama:</strong> {{ selectedAnak.nama }}</p>
                              <p class="mb-1"><strong>NIK:</strong> {{ selectedAnak.nik }}</p>
                              <p class="mb-1">
                                <strong>Jenis Kelamin:</strong>
                                {{ selectedAnak.gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                              </p>
                              <p class="mb-1">
                                <strong>Usia:</strong> {{ selectedAnak.usia }} bulan
                              </p>
                              <p class="mb-1"><strong>Alamat:</strong> {{ selectedAnak.alamat }}</p>
                              <p class="mb-1">
                                <strong>Desa/Kecamatan:</strong>
                                {{ selectedAnak.desa }}, {{ selectedAnak.kecamatan }}
                              </p>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="card bg-light border-0 shadow-sm p-3 h-100">
                              <h6 class="tab-pane-sub-title mb-3 text-danger">Orang Tua</h6>
                              <p class="mb-1">
                                <strong>Ayah:</strong> {{ selectedAnak.nama_ayah }}
                              </p>
                              <p class="mb-1">
                                <strong>NIK Ayah:</strong> {{ selectedAnak.nik_ayah }}
                              </p>
                              <p class="mb-1"><strong>Ibu:</strong> {{ selectedAnak.nama_ibu }}</p>
                              <p class="mb-1">
                                <strong>NIK Ibu:</strong> {{ selectedAnak.nik_ibu }}
                              </p>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Data Kelahiran -->
                      <div class="tab-pane fade" id="tab-pane-kelahiran" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="tab-pane-sub-title mb-3 text-danger">Data Kelahiran</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead>
                                <tr>
                                  <th>No. KIA</th>
                                  <th>Tempat Lahir</th>
                                  <th>Tanggal Lahir</th>
                                  <th>Berat (kg)</th>
                                  <th>Panjang (cm)</th>
                                  <th>Jenis Persalinan</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedAnak.kelahiran"
                                  :key="'kelahiran-' + i"
                                >
                                  <td>{{ item.no_kia }}</td>
                                  <td>{{ item.tmpt_dilahirkan }}</td>
                                  <td>{{ this.formatDate(item.tgl_lahir) }}</td>
                                  <td>{{ item.bb }}</td>
                                  <td>{{ item.pb }}</td>
                                  <td class="text-capitalize">{{ item.jenis }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      <!-- Data Kunjungan Posyandu -->
                      <div class="tab-pane fade" id="tab-pane-kunjungan" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="tab-pane-sub-title mb-3 text-danger">Riwayat Penimbangan</h6>
                          <div class="table-responsive mb-4">
                            <table class="table table-sm table-hover align-middle">
                              <thead class="table-secondary">
                                <tr>
                                  <th>Tanggal</th>
                                  <th>Status BB</th>
                                  <th>Status TB</th>
                                  <th>Status BB/TB</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(riwayat, i) in selectedAnak.riwayat_penimbangan"
                                  :key="'penimbangan-' + i"
                                >
                                  <td>{{ this.formatDate(riwayat.tanggal) }}</td>
                                  <td
                                    :class="{
                                      'text-danger': riwayat.bbu === 'Severely Underweight',
                                      'text-secondary': riwayat.bbu === 'Underweight'
                                    }"
                                  >
                                    {{ riwayat.bbu }}
                                  </td>
                                  <td
                                    :class="{
                                      'text-danger': riwayat.tbu === 'Severely Stunted',
                                      'text-secondary': riwayat.tbu === 'Stunted'
                                    }"
                                  >
                                  {{ riwayat.tbu }}</td>
                                  <td
                                    :class="{
                                      'text-danger': riwayat.bbtb === 'Severely Wasted',
                                      'text-secondary': riwayat.bbtb === 'Wasted'
                                    }"
                                  >
                                    {{ riwayat.bbtb }}
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>

                          <div class="row g-3">

                            <!-- Tabs Header -->
                            <ul class="nav nav-tabs mb-3" id="chartTab" role="tablist">
                              <li class="nav-item" role="presentation">
                                <button
                                  class="nav-link active"
                                  id="bb-tab"
                                  data-bs-toggle="tab"
                                  data-bs-target="#bb"
                                  type="button"
                                  role="tab"
                                  aria-controls="bb"
                                  aria-selected="true"
                                >
                                  BB/U
                                </button>
                              </li>
                              <li class="nav-item" role="presentation">
                                <button
                                  class="nav-link"
                                  id="tb-tab"
                                  data-bs-toggle="tab"
                                  data-bs-target="#tb"
                                  type="button"
                                  role="tab"
                                  aria-controls="tb"
                                  aria-selected="false"
                                >
                                  TB/U
                                </button>
                              </li>
                              <li class="nav-item" role="presentation">
                                <button
                                  class="nav-link"
                                  id="bbtb-tab"
                                  data-bs-toggle="tab"
                                  data-bs-target="#bbtb"
                                  type="button"
                                  role="tab"
                                  aria-controls="tb"
                                  aria-selected="false"
                                >
                                  BB/TB
                                </button>
                              </li>
                            </ul>

                            <!-- Tabs Content -->
                            <div class="tab-content" id="chartTabContent">

                              <!-- BB/U TAB -->
                              <div
                                class="tab-pane fade show active"
                                id="bb"
                                role="tabpanel"
                                aria-labelledby="bb-tab"
                              >
                                <div class="col-12">
                                  <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                      <h6 class="mb-2">Grafik BB/U</h6>
                                      <div style="height: 350px;">
                                        <canvas ref="chart_bbu"></canvas>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <!-- TB/U TAB -->
                              <div
                                class="tab-pane fade"
                                id="tb"
                                role="tabpanel"
                                aria-labelledby="tb-tab"
                              >
                                <div class="col-12">
                                  <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                      <h6 class="mb-2">Grafik TB/U</h6>
                                      <div style="height: 350px;">
                                        <canvas ref="chart_tbu"></canvas>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>

                              <!-- BB/TB TAB -->
                              <div
                                class="tab-pane fade"
                                id="bbtb"
                                role="tabpanel"
                                aria-labelledby="bbtb-tab"
                              >
                                <div class="col-12">
                                  <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                      <h6 class="mb-2">Grafik BB/TB</h6>
                                      <div style="height: 350px;">
                                        <canvas ref="chart_bbtb"></canvas>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>

                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Data Intervensi -->
                      <div class="tab-pane fade" id="tab-pane-intervensi" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="tab-pane-sub-title mb-3 text-danger">Riwayat Intervensi</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead class="table-secondary">
                                <tr>
                                  <th>Tanggal</th>
                                  <th>Kader</th>
                                  <th>Jenis Intervensi</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedAnak.riwayat_intervensi"
                                  :key="'intervensi-' + i"
                                >
                                  <td>{{ this.formatDate(item.tanggal) }}</td>
                                  <td>{{ item.kader }}</td>
                                  <td>{{ item.intervensi }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>

                      <!-- Data TPK -->
                      <div class="tab-pane fade" id="tab-pane-tpk" role="tabpanel">
                        <div class="card bg-light border-0 shadow-sm p-3">
                          <h6 class="tab-pane-sub-title mb-3 text-danger">Riwayat Pendampingan TPK</h6>
                          <div class="table-responsive">
                            <table class="table table-sm table-striped align-middle">
                              <thead class="table-secondary">
                                <tr>
                                  <th>Tanggal</th>
                                  <th>Petugas</th>
                                  <th>RT</th>
                                  <th>RW</th>
                                  <th>Berat Lahir</th>
                                  <th>Panjang Lahir</th>
                                  <th>Berat Badan</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr
                                  v-for="(item, i) in selectedAnak.riwayat_pendampingan"
                                  :key="'pendampingan-' + i"
                                >
                                  <td>{{ this.formatDate(item.tgl_pendampingan) }}</td>
                                  <td>{{ item.petugas }}</td>
                                  <td>{{ item.rt }}</td>
                                  <td>{{ item.rw }}</td>
                                  <td>{{ item.bb_lahir }}</td>
                                  <td>{{ item.pb_lahir }}</td>
                                  <td>{{ item.bb }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <CopyRight />
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import Welcome from '@/components/Welcome.vue'
import axios from 'axios'
// Import WHO Standards
import wfa from '@/assets/wfa.json'
import hfa from '@/assets/hfa.json'
import wfh from '@/assets/wfh.json'

import { ref, computed } from 'vue'
import {
  Chart,
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  Title,
  CategoryScale,
  Legend,
  Tooltip,
  Filler,
} from 'chart.js'

Chart.register(
  LineController,
  LineElement,
  PointElement,
  LinearScale,
  Title,
  CategoryScale,
  Legend,
  Tooltip,
  Filler,
)

// Simple sort icon component
const SortIcon = {
  props: ['field'],
  template: `<span v-if="$parent.sortKey === field">{{ $parent.sortDir === 'asc' ? '▲' : '▼' }}</span>`,
}

// PORT backend kamu
const API_PORT = 8000

const bulan = [
  'Januari', 'Februari', 'Maret', 'April',
  'Mei', 'Juni', 'Juli', 'Agustus',
  'September', 'Oktober', 'November', 'Desember'
]

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Children',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, SortIcon, Welcome },
  data() {
    return {
      periodeTitle:'',
      chartBBTB: null,
      chartBB: null,
      chartTB: null,
      chartInstance: {
        bbu: null,
        tbu: null,
        bbtb: null
      },
      whoData: {
        wfa,
        hfa,
        wfh
      },

      /* Wajib ada */
      file: null,
      fileName: '',
      fileSize: 0,
      filePreviewLines: '',
      fileError: '',
      uploading: false,
      uploadProgress: 0,
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      logoSrc: null,
      logoLoaded: true,
      totalKasus: 37,
      windowWidth: window.innerWidth,
      isFormOpen: false,
      isDataDrag: false,
      showAdvanced: false,
      totalAnak: 0,
      filters: {
        bbu: [],
        tbu: [],
        bbtb: [],
        stagnan: [],
        intervensi: [],
        posyandu: '',
        rw: '',
        rt: '',
        periodeAwal: '',
        periodeAkhir: '',
      },
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,
      intervensiOptions: [
        'MBG',
        'KIE',
        'Bansos',
        'PMT',
        'Lainnya',
        'Belum mendapatkan intervensi',
      ],
      bbuOptions: ['Severely Underweight', 'Underweight', 'Normal', 'Risiko BB Lebih'],
      tbuOptions: ['Severely Stunted', 'Stunted', 'Normal', 'Tinggi'],
      bbtbOptions: [
        'Severely Wasted',
        'Wasted',
        'Normal',
        'Possible risk of Overweight',
        'Overweight',
        'Obesitas',
      ],
      stagnanOptions: ['1 T', '2 T', '> 2 T'],
      // config
      ACCEPTED_EXT: ['csv'],
      ACCEPTED_MIME: ['text/csv', 'application/vnd.ms-excel', 'text/plain'],
      MAX_FILE_SIZE: 5 * 1024 * 1024, // 5 MB

      /* Just for som pages */
      gizi: [],
      selectedAnak: '',
      children: [],
      filteredData: [], // data hasil filter
      mobileFilterOpen: false,
    }
  },
  setup() {
    const searchQuery = ref('')
    const currentPage = ref(1)
    const perPage = ref(10)
    const sortKey = ref('')
    const sortDir = ref('asc')
    const filteredData = ref([])

    const applySearch = () => {
      const query = searchQuery.value.toLowerCase()
      filteredData.value = window.children.filter((c) =>
        Object.values(c).some((v) => String(v).toLowerCase().includes(query)),
      )
      currentPage.value = 1
    }

    const sortBy = (key) => {
      if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortKey.value = key
        sortDir.value = 'asc'
      }
      filteredData.value.sort((a, b) => {
        if (a[key] < b[key]) return sortDir.value === 'asc' ? -1 : 1
        if (a[key] > b[key]) return sortDir.value === 'asc' ? 1 : -1
        return 0
      })
    }

    const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value))

    const paginatedData = computed(() => {
      const start = (currentPage.value - 1) * perPage.value
      const end = start + perPage.value
      return filteredData.value.slice(start, end)
    })

    const visiblePages = computed(() => {
      const pages = []
      const total = totalPages.value
      const current = currentPage.value

      if (total <= 4) {
        for (let i = 1; i <= total; i++) pages.push(i)
      } else if (current <= 2) {
        // Halaman awal
        pages.push(1, 2, 3, '...', total)
      } else if (current >= total - 1) {
        // Halaman akhir
        pages.push(total - 2, total - 1, total)
      } else {
        // Tengah
        pages.push(current - 1, current, current + 1, '...', total)
      }

      return pages
    })

    const changePage = (page) => {
      if (page < 1 || page > totalPages.value || page === '...') return
      currentPage.value = page
    }

    //const getWHO_SD = () => {}

    return {
      //getWHO_SD,
      searchQuery,
      // eslint-disable-next-line vue/no-dupe-keys
      filteredData,
      currentPage,
      perPage,
      sortKey,
      sortDir,
      totalPages,
      paginatedData,
      applySearch,
      sortBy,
      changePage,
      visiblePages,
    }
  },
  methods: {
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
        anak.riwayat_penimbangan.forEach((r) => {
          csvContent += `${r.tanggal},${r.bb},${r.tb},${r.bb_tb}\n`
        })
        csvContent += `\n`
      }

      // Riwayat intervensi/bantuan (jika ada)
      if (anak.riwayat_intervensi && anak.riwayat_intervensi.length) {
        csvContent += `Riwayat Intervensi / Bantuan\nTanggal,Kader,Intervensi\n`
        anak.riwayat_intervensi.forEach((r) => {
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
    },
    formatDate(dateString) {
      if (!dateString) return '-'
      const date = new Date(dateString)
      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const year = date.getFullYear()
      return `${day}-${month}-${year}`
    },
    async loadChildren() {
      try {
        let { periodeAwal, periodeAkhir } = this.filters;
        // Jika salah satu kosong, kita treat sama: reset *keduanya*
        if (!periodeAwal || !periodeAkhir) {
          const now = new Date();
          // Periode akhir = akhir bulan ini
          const endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0);
          // Periode awal = 12 bulan lalu, awal bulan
          const startDate = new Date(now.getFullYear() - 1, now.getMonth() + 1, 1);
          this.filters.periodeAwal = bulan[startDate.getMonth()] + "+" + startDate.getFullYear();
          this.filters.periodeAkhir = bulan[endDate.getMonth()] + "+" + endDate.getFullYear();
          //console.log("Set default periode:", this.filters.periodeAwal, this.filters.periodeAkhir);
        }

        const res = await axios.get(`${baseURL}/api/children`, {
          headers: {
            Accept: 'application/json',
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
          params: this.filters, // 🔹 kirim semua filter ke backend
        })

        // Simpan data status gizi
        this.statusGiziSummary = res.data.status || [];

        const items = res.data.data_anak || [];

        // 🔹 Ambil semua nama posyandu unik
        const allPosyandu = items.flatMap(
          (item) => item.posyandu?.map((p) => p.posyandu).filter(Boolean) || [],
        )
        const uniquePosyandu = [...new Set(allPosyandu)]

        this.posyanduList = uniquePosyandu.map((nama, idx) => ({
          id: idx + 1,
          nama_posyandu: nama,
        }))

        // 🔹 Format data anak
        this.children = items.map((item) => {
          const kelahiran = item.kelahiran?.[0] || {};
          const keluarga = item.keluarga?.[0] || {};
          const pendamping = item.pendampingan?.at(-1) || {};
          const posyandu = item.posyandu?.at(-1) || {};
          // eslint-disable-next-line no-unused-vars
          const lastIntervensi = item.intervensi?.at(-1);

          return {
            id: item.id,
            nama: item.nama || '-',
            nik: item.nik || '-',
            gender: item.jk || '-',
            provinsi: item.provinsi || '-',
            kota: item.kota || '-',
            kecamatan: item.kecamatan || '-',
            kelurahan: item.kelurahan || '-',
            rt: item.rt || '-',
            rw: item.rw || '-',
            posyandu: posyandu.posyandu || '-',
            usia: posyandu.usia || '-',
            tgl_ukur: posyandu.tgl_ukur || '-',
            bbu: posyandu.bbu || '-',
            tbu: posyandu.tbu || '-',
            bbtb: posyandu.bbtb || '-',
            bb: posyandu.bb || '-',
            tb: posyandu.tb || '-',
            bb_naik: posyandu.bb_naik || false,
            intervensi: lastIntervensi
            ? (lastIntervensi.jenis || "-")
            : "Belum mendapatkan intervensi",
            //intervensi: intervensi.intervensi?.length ? item.intervensi.map(i => i.jenis).join(', ') : 'Belum mendapatkan intervensi',
            tmpt_dilahirkan: kelahiran.tmpt_dilahirkan || '-',
            tgl_lahir: kelahiran.tgl_lahir || '-',
            bb_lahir: kelahiran.bb_lahir || '-',
            pb_lahir: kelahiran.pb_lahir || '-',
            persalinan: kelahiran.persalinan || '-',
            nama_ayah: keluarga.nama_ayah || '-',
            nama_ibu: keluarga.nama_ibu || '-',
            pekerjaan_ayah: keluarga.pekerjaan_ayah || '-',
            pekerjaan_ibu: keluarga.pekerjaan_ibu || '-',
            usia_ayah: keluarga.usia_ayah || '-',
            usia_ibu: keluarga.usia_ibu || '-',
            anak_ke: keluarga.anak_ke || '-',
            kader_pendamping: pendamping.kader || '-',
            tgl_pendampingan: pendamping.tanggal || '-',
            raw: {
              kelahiran: item.kelahiran || [],
              keluarga: item.keluarga || [],
              pendampingan: item.pendampingan || [],
              posyandu: item.posyandu || [],
              intervensi: item.intervensi || [],
            },
          }
        })

        //console.log('data: ', this.children);

        // ✅ simpan hasilnya
        this.filteredData = [...this.children]

        // 🔹 (opsional) sekalian update ringkasan status gizi
        await this.hitungStatusGizi()
      } catch (e) {
        console.error(e)

        //this.showError('Error Ambil Data Anak', e);
      }
    },
    async hitungStatusGizi() {
      try {
        // Backend sudah kirim array status
        const status = this.statusGiziSummary || [];

        //console.log("STATUS GIZI:", status);

        // isi status ke komponen
        this.gizi = status;

        // total anak = dari filteredData
        this.totalAnak = this.filteredData.length;

        // kelurahan dari filter (atau API kalo ada)
        this.kelurahan = this.filters.kelurahan || '-';

      } catch (error) {
        console.error('❌ hitungStatusGizi error:', error);
      }
    },
    async applyFilter() {
      this.isLoading = true
      try {
        this.periodeTitle = this.filters.periodeAwal.replace('+', ' ')+ ' - '+this.filters.periodeAkhir.replace('+', ' ')
        await this.loadChildren()
        // await this.hitungStatusGizi()
      }catch(e){
        console.error(e);
      }finally{
        this.isLoading = false
      }
    },
    handlePosyanduChange() {
      const pos = this.filters.posyandu

      if (pos) {
        const filteredChildren = this.children.filter((c) => c.posyandu === pos)

        const rwSet = new Set(filteredChildren.map((c) => c.rw).filter(Boolean))
        const rtSet = new Set(filteredChildren.map((c) => c.rt).filter(Boolean))

        this.rwList = Array.from(rwSet)
        this.rtList = Array.from(rtSet)
        this.rwReadonly = false
        this.rtReadonly = false
      } else {
        // reset kalau posyandu dikosongin
        const rwSet = new Set(this.children.map((c) => c.rw).filter(Boolean))
        const rtSet = new Set(this.children.map((c) => c.rt).filter(Boolean))
        this.rwList = Array.from(rwSet)
        this.rtList = Array.from(rtSet)
        this.rwReadonly = true
        this.rtReadonly = true
        this.filters.rw = ''
        this.filters.rt = ''
      }
    },
    async resetFilter() {
      this.filters = {
        bbu: [],
        tbu: [],
        bbtb: [],
        stagnan: [],
        intervensi: [],
        posyandu: '',
        rw: '',
        rt: '',
        periodeAwal: '',
        periodeAkhir: '',
      }

      this.rwList = []
      this.rtList = []
      this.rwReadonly = true
      this.rtReadonly = true

      this.filteredData = [...this.children]
      await this.loadChildren()
      await this.getWilayahUser()
      //await this.hitungStatusGizi()
    },
    selectAll_bbu() {
      this.filters.bbu = [...this.bbuOptions]
    },
    selectAll_tbu() {
      this.filters.tbu = [...this.tbuOptions]
    },
    selectAll_bbtb() {
      this.filters.bbtb = [...this.bbtbOptions]
    },
    selectAll_stagnan() {
      this.filters.stagnan = [...this.stagnanOptions]
    },
    selectAll_intervensi() {
      this.filters.intervensi = [...this.intervensiOptions]
    },
    clearAll_bbu() {
      this.filters.bbu = []
    },
    clearAll_tbu() {
      this.filters.tbu = []
    },
    clearAll_bbtb() {
      this.filters.bbtb = []
    },
    clearAll_stagnan() {
      this.filters.stagnan = []
    },
    clearAll_intervensi() {
      this.filters.intervensi = []
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
      } catch (error) {
        console.warn('Gagal load config:', error)
        this.logoLoaded = false
      }
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
        this.filters.kelurahan = wilayah.kelurahan
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        //await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
    },
    generatePeriodeOptions() {
      const bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ]

      const now = new Date()
      const result = []

      for (let i = 0; i < 12; i++) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push(label)
      }

      this.periodeOptions = result
    },
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    getTodayDate() {
      const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']
      const bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
      ]
      const now = new Date()
      return `${hari[now.getDay()]}, ${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`
    },
    getThisMonth() {
      const bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember',
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
    async showDetail(props) {
      //console.log('Klik props:', props)
      const nik = props.nik
      const res = await axios.get(`${baseURL}/api/children/${nik}`, {
        headers: {
          Accept: 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        }
      })
      //console.log('detail: ', res.data);

      const prop = res.data
      const raw = prop.raw || {}

      const posyanduList = Array.isArray(raw.posyandu) ? raw.posyandu : []
      const intervensiList = Array.isArray(raw.intervensi) ? raw.intervensi : []
      const pendampinganList = raw.pendampingan
        ? Array.isArray(raw.pendampingan)
          ? raw.pendampingan
          : [raw.pendampingan] // <-- jadikan array
        : []

      const kelahiranList = raw.kelahiran
        ? Array.isArray(raw.kelahiran)
          ? raw.kelahiran
          : [raw.kelahiran] // <-- jadikan array
        : []
      const keluargaList = Array.isArray(raw.keluarga) ? raw.keluarga : []

      const lastPosyandu = posyanduList.length
        ? posyanduList.sort((a, b) => new Date(a.tgl_ukur) - new Date(b.tgl_ukur)).slice(-1)[0]
        : {}

      this.selectedAnak = {
        // --- Identitas Anak ---
        id: props.id,
        nik: props.nik || '-',
        nama: props.nama || '-',
        gender: props.gender || props.jk || '-',
        usia: lastPosyandu.usia || '-',
        alamat: `${props.kelurahan || '-'}, RT ${props.rt || '-'} / RW ${props.rw || '-'}`,
        desa: props.kelurahan || '-',
        kecamatan: props.kecamatan || '-',
        provinsi: props.provinsi || '-',
        kota: props.kota || '-',
        status_gizi: props.bbtb || '-',

        // --- Orang Tua (keluarga[0]) ---
        nama_ayah: keluargaList?.nama_ayah || '-',
        nik_ayah: keluargaList?.nik_ayah || '-',
        nama_ibu: keluargaList?.nama_ibu || '-',
        nik_ibu: keluargaList?.nik_ibu || '-',
        no_telp: keluargaList?.no_telp || '-',

        kelahiran: kelahiranList.length
          ? kelahiranList.map(k => ({
              no_kia: k.no_kia || '-',
              tmpt_dilahirkan: k.tmpt_dilahirkan || '-',
              tgl_lahir: k.tgl_lahir || '-',
              bb: k.bb_lahir || '-',
              pb: k.pb_lahir || '-',
              jenis: k.persalinan || '-',
            }))
          : [],


        // --- Riwayat Penimbangan (Posyandu) ---
        riwayat_penimbangan: posyanduList.map((p) => ({
          tanggal: p.tgl_ukur || '-',
          usia: p.usia || 0,
          bb: p.bb || '-',
          tb: p.tb || '-',
          bbu: p.bbu || '-',
          tbu: p.tbu || '-',
          bbtb: p.bbtb || '-',
          zs_bbu: p.zs_bbu,
          zs_tbu: p.zs_tbu,
          zs_bbtb: p.zs_bbtb,
        })),

        // ambil status terakhir
        status_terakhir: {
          bbu: lastPosyandu.bbu || '-',
          tbu: lastPosyandu.tbu || '-',
          bbtb: lastPosyandu.bbtb || '-',
        },

        // --- Riwayat Intervensi ---
        riwayat_intervensi: intervensiList.map((i) => ({
          tanggal: i.tgl_intervensi || '-',
          kader: i.kader || '-',
          intervensi: i.jenis || '-',
        })),

        // --- Riwayat Pendampingan (TPK) ---
        riwayat_pendampingan: pendampinganList.map((p) => ({
          tgl_pendampingan: p.tanggal || '-',
          petugas: p.kader || '-',
          usia: p.usia || '-',
          rt: props.rt || '-',
          rw: props.rw || '-',
          bb_lahir: kelahiranList?.bb_lahir || '-',
          pb_lahir: kelahiranList?.pb_lahir || '-',
          bb: p.bb || '-',
        })),
      }

      this.$nextTick(() => {
        this.renderKMSChart('bbu')
        this.renderKMSChart('tbu')
        this.renderKMSChart('bbtb')
        const el = document.getElementById('detailSection')
        if (el) {
          el.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          })
        }
      })
    },
    getWHO_SD(type, gender, value, ageInMonths = null) {
      if (!this.whoData) return null;

      //console.log(this.whoData, type, gender, value);
      if (type === 'bbu') {
        // WFA (weight-for-age)
        const rows = this.whoData.wfa.wfa[gender] || [];

        return rows.find(r => r.month === Number(value)) || null;
      }

      if (type === 'tbu') {
        // HFA (height-for-age)
        const rows = this.whoData.hfa.hfa[gender] || [];
        return rows.find(r => r.month === Number(value)) || null;
      }

      if (type === 'bbtb') {

        if (ageInMonths == null) return null;

        let rangeGroup = null;
        let fieldName = null;

        if (ageInMonths <= 24) {
          rangeGroup = this.whoData.wfh.wfh["0-24"][0];
          fieldName = "length";
        } else {
          rangeGroup = this.whoData.wfh.wfh["25-60"][0];
          fieldName = "height";
        }

        if (!rangeGroup) return null;

        const genderKey = gender === "L" ? "L" : "P";
        const rows = rangeGroup[genderKey] || [];

        if (!rows.length) return null;

        const target = Number(value);

        // ambil tinggi/panjang TERDEKAT
        const row = rows.reduce((prev, curr) => {
          if (!prev) return curr;
          return Math.abs(curr[fieldName] - target) <
                Math.abs(prev[fieldName] - target)
            ? curr
            : prev;
        }, null);

        return row;
      }


      return null;
    },
    renderKMSChart(type) {
      const data = this.selectedAnak.riwayat_penimbangan || [];
      if (!data.length) return;

      const gender = this.selectedAnak.gender === "L" ? "L" : "P";

      // label pakai usia untuk bbu & tbu, tinggi (tb) untuk bbtb
      const labels = type === 'bbtb'
        ? data.map(d => d.tb)
        : data.map(d => d.usia);


      const mapper = (field) =>
      data.map(d => {
        const key = type === 'bbtb' ? d.tb : d.usia;
        const age = d.usia; // usia dalam bulan

        return type === 'bbtb'
          ? this.getWHO_SD(type, gender, key, age)?.[field] ?? null
          : this.getWHO_SD(type, gender, key)?.[field] ?? null;
      });

      // WHO curves
      const minus3SD = mapper('sd3neg');
      const minus2SD = mapper('sd2neg');
      const minus1SD = mapper('sd1neg');
      const medianSD = mapper('median');
      const plus1SD  = mapper('sd1');
      const plus2SD  = mapper('sd2');
      const plus3SD  = mapper('sd3');

      // real anak
      const real =
        type === 'bbu'  ? data.map(d => d.bb) :
        type === 'tbu'  ? data.map(d => d.tb) :
        type === 'bbtb' ? data.map(d => d.bb) : [];

      // Hapus chart lama
      if (this.chartInstance[type]) {
        this.chartInstance[type].destroy();
      }

      const ctx = this.$refs[`chart_${type}`].getContext('2d');

      this.chartInstance[type] = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: '-3 SD',
              data: minus3SD,
              borderColor: '#555',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: '-2 SD',
              data: minus2SD,
              borderColor: '#cc0000',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: '-1 SD',
              data: minus1SD,
              borderColor: '#444',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: '0 SD',
              data: medianSD,
              borderColor: '#4caf50',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: '1 SD',
              data: plus1SD,
              borderColor: '#ffa500',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: '2 SD',
              data: plus2SD,
              borderColor: '#111',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: '3 SD',
              data: plus3SD,
              borderColor: '#777',
              borderWidth: 2,
              pointRadius: 0,
            },
            {
              label: type === 'bbu' ? 'BB/U' : type === 'tbu' ? 'TB/U' : 'BB/TB',
              data: real,
              borderColor: '#0066ff',
              backgroundColor: '#0066ff',
              borderWidth: 3,
              pointRadius: 5,
              tension: 0.3
            }
          ]
        },
        options: {
        responsive: true,
        maintainAspectRatio: false,

        plugins: {
          legend: {
            position: 'top',
            labels: { usePointStyle: true }
          },
          datalabels: { display: false },
        },

        scales: {
          x: {
            title: {
              display: true,
              text: type === 'bbtb' ? 'Tinggi (cm)' : 'Usia (bulan)',
              font: { style: 'italic' }
            },

            // --- WHO Style Grid ----
            grid: {
              display: true,
              color: '#ccc',
              lineWidth: context => {
                const index = context.tick.index;
                return index % 2 === 0 ? 1.8 : 0.7;    // garis tebal tiap 2 label
              },
              borderDash: context => {
                const index = context.tick.index;
                return index % 2 === 0 ? [] : [4, 4];  // garis putus-putus WHO style
              }
            }
          },

          y: {
            title: {
              display: true,
              text: type === 'tbu' ? 'Tinggi (cm)' : 'Berat (kg)',
              font: { style: 'italic' }
            },

            // --- WHO Style Grid ----
            grid: {
              display: true,
              color: '#ccc',
              lineWidth: context => {
                const value = context.tick.value;
                return value % 1 === 0 ? 1.5 : 0.6;    // garis tebal tiap 1 kg / cm
              },
              borderDash: context => {
                const value = context.tick.value;
                return value % 1 === 0 ? [] : [4, 4];
              }
            }
          }
        }
      }

      });
    }

  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    if (storedEmail) {
      let namePart = storedEmail.split('@')[0]
      namePart = namePart.replace(/[._]/g, ' ')
      this.username = namePart
        .split(' ')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ')
    } else {
      this.username = 'User'
    }
    this.today = this.getTodayDate()
    this.thisMonth = this.getThisMonth()
  },
  computed: {
    // eslint-disable-next-line vue/return-in-computed-property
    periodeLabel() {
      // DEFAULT → pakai bulan berjalan
      const defaultLabel = new Date().toLocaleString('id-ID', {
        month: 'long',
        year: 'numeric'
      })

      // Jika user belum pilih periode → gunakan default
      if (!this.filters.periodeAwal || !this.filters.periodeAwal.includes('-')) {
        return defaultLabel
      }

      // Parse "YYYY-MM"
      const [year, month] = this.filters.periodeAwal.split('-')
      const date = new Date(Number(year), Number(month) - 1, 1)

      // Return label format Indonesia
      return date.toLocaleString('id-ID', {
        month: 'long',
        year: 'numeric'
      })
    },
    underDisplayText() {
      const total = this.filters.bbu.length
      const allSelected = total === this.bbuOptions.length

      if (allSelected) return 'All'
      if (total === 1) return this.filters.bbu[0]
      return `${total} Selected`
    },
    StuntingDisplayText() {
      const total = this.filters.tbu.length
      const allSelected = total === this.tbuOptions.length

      if (allSelected) return 'All'
      if (total === 1) return this.filters.tbu[0]
      return `${total} Selected`
    },
    WastingDisplayText() {
      const total = this.filters.bbtb.length
      const allSelected = total === this.bbtbOptions.length

      if (allSelected) return 'All'
      if (total === 1) return this.filters.bbtb[0]
      return `${total} Selected`
    },
    stagnanDisplayText() {
      const total = this.filters.stagnan.length
      const allSelected = total === this.stagnanOptions.length

      if (allSelected) return 'All'
      if (total === 1) return this.filters.stagnan[0]
      return `${total} Selected`
    },
    intervesiDisplayText() {
      const total = this.filters.intervensi.length
      const allSelected = total === this.intervensiOptions.length

      if (allSelected) return 'All'
      if (total === 1) return this.filters.intervensi[0]
      return `${total} Selected`
    },
  },
  async mounted() {
    this.isLoading = true
    try {
      await this.getWilayahUser()
      await this.loadConfigWithCache()
      this.generatePeriodeOptions()


      // 🔥 Load children dulu
      await this.loadChildren()

      // Copy semua children
      this.filteredData = [...this.children]

      const q = this.$route.query
      const { tipe, status } = q

      // 1. Mapping FILTER STATUS
      if (tipe && status) {
        this.filters.bbu = []
        this.filters.tbu = []
        this.filters.bbtb = []

        if (tipe === 'BB/U') this.filters.bbu = [status]
        else if (tipe === 'TB/U') this.filters.tbu = [status]
        else if (tipe === 'BB/TB') this.filters.bbtb = [status]
      }

      // 2. Mapping wilayah
      this.filters.posyandu = q.posyandu || ''
      this.filters.rw = q.rw || ''
      this.filters.rt = q.rt || ''

      // 3. PERIODE (format chart = 2025-03 → ubah ke "Maret 2025")
      if (q.periode) {
        const [year, month] = q.periode.split('-')

        const bulanNama = [
          "Januari", "Februari", "Maret", "April", "Mei", "Juni",
          "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ]

        const nama = bulanNama[parseInt(month) - 1]

        // Format harus sama dengan periodeOptions
        const formatted = `${nama} ${year}`

        this.filters.periodeAwal = formatted
        this.filters.periodeAkhir = formatted
      }

      await this.$nextTick()
      this.applyFilter && this.applyFilter()

      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('Error mounted:', err)
    } finally {
      this.isLoading = false
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
  },
  watch: {
    'filters.posyandu'(val) {
      this.handlePosyanduChange(val)
    },
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Neuton:wght@400;700&display=swap');
/* ============================= */
/* DEFAULT UNTUK LAYAR ≥ 1300px */
/* ============================= */
.form-control, /* input, textarea */
.form-select,
button {
  font-size: 0.9rem;
}

* {
  word-wrap: break-word;
  white-space: normal;
}

label {
  font-size: 0.9rem; /* label proporsional */
}
.dropdown-menu .form-check-label {
  white-space: normal !important;
  word-break: break-word;
}

.collapse-enter-active,
.collapse-leave-active {
  transition: all 0.3s ease;
  overflow: hidden;
}

.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
}

.collapse-enter-to,
.collapse-leave-from {
  max-height: 1000px; /* cukup besar agar muat konten */
  opacity: 1;
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

.ringkasan-header,
.table-name {
  font-family: 'Neuton', serif;
  font-size: 24px;
  font-weight: bold;
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
.dropzone-full {
  cursor: pointer;
  transition:
    background-color 0.15s ease,
    border-color 0.15s ease;
  min-height: 120px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.dropzone-full.border-primary {
  border-width: 2px !important;
}

.dropzone-full .bi {
  opacity: 0.9;
}

/* state ketika drag */
.dropzone-full.bg-light {
  background-color: #f8fafc !important;
}

/* optional: fokus keyboard */
.dropzone-full:focus {
  outline: none;
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12);
}

.border-violet {
  border-color: #4f0891 !important;
} /* Overweight - Hitam */
.text-violet {
  color: #4f0891 !important;
}

/* === Warna garis sesuai item.color === */
.stroke-danger polyline {
  stroke: #dc3545;
}
.stroke-warning polyline {
  stroke: #ffc107;
}
.stroke-success polyline {
  stroke: #198754;
}
.stroke-info polyline {
  stroke: #0dcaf0;
}
.stroke-dark polyline {
  stroke: #343a40;
}
.stroke-violet polyline {
  stroke: #4f0891;
}
/* Hover efek */
.card:hover .svg-line polyline {
  opacity: 1;
  stroke-width: 3;
  transition: all 0.3s ease;
}

.tab-pane p {
  font-size: 13px;
}

.tab-pane-sub-title {
  font-size: 17px;
  font-weight: 600;
}

@media (min-width: 992px) {
  .custom-20 {
    width: 20% !important;
    flex: 0 0 20% !important;
    max-width: 20% !important;
  }
}

/* Dropdown full width */
.dropdown,
.dropdown > button.form-select {
  width: 100% !important;
  display: block !important;
}

.dropdown-menu {
  width: 100% !important;
}

/* Truncate */
.selected-text,
.form-select.text-truncate {
  display: block;
  width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-item label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Tombol floating filter (mobile only) */
.filter-float-btn {
  position: fixed;
  bottom: 15px;
  left: 15px;
  z-index: 2000;
  display: none;
}

@media (max-width: 768px) {
  .filter-float-btn {
    display: block;
  }

  /* Panel filter mobile */
  .filter-mobile-panel {
    position: fixed;
    left: 0;
    right: 0;
    bottom: -100%;
    height: 85%;
    background: #fff;
    border-radius: 16px 16px 0 0;
    box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.15);
    z-index: 2001;
    padding: 15px;
    overflow-y: auto;
    transition: bottom 0.35s ease;
  }

  .filter-mobile-panel.open {
    bottom: 0;
  }
}

.custom-card-size {
  height: 109px !important;
  width: 98% !important;
  margin-left: 0 !important;
  margin-right: 0 !important;
}

.no-col-padding {
  padding-left: 0 !important;
  padding-right: 0 !important;
}

.table-responsive {
    /* Ini penting: mengatur konteks penumpukan (stacking context) */
    overflow-x: auto;
}

/* Kolom yang ingin dibekukan (kolom Nama) */
.frozen-column {
    /* 1. Kunci posisi pada 0 dari kiri */
    position: sticky !important; /* Gunakan !important untuk memastikan override */
    left: 0;

    /* 2. Pastikan kolom berada di lapisan terdepan saat di-scroll */
    z-index: 10;

    /* 3. Atur latar belakang agar kolom di belakangnya tidak terlihat */
    /* Warna Putih untuk baris data biasa */
    background-color: #fff !important;
}

/* Khusus untuk Header (<thead>) */
.table-responsive thead .frozen-column {
    /* Warna Hijau Terang/Success untuk thead Anda */
    background-color: #d1e7dd !important;

    /* Beri z-index lebih tinggi agar header tetap di atas baris data saat scroll vertikal (jika ada) */
    z-index: 11 !important;
}

/* ===== Pagination Responsive All Devices ===== */

/* MOBILE SMALL (≤576px) */
@media (max-width: 576px) {
  .pagination {
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 4px;
    padding-bottom: 6px;
  }

  .pagination .page-link {
    padding: 4px 8px;
    font-size: 0.65rem;
    min-width: 30px;
  }

  .pagination .page-item {
    flex-shrink: 0;
  }
}

/* TABLET (576px–768px) */
@media (min-width: 577px) and (max-width: 768px) {
  .pagination {
    flex-wrap: wrap;
    gap: 6px;
  }

  .pagination .page-link {
    padding: 6px 12px;
    font-size: 0.65rem;
  }
}

/* SMALL DESKTOP (768px–1200px) */
@media (min-width: 769px) and (max-width: 1200px) {
  .pagination {
    gap: 8px;
  }

  .pagination .page-link {
    padding: 7px 14px;
    font-size: 0.65rem;
  }
}

/* LARGE DESKTOP (≥1200px) */
@media (min-width: 1201px) {
  .pagination .page-link {
    padding: 8px 16px;
    font-size: 0.65rem;
  }
}

.th-font{
  font-size: 16px;
}
.td-font{
  font-size: 14px;
}
</style>
