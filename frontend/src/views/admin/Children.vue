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
                Laporan Status Gizi Desa {{ this.kelurahan }} Periode {{ this.filters.periodeAwal.replace('+', ' ') }} - {{ this.filters.periodeAkhir.replace('+', ' ') }}
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
              <div class="col-md-12 mt-4" v-if="selectedAnak">
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
                                      <h6 class="mb-2">BB/U (0–60 bln)</h6>
                                      <div style="height: 200px;">
                                        <canvas ref="chartBB"></canvas>
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
                                      <h6 class="mb-2">TB/U (0–60 bln)</h6>
                                      <div style="height: 200px;">
                                        <canvas ref="chartTB"></canvas>
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
                                      <h6 class="mb-2">BB/TB</h6>
                                      <div style="height: 200px;">
                                        <canvas ref="chartBBTB"></canvas>
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
      chartBBTB: null,
      chartBB: null,
      chartTB: null,
      //ageMonths: 24,
      //currentWeight: 12,
      //currentHeight: 87,
      //gender: 'male',
      kmsColors: {
        top: '#F2D803',
        midTop: '#84BA24',
        mid: '#2CA339',
        midMed: '#2DA83C',
        midBottom: '#80B626',
        bottom: '#DCBF1E',
      },
      wfa:{
        "L": [
          { "month": 0, "sd3neg": 2.1, "sd2neg": 2.5, "sd1neg": 2.9, "median": 3.3, "sd1": 3.9, "sd2": 4.4, "sd3": 5.0 },
          { "month": 1, "sd3neg": 2.9, "sd2neg": 3.4, "sd1neg": 3.9, "median": 4.5, "sd1": 5.1, "sd2": 5.8, "sd3": 6.6 },
          { "month": 2, "sd3neg": 3.8, "sd2neg": 4.3, "sd1neg": 4.9, "median": 5.6, "sd1": 6.4, "sd2": 7.2, "sd3": 8.0 },
          { "month": 3, "sd3neg": 4.4, "sd2neg": 5.0, "sd1neg": 5.7, "median": 6.4, "sd1": 7.2, "sd2": 8.0, "sd3": 8.9 },
          { "month": 4, "sd3neg": 4.9, "sd2neg": 5.6, "sd1neg": 6.2, "median": 7.0, "sd1": 7.8, "sd2": 8.7, "sd3": 9.6 },
          { "month": 5, "sd3neg": 5.3, "sd2neg": 6.0, "sd1neg": 6.7, "median": 7.5, "sd1": 8.4, "sd2": 9.2, "sd3": 10.4 },
          { "month": 6, "sd3neg": 5.7, "sd2neg": 6.4, "sd1neg": 7.1, "median": 7.9, "sd1": 8.8, "sd2": 9.7, "sd3": 10.9 },
          { "month": 7, "sd3neg": 5.9, "sd2neg": 6.7, "sd1neg": 7.4, "median": 8.3, "sd1": 9.2, "sd2": 10.2, "sd3": 11.4 },
          { "month": 8, "sd3neg": 6.2, "sd2neg": 6.9, "sd1neg": 7.7, "median": 8.6, "sd1": 9.6, "sd2": 10.5, "sd3": 11.9 },
          { "month": 9, "sd3neg": 6.4, "sd2neg": 7.1, "sd1neg": 8.0, "median": 8.9, "sd1": 9.9, "sd2": 10.9, "sd3": 12.3 },
          { "month": 10, "sd3neg": 6.6, "sd2neg": 7.4, "sd1neg": 8.2, "median": 9.2, "sd1": 10.2, "sd2": 11.2, "sd3": 12.7 },
          { "month": 11, "sd3neg": 6.8, "sd2neg": 7.6, "sd1neg": 8.4, "median": 9.4, "sd1": 10.5, "sd2": 11.5, "sd3": 13.0 },

          { "month": 12, "sd3neg": 6.9, "sd2neg": 7.7, "sd1neg": 8.6, "median": 9.6, "sd1": 10.8, "sd2": 11.8, "sd3": 13.3 },
          { "month": 13, "sd3neg": 7.1, "sd2neg": 7.9, "sd1neg": 8.8, "median": 9.9, "sd1": 11.0, "sd2": 12.1, "sd3": 13.7 },
          { "month": 14, "sd3neg": 7.2, "sd2neg": 8.1, "sd1neg": 9.0, "median": 10.1, "sd1": 11.3, "sd2": 12.4, "sd3": 14.0 },
          { "month": 15, "sd3neg": 7.4, "sd2neg": 8.3, "sd1neg": 9.2, "median": 10.3, "sd1": 11.5, "sd2": 12.7, "sd3": 14.3 },
          { "month": 16, "sd3neg": 7.5, "sd2neg": 8.4, "sd1neg": 9.4, "median": 10.5, "sd1": 11.7, "sd2": 12.9, "sd3": 14.6 },
          { "month": 17, "sd3neg": 7.7, "sd2neg": 8.6, "sd1neg": 9.5, "median": 10.7, "sd1": 11.9, "sd2": 13.2, "sd3": 14.9 },

          { "month": 18, "sd3neg": 7.8, "sd2neg": 8.7, "sd1neg": 9.7, "median": 10.9, "sd1": 12.1, "sd2": 13.4, "sd3": 15.2 },
          { "month": 19, "sd3neg": 7.9, "sd2neg": 8.9, "sd1neg": 9.8, "median": 11.1, "sd1": 12.3, "sd2": 13.6, "sd3": 15.5 },
          { "month": 20, "sd3neg": 8.1, "sd2neg": 9.0, "sd1neg": 10.0, "median": 11.3, "sd1": 12.5, "sd2": 13.8, "sd3": 15.8 },

          { "month": 21, "sd3neg": 8.2, "sd2neg": 9.2, "sd1neg": 10.1, "median": 11.5, "sd1": 12.7, "sd2": 14.0, "sd3": 16.1 },
          { "month": 22, "sd3neg": 8.3, "sd2neg": 9.3, "sd1neg": 10.3, "median": 11.7, "sd1": 12.9, "sd2": 14.3, "sd3": 16.4 },
          { "month": 23, "sd3neg": 8.4, "sd2neg": 9.4, "sd1neg": 10.4, "median": 11.9, "sd1": 13.1, "sd2": 14.5, "sd3": 16.7 },

          { "month": 24, "sd3neg": 8.5, "sd2neg": 9.5, "sd1neg": 10.5, "median": 12.1, "sd1": 13.3, "sd2": 14.7, "sd3": 17.0 }
        ],
        "P": [
          { "month": 0,  "sd3neg": 2.0, "sd2neg": 2.4, "sd1neg": 2.8, "median": 3.2, "sd1": 3.7, "sd2": 4.2, "sd3": 4.8 },
          { "month": 1,  "sd3neg": 2.7, "sd2neg": 3.2, "sd1neg": 3.6, "median": 4.2, "sd1": 4.8, "sd2": 5.4, "sd3": 6.1 },
          { "month": 2,  "sd3neg": 3.4, "sd2neg": 3.9, "sd1neg": 4.5, "median": 5.1, "sd1": 5.8, "sd2": 6.6, "sd3": 7.3 },
          { "month": 3,  "sd3neg": 4.0, "sd2neg": 4.5, "sd1neg": 5.1, "median": 5.8, "sd1": 6.6, "sd2": 7.5, "sd3": 8.3 },
          { "month": 4,  "sd3neg": 4.4, "sd2neg": 5.0, "sd1neg": 5.6, "median": 6.4, "sd1": 7.3, "sd2": 8.2, "sd3": 9.1 },
          { "month": 5,  "sd3neg": 4.8, "sd2neg": 5.4, "sd1neg": 6.0, "median": 6.9, "sd1": 7.8, "sd2": 8.8, "sd3": 9.8 },
          { "month": 6,  "sd3neg": 5.1, "sd2neg": 5.7, "sd1neg": 6.4, "median": 7.3, "sd1": 8.2, "sd2": 9.3, "sd3": 10.4 },
          { "month": 7,  "sd3neg": 5.3, "sd2neg": 6.0, "sd1neg": 6.7, "median": 7.6, "sd1": 8.6, "sd2": 9.8, "sd3": 10.9 },
          { "month": 8,  "sd3neg": 5.6, "sd2neg": 6.3, "sd1neg": 7.0, "median": 8.0, "sd1": 9.0, "sd2": 10.2, "sd3": 11.4 },
          { "month": 9,  "sd3neg": 5.8, "sd2neg": 6.5, "sd1neg": 7.3, "median": 8.3, "sd1": 9.3, "sd2": 10.6, "sd3": 11.9 },
          { "month": 10, "sd3neg": 5.9, "sd2neg": 6.7, "sd1neg": 7.5, "median": 8.6, "sd1": 9.6, "sd2": 10.9, "sd3": 12.3 },
          { "month": 11, "sd3neg": 6.1, "sd2neg": 6.9, "sd1neg": 7.7, "median": 8.9, "sd1": 9.9, "sd2": 11.3, "sd3": 12.7 },

          { "month": 12, "sd3neg": 6.3, "sd2neg": 7.0, "sd1neg": 7.9, "median": 9.2, "sd1": 10.2, "sd2": 11.6, "sd3": 13.0 },
          { "month": 13, "sd3neg": 6.4, "sd2neg": 7.2, "sd1neg": 8.1, "median": 9.4, "sd1": 10.5, "sd2": 11.9, "sd3": 13.4 },
          { "month": 14, "sd3neg": 6.6, "sd2neg": 7.4, "sd1neg": 8.3, "median": 9.7, "sd1": 10.7, "sd2": 12.2, "sd3": 13.7 },
          { "month": 15, "sd3neg": 6.7, "sd2neg": 7.6, "sd1neg": 8.5, "median": 9.9, "sd1": 11.0, "sd2": 12.5, "sd3": 14.0 },
          { "month": 16, "sd3neg": 6.9, "sd2neg": 7.7, "sd1neg": 8.7, "median": 10.1, "sd1": 11.2, "sd2": 12.7, "sd3": 14.3 },
          { "month": 17, "sd3neg": 7.0, "sd2neg": 7.9, "sd1neg": 8.8, "median": 10.3, "sd1": 11.4, "sd2": 13.0, "sd3": 14.6 },

          { "month": 18, "sd3neg": 7.1, "sd2neg": 8.1, "sd1neg": 9.0, "median": 10.5, "sd1": 11.7, "sd2": 13.3, "sd3": 14.9 },
          { "month": 19, "sd3neg": 7.3, "sd2neg": 8.2, "sd1neg": 9.2, "median": 10.7, "sd1": 11.9, "sd2": 13.5, "sd3": 15.2 },
          { "month": 20, "sd3neg": 7.4, "sd2neg": 8.4, "sd1neg": 9.3, "median": 10.9, "sd1": 12.1, "sd2": 13.7, "sd3": 15.5 },

          { "month": 21, "sd3neg": 7.5, "sd2neg": 8.5, "sd1neg": 9.5, "median": 11.1, "sd1": 12.3, "sd2": 14.0, "sd3": 15.7 },
          { "month": 22, "sd3neg": 7.6, "sd2neg": 8.7, "sd1neg": 9.7, "median": 11.3, "sd1": 12.5, "sd2": 14.2, "sd3": 16.0 },
          { "month": 23, "sd3neg": 7.8, "sd2neg": 8.8, "sd1neg": 9.8, "median": 11.5, "sd1": 12.7, "sd2": 14.5, "sd3": 16.3 },

          { "month": 24, "sd3neg": 7.9, "sd2neg": 8.9, "sd1neg": 10.0, "median": 11.7, "sd1": 12.9, "sd2": 14.7, "sd3": 16.6 },
          { "month": 25, "sd3neg": 8.0, "sd2neg": 9.1, "sd1neg": 10.1, "median": 11.9, "sd1": 13.1, "sd2": 14.9, "sd3": 16.8 },
          { "month": 26, "sd3neg": 8.1, "sd2neg": 9.2, "sd1neg": 10.3, "median": 12.0, "sd1": 13.3, "sd2": 15.2, "sd3": 17.1 },
          { "month": 27, "sd3neg": 8.2, "sd2neg": 9.3, "sd1neg": 10.4, "median": 12.2, "sd1": 13.5, "sd2": 15.4, "sd3": 17.3 },
          { "month": 28, "sd3neg": 8.3, "sd2neg": 9.4, "sd1neg": 10.6, "median": 12.4, "sd1": 13.7, "sd2": 15.6, "sd3": 17.6 },
          { "month": 29, "sd3neg": 8.4, "sd2neg": 9.6, "sd1neg": 10.7, "median": 12.5, "sd1": 13.9, "sd2": 15.8, "sd3": 17.8 },

          { "month": 30, "sd3neg": 8.5, "sd2neg": 9.7, "sd1neg": 10.8, "median": 12.7, "sd1": 14.1, "sd2": 16.0, "sd3": 18.1 },
          { "month": 31, "sd3neg": 8.6, "sd2neg": 9.8, "sd1neg": 11.0, "median": 12.9, "sd1": 14.3, "sd2": 16.3, "sd3": 18.4 },
          { "month": 32, "sd3neg": 8.7, "sd2neg": 9.9, "sd1neg": 11.1, "median": 13.0, "sd1": 14.5, "sd2": 16.5, "sd3": 18.6 },
          { "month": 33, "sd3neg": 8.8, "sd2neg": 10.0, "sd1neg": 11.3, "median": 13.2, "sd1": 14.7, "sd2": 16.7, "sd3": 18.9 },
          { "month": 34, "sd3neg": 8.9, "sd2neg": 10.1, "sd1neg": 11.4, "median": 13.4, "sd1": 14.9, "sd2": 16.9, "sd3": 19.1 },
          { "month": 35, "sd3neg": 9.0, "sd2neg": 10.3, "sd1neg": 11.6, "median": 13.5, "sd1": 15.1, "sd2": 17.1, "sd3": 19.4 },

          { "month": 36, "sd3neg": 9.1, "sd2neg": 10.4, "sd1neg": 11.7, "median": 13.7, "sd1": 15.3, "sd2": 17.3, "sd3": 19.6 },
          { "month": 37, "sd3neg": 9.2, "sd2neg": 10.5, "sd1neg": 11.8, "median": 13.9, "sd1": 15.5, "sd2": 17.5, "sd3": 19.9 },
          { "month": 38, "sd3neg": 9.3, "sd2neg": 10.6, "sd1neg": 12.0, "median": 14.0, "sd1": 15.7, "sd2": 17.7, "sd3": 20.1 },
          { "month": 39, "sd3neg": 9.4, "sd2neg": 10.7, "sd1neg": 12.1, "median": 14.2, "sd1": 15.9, "sd2": 17.9, "sd3": 20.4 },

          { "month": 40, "sd3neg": 9.5, "sd2neg": 10.8, "sd1neg": 12.2, "median": 14.3, "sd1": 16.1, "sd2": 18.1, "sd3": 20.6 },
          { "month": 41, "sd3neg": 9.6, "sd2neg": 10.9, "sd1neg": 12.4, "median": 14.5, "sd1": 16.3, "sd2": 18.3, "sd3": 20.9 },
          { "month": 42, "sd3neg": 9.7, "sd2neg": 11.0, "sd1neg": 12.5, "median": 14.7, "sd1": 16.5, "sd2": 18.5, "sd3": 21.1 },
          { "month": 43, "sd3neg": 9.8, "sd2neg": 11.2, "sd1neg": 12.6, "median": 14.8, "sd1": 16.7, "sd2": 18.7, "sd3": 21.4 },
          { "month": 44, "sd3neg": 9.9, "sd2neg": 11.3, "sd1neg": 12.8, "median": 15.0, "sd1": 16.9, "sd2": 18.9, "sd3": 21.6 },
          { "month": 45, "sd3neg": 10.0, "sd2neg": 11.4, "sd1neg": 12.9, "median": 15.2, "sd1": 17.1, "sd2": 19.1, "sd3": 21.9 },

          { "month": 46, "sd3neg": 10.1, "sd2neg": 11.5, "sd1neg": 13.0, "median": 15.3, "sd1": 17.3, "sd2": 19.3, "sd3": 22.1 },
          { "month": 47, "sd3neg": 10.2, "sd2neg": 11.6, "sd1neg": 13.2, "median": 15.5, "sd1": 17.5, "sd2": 19.5, "sd3": 22.4 },
          { "month": 48, "sd3neg": 10.3, "sd2neg": 11.7, "sd1neg": 13.3, "median": 15.7, "sd1": 17.7, "sd2": 19.7, "sd3": 22.6 },
          { "month": 49, "sd3neg": 10.4, "sd2neg": 11.8, "sd1neg": 13.4, "median": 15.8, "sd1": 17.9, "sd2": 19.9, "sd3": 22.9 },

          { "month": 50, "sd3neg": 10.5, "sd2neg": 11.9, "sd1neg": 13.6, "median": 16.0, "sd1": 18.1, "sd2": 20.1, "sd3": 23.1 },
          { "month": 51, "sd3neg": 10.6, "sd2neg": 12.0, "sd1neg": 13.7, "median": 16.2, "sd1": 18.3, "sd2": 20.3, "sd3": 23.4 },
          { "month": 52, "sd3neg": 10.7, "sd2neg": 12.1, "sd1neg": 13.8, "median": 16.3, "sd1": 18.5, "sd2": 20.5, "sd3": 23.6 },
          { "month": 53, "sd3neg": 10.8, "sd2neg": 12.2, "sd1neg": 14.0, "median": 16.5, "sd1": 18.7, "sd2": 20.7, "sd3": 23.9 },
          { "month": 54, "sd3neg": 10.9, "sd2neg": 12.3, "sd1neg": 14.1, "median": 16.7, "sd1": 18.9, "sd2": 20.9, "sd3": 24.1 },
          { "month": 55, "sd3neg": 11.0, "sd2neg": 12.4, "sd1neg": 14.2, "median": 16.8, "sd1": 19.1, "sd2": 21.1, "sd3": 24.4 },

          { "month": 56, "sd3neg": 11.1, "sd2neg": 12.5, "sd1neg": 14.3, "median": 17.0, "sd1": 19.3, "sd2": 21.3, "sd3": 24.6 },
          { "month": 57, "sd3neg": 11.2, "sd2neg": 12.6, "sd1neg": 14.5, "median": 17.2, "sd1": 19.5, "sd2": 21.5, "sd3": 24.9 },
          { "month": 58, "sd3neg": 11.3, "sd2neg": 12.7, "sd1neg": 14.6, "median": 17.3, "sd1": 19.7, "sd2": 21.7, "sd3": 25.1 },
          { "month": 59, "sd3neg": 11.4, "sd2neg": 12.8, "sd1neg": 14.7, "median": 17.5, "sd1": 19.9, "sd2": 21.9, "sd3": 25.4 },
          { "month": 60, "sd3neg": 11.5, "sd2neg": 12.9, "sd1neg": 14.8, "median": 17.7, "sd1": 20.1, "sd2": 22.1, "sd3": 25.6 }
        ]
      },
      hfa:{
        "L": [
          { "month": 0,  "sd3neg": 44.2, "sd2neg": 46.1, "sd1neg": 48.0, "median": 49.9, "sd1": 51.8, "sd2": 53.7, "sd3": 55.6 },
          { "month": 1,  "sd3neg": 48.9, "sd2neg": 50.8, "sd1neg": 52.8, "median": 54.7, "sd1": 56.7, "sd2": 58.6, "sd3": 60.6 },
          { "month": 2,  "sd3neg": 52.4, "sd2neg": 54.4, "sd1neg": 56.4, "median": 58.4, "sd1": 60.4, "sd2": 62.5, "sd3": 64.5 },
          { "month": 3,  "sd3neg": 55.3, "sd2neg": 57.3, "sd1neg": 59.3, "median": 61.4, "sd1": 63.5, "sd2": 65.5, "sd3": 67.6 },
          { "month": 4,  "sd3neg": 57.6, "sd2neg": 59.7, "sd1neg": 61.8, "median": 64.0, "sd1": 66.2, "sd2": 68.3, "sd3": 70.5 },
          { "month": 5,  "sd3neg": 59.6, "sd2neg": 61.7, "sd1neg": 63.9, "median": 66.0, "sd1": 68.1, "sd2": 70.3, "sd3": 72.5 },
          { "month": 6,  "sd3neg": 61.2, "sd2neg": 63.3, "sd1neg": 65.6, "median": 67.6, "sd1": 69.9, "sd2": 72.2, "sd3": 74.4 },
          { "month": 7,  "sd3neg": 62.7, "sd2neg": 64.8, "sd1neg": 67.1, "median": 69.2, "sd1": 71.3, "sd2": 73.7, "sd3": 75.9 },
          { "month": 8,  "sd3neg": 64.0, "sd2neg": 66.2, "sd1neg": 68.5, "median": 70.6, "sd1": 72.8, "sd2": 75.2, "sd3": 77.5 },
          { "month": 9,  "sd3neg": 65.2, "sd2neg": 67.5, "sd1neg": 69.7, "median": 72.0, "sd1": 74.1, "sd2": 76.5, "sd3": 78.9 },
          { "month": 10, "sd3neg": 66.4, "sd2neg": 68.7, "sd1neg": 71.0, "median": 73.3, "sd1": 75.6, "sd2": 78.0, "sd3": 80.3 },
          { "month": 11, "sd3neg": 67.6, "sd2neg": 69.9, "sd1neg": 72.1, "median": 74.5, "sd1": 76.9, "sd2": 79.3, "sd3": 81.7 },

          { "month": 12, "sd3neg": 68.6, "sd2neg": 71.0, "sd1neg": 73.3, "median": 75.7, "sd1": 78.1, "sd2": 80.6, "sd3": 83.0 },
          { "month": 13, "sd3neg": 69.6, "sd2neg": 72.0, "sd1neg": 74.4, "median": 76.7, "sd1": 79.3, "sd2": 81.8, "sd3": 84.2 },
          { "month": 14, "sd3neg": 70.6, "sd2neg": 73.0, "sd1neg": 75.4, "median": 77.8, "sd1": 80.4, "sd2": 83.0, "sd3": 85.4 },
          { "month": 15, "sd3neg": 71.6, "sd2neg": 74.0, "sd1neg": 76.4, "median": 78.8, "sd1": 81.4, "sd2": 84.2, "sd3": 86.5 },
          { "month": 16, "sd3neg": 72.5, "sd2neg": 75.0, "sd1neg": 77.4, "median": 79.7, "sd1": 82.4, "sd2": 85.2, "sd3": 87.6 },
          { "month": 17, "sd3neg": 73.3, "sd2neg": 75.8, "sd1neg": 78.3, "median": 80.6, "sd1": 83.3, "sd2": 86.2, "sd3": 88.6 },

          { "month": 18, "sd3neg": 74.2, "sd2neg": 76.7, "sd1neg": 79.2, "median": 81.5, "sd1": 84.2, "sd2": 87.1, "sd3": 89.6 },
          { "month": 19, "sd3neg": 75.0, "sd2neg": 77.5, "sd1neg": 80.1, "median": 82.3, "sd1": 85.0, "sd2": 88.0, "sd3": 90.5 },
          { "month": 20, "sd3neg": 75.8, "sd2neg": 78.3, "sd1neg": 80.9, "median": 83.2, "sd1": 85.8, "sd2": 88.8, "sd3": 91.4 },

          { "month": 21, "sd3neg": 76.5, "sd2neg": 79.1, "sd1neg": 81.7, "median": 84.0, "sd1": 86.6, "sd2": 89.6, "sd3": 92.3 },
          { "month": 22, "sd3neg": 77.2, "sd2neg": 79.8, "sd1neg": 82.5, "median": 84.7, "sd1": 87.4, "sd2": 90.4, "sd3": 93.1 },
          { "month": 23, "sd3neg": 77.9, "sd2neg": 80.5, "sd1neg": 83.2, "median": 85.5, "sd1": 88.1, "sd2": 91.2, "sd3": 94.0 },

          { "month": 24, "sd3neg": 78.6, "sd2neg": 81.2, "sd1neg": 84.0, "median": 86.2, "sd1": 88.8, "sd2": 92.0, "sd3": 94.8 },
          { "month": 25, "sd3neg": 79.3, "sd2neg": 81.9, "sd1neg": 84.7, "median": 86.9, "sd1": 89.6, "sd2": 92.7, "sd3": 95.6 },
          { "month": 26, "sd3neg": 79.9, "sd2neg": 82.6, "sd1neg": 85.4, "median": 87.6, "sd1": 90.3, "sd2": 93.5, "sd3": 96.4 },
          { "month": 27, "sd3neg": 80.5, "sd2neg": 83.2, "sd1neg": 86.1, "median": 88.3, "sd1": 91.0, "sd2": 94.2, "sd3": 97.1 },
          { "month": 28, "sd3neg": 81.1, "sd2neg": 83.8, "sd1neg": 86.7, "median": 88.9, "sd1": 91.7, "sd2": 94.9, "sd3": 97.9 },
          { "month": 29, "sd3neg": 81.7, "sd2neg": 84.4, "sd1neg": 87.4, "median": 89.6, "sd1": 92.4, "sd2": 95.7, "sd3": 98.6 },

          { "month": 30, "sd3neg": 82.2, "sd2neg": 85.0, "sd1neg": 88.0, "median": 90.2, "sd1": 93.1, "sd2": 96.4, "sd3": 99.4 },
          { "month": 31, "sd3neg": 82.8, "sd2neg": 85.6, "sd1neg": 88.7, "median": 90.9, "sd1": 93.8, "sd2": 97.1, "sd3": 100.1 },
          { "month": 32, "sd3neg": 83.4, "sd2neg": 86.2, "sd1neg": 89.3, "median": 91.5, "sd1": 94.4, "sd2": 97.8, "sd3": 100.8 },
          { "month": 33, "sd3neg": 83.9, "sd2neg": 86.7, "sd1neg": 89.9, "median": 92.1, "sd1": 95.0, "sd2": 98.4, "sd3": 101.5 },
          { "month": 34, "sd3neg": 84.4, "sd2neg": 87.3, "sd1neg": 90.5, "median": 92.7, "sd1": 95.7, "sd2": 99.1, "sd3": 102.2 },
          { "month": 35, "sd3neg": 85.0, "sd2neg": 87.8, "sd1neg": 91.1, "median": 93.3, "sd1": 96.3, "sd2": 99.7, "sd3": 102.9 },

          { "month": 36, "sd3neg": 85.5, "sd2neg": 88.4, "sd1neg": 91.7, "median": 94.0, "sd1": 97.0, "sd2": 100.4, "sd3": 103.6 },
          { "month": 37, "sd3neg": 86.0, "sd2neg": 88.9, "sd1neg": 92.3, "median": 94.6, "sd1": 97.6, "sd2": 101.0, "sd3": 104.3 },
          { "month": 38, "sd3neg": 86.5, "sd2neg": 89.4, "sd1neg": 92.9, "median": 95.2, "sd1": 98.2, "sd2": 101.7, "sd3": 105.0 },
          { "month": 39, "sd3neg": 87.0, "sd2neg": 90.0, "sd1neg": 93.4, "median": 95.7, "sd1": 98.8, "sd2": 102.3, "sd3": 105.7 },

          { "month": 40, "sd3neg": 87.5, "sd2neg": 90.5, "sd1neg": 94.0, "median": 96.3, "sd1": 99.4, "sd2": 102.9, "sd3": 106.3 },
          { "month": 41, "sd3neg": 88.0, "sd2neg": 91.0, "sd1neg": 94.5, "median": 96.9, "sd1": 100.0, "sd2": 103.5, "sd3": 107.0 },
          { "month": 42, "sd3neg": 88.4, "sd2neg": 91.4, "sd1neg": 95.0, "median": 97.4, "sd1": 100.5, "sd2": 104.1, "sd3": 107.7 },
          { "month": 43, "sd3neg": 88.9, "sd2neg": 91.9, "sd1neg": 95.5, "median": 97.9, "sd1": 101.1, "sd2": 104.7, "sd3": 108.3 },
          { "month": 44, "sd3neg": 89.3, "sd2neg": 92.4, "sd1neg": 96.0, "median": 98.5, "sd1": 101.6, "sd2": 105.3, "sd3": 109.0 },
          { "month": 45, "sd3neg": 89.8, "sd2neg": 92.9, "sd1neg": 96.5, "median": 99.0, "sd1": 102.2, "sd2": 105.9, "sd3": 109.7 },

          { "month": 46, "sd3neg": 90.2, "sd2neg": 93.3, "sd1neg": 96.9, "median": 99.5, "sd1": 102.7, "sd2": 106.5, "sd3": 110.3 },
          { "month": 47, "sd3neg": 90.7, "sd2neg": 93.8, "sd1neg": 97.4, "median": 100.0, "sd1": 103.2, "sd2": 107.0, "sd3": 111.0 },
          { "month": 48, "sd3neg": 91.1, "sd2neg": 94.2, "sd1neg": 97.8, "median": 100.5, "sd1": 103.7, "sd2": 107.6, "sd3": 111.6 },
          { "month": 49, "sd3neg": 91.6, "sd2neg": 94.7, "sd1neg": 98.3, "median": 101.0, "sd1": 104.2, "sd2": 108.2, "sd3": 112.3 },

          { "month": 50, "sd3neg": 92.0, "sd2neg": 95.1, "sd1neg": 98.7, "median": 101.5, "sd1": 104.8, "sd2": 108.7, "sd3": 112.9 },
          { "month": 51, "sd3neg": 92.4, "sd2neg": 95.6, "sd1neg": 99.2, "median": 102.0, "sd1": 105.3, "sd2": 109.3, "sd3": 113.6 },
          { "month": 52, "sd3neg": 92.9, "sd2neg": 96.0, "sd1neg": 99.6, "median": 102.5, "sd1": 105.8, "sd2": 109.9, "sd3": 114.2 },
          { "month": 53, "sd3neg": 93.3, "sd2neg": 96.4, "sd1neg": 100.0, "median": 103.0, "sd1": 106.3, "sd2": 110.4, "sd3": 114.9 },
          { "month": 54, "sd3neg": 93.7, "sd2neg": 96.9, "sd1neg": 100.5, "median": 103.5, "sd1": 106.8, "sd2": 111.0, "sd3": 115.5 },
          { "month": 55, "sd3neg": 94.1, "sd2neg": 97.3, "sd1neg": 100.9, "median": 103.9, "sd1": 107.3, "sd2": 111.6, "sd3": 116.1 },
          { "month": 56, "sd3neg": 94.5, "sd2neg": 97.7, "sd1neg": 101.3, "median": 104.4, "sd1": 107.8, "sd2": 112.1, "sd3": 116.7 },
          { "month": 57, "sd3neg": 95.0, "sd2neg": 98.1, "sd1neg": 101.8, "median": 104.9, "sd1": 108.3, "sd2": 112.7, "sd3": 117.4 },
          { "month": 58, "sd3neg": 95.4, "sd2neg": 98.6, "sd1neg": 102.2, "median": 105.4, "sd1": 108.8, "sd2": 113.2, "sd3": 118.0 },
          { "month": 59, "sd3neg": 95.8, "sd2neg": 99.0, "sd1neg": 102.6, "median": 105.8, "sd1": 109.3, "sd2": 113.8, "sd3": 118.6 },
          { "month": 60, "sd3neg": 96.2, "sd2neg": 99.4, "sd1neg": 103.1, "median": 106.3, "sd1": 109.8, "sd2": 114.3, "sd3": 119.2 }
        ],
        "P": [
          { "month": 0,  "sd3neg": 43.6, "sd2neg": 45.4, "sd1neg": 47.3, "median": 49.1, "sd1": 51.0, "sd2": 52.9, "sd3": 54.7 },
          { "month": 1,  "sd3neg": 47.8, "sd2neg": 49.8, "sd1neg": 51.7, "median": 53.7, "sd1": 55.6, "sd2": 57.6, "sd3": 59.5 },
          { "month": 2,  "sd3neg": 51.0, "sd2neg": 53.0, "sd1neg": 55.0, "median": 57.1, "sd1": 59.1, "sd2": 61.1, "sd3": 63.2 },
          { "month": 3,  "sd3neg": 53.5, "sd2neg": 55.6, "sd1neg": 57.7, "median": 59.8, "sd1": 61.9, "sd2": 64.0, "sd3": 66.1 },
          { "month": 4,  "sd3neg": 55.6, "sd2neg": 57.8, "sd1neg": 59.9, "median": 62.1, "sd1": 64.3, "sd2": 66.4, "sd3": 68.6 },
          { "month": 5,  "sd3neg": 57.4, "sd2neg": 59.6, "sd1neg": 61.8, "median": 64.0, "sd1": 66.2, "sd2": 68.5, "sd3": 70.7 },
          { "month": 6,  "sd3neg": 58.9, "sd2neg": 61.1, "sd1neg": 63.3, "median": 65.7, "sd1": 68.0, "sd2": 70.3, "sd3": 72.5 },
          { "month": 7,  "sd3neg": 60.3, "sd2neg": 62.6, "sd1neg": 64.9, "median": 67.3, "sd1": 69.7, "sd2": 72.0, "sd3": 74.3 },
          { "month": 8,  "sd3neg": 61.7, "sd2neg": 64.0, "sd1neg": 66.3, "median": 68.7, "sd1": 71.1, "sd2": 73.5, "sd3": 75.9 },
          { "month": 9,  "sd3neg": 62.9, "sd2neg": 65.2, "sd1neg": 67.6, "median": 70.0, "sd1": 72.5, "sd2": 74.8, "sd3": 77.4 },
          { "month": 10, "sd3neg": 64.1, "sd2neg": 66.4, "sd1neg": 68.9, "median": 71.3, "sd1": 73.7, "sd2": 76.1, "sd3": 78.8 },
          { "month": 11, "sd3neg": 65.2, "sd2neg": 67.6, "sd1neg": 70.1, "median": 72.5, "sd1": 75.0, "sd2": 77.4, "sd3": 80.2 },
          { "month": 12, "sd3neg": 66.3, "sd2neg": 68.6, "sd1neg": 71.2, "median": 73.7, "sd1": 76.2, "sd2": 78.7, "sd3": 81.5 },

          { "month": 13, "sd3neg": 67.3, "sd2neg": 69.7, "sd1neg": 72.3, "median": 74.9, "sd1": 77.4, "sd2": 80.0, "sd3": 82.9 },
          { "month": 14, "sd3neg": 68.3, "sd2neg": 70.7, "sd1neg": 73.3, "median": 76.0, "sd1": 78.6, "sd2": 81.2, "sd3": 84.2 },
          { "month": 15, "sd3neg": 69.3, "sd2neg": 71.7, "sd1neg": 74.4, "median": 77.2, "sd1": 79.7, "sd2": 82.4, "sd3": 85.5 },
          { "month": 16, "sd3neg": 70.2, "sd2neg": 72.7, "sd1neg": 75.4, "median": 78.2, "sd1": 80.8, "sd2": 83.6, "sd3": 86.7 },
          { "month": 17, "sd3neg": 71.1, "sd2neg": 73.7, "sd1neg": 76.4, "median": 79.3, "sd1": 81.9, "sd2": 84.7, "sd3": 88.0 },
          { "month": 18, "sd3neg": 72.0, "sd2neg": 74.6, "sd1neg": 77.4, "median": 80.3, "sd1": 83.0, "sd2": 85.8, "sd3": 89.2 },

          { "month": 19, "sd3neg": 72.8, "sd2neg": 75.4, "sd1neg": 78.3, "median": 81.3, "sd1": 84.0, "sd2": 86.9, "sd3": 90.4 },
          { "month": 20, "sd3neg": 73.6, "sd2neg": 76.3, "sd1neg": 79.2, "median": 82.3, "sd1": 85.0, "sd2": 88.0, "sd3": 91.5 },

          { "month": 21, "sd3neg": 74.4, "sd2neg": 77.1, "sd1neg": 80.1, "median": 83.2, "sd1": 86.0, "sd2": 89.1, "sd3": 92.7 },
          { "month": 22, "sd3neg": 75.1, "sd2neg": 77.9, "sd1neg": 80.9, "median": 84.2, "sd1": 87.0, "sd2": 90.2, "sd3": 93.9 },
          { "month": 23, "sd3neg": 75.8, "sd2neg": 78.6, "sd1neg": 81.8, "median": 85.1, "sd1": 88.0, "sd2": 91.2, "sd3": 95.0 },

          { "month": 24, "sd3neg": 76.4, "sd2neg": 79.3, "sd1neg": 82.5, "median": 85.9, "sd1": 88.9, "sd2": 92.1, "sd3": 96.1 },

          { "month": 25, "sd3neg": 76.9, "sd2neg": 79.8, "sd1neg": 83.1, "median": 86.6, "sd1": 89.7, "sd2": 93.0, "sd3": 97.0 },
          { "month": 26, "sd3neg": 77.5, "sd2neg": 80.5, "sd1neg": 83.8, "median": 87.4, "sd1": 90.6, "sd2": 93.9, "sd3": 98.0 },
          { "month": 27, "sd3neg": 78.0, "sd2neg": 81.0, "sd1neg": 84.4, "median": 88.1, "sd1": 91.4, "sd2": 94.7, "sd3": 98.9 },
          { "month": 28, "sd3neg": 78.6, "sd2neg": 81.7, "sd1neg": 85.1, "median": 88.8, "sd1": 92.1, "sd2": 95.6, "sd3": 99.9 },
          { "month": 29, "sd3neg": 79.1, "sd2neg": 82.2, "sd1neg": 85.7, "median": 89.5, "sd1": 92.9, "sd2": 96.4, "sd3": 100.8 },

          { "month": 30, "sd3neg": 79.6, "sd2neg": 82.7, "sd1neg": 86.3, "median": 90.2, "sd1": 93.6, "sd2": 97.2, "sd3": 101.7 },

          { "month": 31, "sd3neg": 80.1, "sd2neg": 83.3, "sd1neg": 86.9, "median": 90.9, "sd1": 94.4, "sd2": 98.0, "sd3": 102.6 },
          { "month": 32, "sd3neg": 80.6, "sd2neg": 83.8, "sd1neg": 87.5, "median": 91.5, "sd1": 95.1, "sd2": 98.8, "sd3": 103.4 },
          { "month": 33, "sd3neg": 81.1, "sd2neg": 84.3, "sd1neg": 88.1, "median": 92.2, "sd1": 95.8, "sd2": 99.6, "sd3": 104.3 },
          { "month": 34, "sd3neg": 81.6, "sd2neg": 84.8, "sd1neg": 88.6, "median": 92.8, "sd1": 96.5, "sd2": 100.3, "sd3": 105.1 },
          { "month": 35, "sd3neg": 82.1, "sd2neg": 85.3, "sd1neg": 89.2, "median": 93.4, "sd1": 97.1, "sd2": 101.1, "sd3": 105.9 },

          { "month": 36, "sd3neg": 82.5, "sd2neg": 85.8, "sd1neg": 89.7, "median": 94.0, "sd1": 97.7, "sd2": 101.8, "sd3": 106.7 },

          { "month": 37, "sd3neg": 83.0, "sd2neg": 86.3, "sd1neg": 90.3, "median": 94.6, "sd1": 98.4, "sd2": 102.5, "sd3": 107.5 },
          { "month": 38, "sd3neg": 83.5, "sd2neg": 86.8, "sd1neg": 90.8, "median": 95.2, "sd1": 99.0, "sd2": 103.3, "sd3": 108.3 },
          { "month": 39, "sd3neg": 84.0, "sd2neg": 87.3, "sd1neg": 91.4, "median": 95.8, "sd1": 99.7, "sd2": 104.0, "sd3": 109.1 },

          { "month": 40, "sd3neg": 84.4, "sd2neg": 87.7, "sd1neg": 91.9, "median": 96.4, "sd1": 100.3, "sd2": 104.7, "sd3": 109.8 },

          { "month": 41, "sd3neg": 84.9, "sd2neg": 88.2, "sd1neg": 92.4, "median": 96.9, "sd1": 100.9, "sd2": 105.4, "sd3": 110.6 },
          { "month": 42, "sd3neg": 85.3, "sd2neg": 88.7, "sd1neg": 93.0, "median": 97.5, "sd1": 101.5, "sd2": 106.1, "sd3": 111.3 },
          { "month": 43, "sd3neg": 85.8, "sd2neg": 89.1, "sd1neg": 93.5, "median": 98.0, "sd1": 102.1, "sd2": 106.8, "sd3": 112.0 },
          { "month": 44, "sd3neg": 86.2, "sd2neg": 89.6, "sd1neg": 94.0, "median": 98.6, "sd1": 102.7, "sd2": 107.5, "sd3": 112.7 },
          { "month": 45, "sd3neg": 86.7, "sd2neg": 90.0, "sd1neg": 94.5, "median": 99.1, "sd1": 103.3, "sd2": 108.1, "sd3": 113.4 },

          { "month": 46, "sd3neg": 87.1, "sd2neg": 90.5, "sd1neg": 95.0, "median": 99.7, "sd1": 103.8, "sd2": 108.8, "sd3": 114.1 },
          { "month": 47, "sd3neg": 87.6, "sd2neg": 90.9, "sd1neg": 95.5, "median": 100.2, "sd1": 104.4, "sd2": 109.4, "sd3": 114.8 },
          { "month": 48, "sd3neg": 88.0, "sd2neg": 91.4, "sd1neg": 96.0, "median": 100.7, "sd1": 105.0, "sd2": 110.1, "sd3": 115.5 },
          { "month": 49, "sd3neg": 88.4, "sd2neg": 91.8, "sd1neg": 96.5, "median": 101.2, "sd1": 105.6, "sd2": 110.7, "sd3": 116.1 },

          { "month": 50, "sd3neg": 88.9, "sd2neg": 92.3, "sd1neg": 96.9, "median": 101.7, "sd1": 106.1, "sd2": 111.3, "sd3": 116.8 },

          { "month": 51, "sd3neg": 89.3, "sd2neg": 92.7, "sd1neg": 97.4, "median": 102.2, "sd1": 106.7, "sd2": 112.0, "sd3": 117.4 },
          { "month": 52, "sd3neg": 89.7, "sd2neg": 93.2, "sd1neg": 97.9, "median": 102.7, "sd1": 107.2, "sd2": 112.6, "sd3": 118.1 },
          { "month": 53, "sd3neg": 90.1, "sd2neg": 93.6, "sd1neg": 98.3, "median": 103.2, "sd1": 107.8, "sd2": 113.2, "sd3": 118.7 },
          { "month": 54, "sd3neg": 90.5, "sd2neg": 94.0, "sd1neg": 98.8, "median": 103.7, "sd1": 108.3, "sd2": 113.8, "sd3": 119.4 },
          { "month": 55, "sd3neg": 90.9, "sd2neg": 94.4, "sd1neg": 99.3, "median": 104.2, "sd1": 108.9, "sd2": 114.4, "sd3": 120.0 },

          { "month": 56, "sd3neg": 91.3, "sd2neg": 94.9, "sd1neg": 99.7, "median": 104.7, "sd1": 109.4, "sd2": 115.0, "sd3": 120.6 },
          { "month": 57, "sd3neg": 91.7, "sd2neg": 95.3, "sd1neg": 100.2, "median": 105.2, "sd1": 110.0, "sd2": 115.7, "sd3": 121.3 },
          { "month": 58, "sd3neg": 92.1, "sd2neg": 95.7, "sd1neg": 100.6, "median": 105.7, "sd1": 110.5, "sd2": 116.3, "sd3": 121.9 },
          { "month": 59, "sd3neg": 92.5, "sd2neg": 96.1, "sd1neg": 101.1, "median": 106.2, "sd1": 111.0, "sd2": 116.9, "sd3": 122.6 },
          { "month": 60, "sd3neg": 92.9, "sd2neg": 96.5, "sd1neg": 101.5, "median": 106.7, "sd1": 111.6, "sd2": 117.6, "sd3": 123.2 }
        ]
      },
      whfa:{
        "baduta":[
          {
            "L": [
              { "length": 45.0, "sd3neg": 1.9, "sd2neg": 2.1, "sd1neg": 2.4, "median": 2.8, "sd1": 3.2, "sd2": 3.7, "sd3": 4.2 },
              { "length": 45.5, "sd3neg": 2.0, "sd2neg": 2.2, "sd1neg": 2.5, "median": 2.9, "sd1": 3.3, "sd2": 3.8, "sd3": 4.4 },
              { "length": 46.0, "sd3neg": 2.0, "sd2neg": 2.3, "sd1neg": 2.6, "median": 3.0, "sd1": 3.4, "sd2": 3.9, "sd3": 4.5 },
              { "length": 46.5, "sd3neg": 2.1, "sd2neg": 2.4, "sd1neg": 2.7, "median": 3.1, "sd1": 3.5, "sd2": 4.0, "sd3": 4.6 },
              { "length": 47.0, "sd3neg": 2.1, "sd2neg": 2.5, "sd1neg": 2.8, "median": 3.2, "sd1": 3.6, "sd2": 4.1, "sd3": 4.8 },
              { "length": 47.5, "sd3neg": 2.2, "sd2neg": 2.6, "sd1neg": 2.9, "median": 3.3, "sd1": 3.7, "sd2": 4.2, "sd3": 4.9 },
              { "length": 48.0, "sd3neg": 2.2, "sd2neg": 2.7, "sd1neg": 3.0, "median": 3.4, "sd1": 3.8, "sd2": 4.3, "sd3": 5.0 },
              { "length": 48.5, "sd3neg": 2.3, "sd2neg": 2.8, "sd1neg": 3.1, "median": 3.5, "sd1": 3.9, "sd2": 4.4, "sd3": 5.1 },
              { "length": 49.0, "sd3neg": 2.3, "sd2neg": 2.9, "sd1neg": 3.2, "median": 3.6, "sd1": 4.0, "sd2": 4.5, "sd3": 5.2 },
              { "length": 49.5, "sd3neg": 2.4, "sd2neg": 2.9, "sd1neg": 3.3, "median": 3.7, "sd1": 4.1, "sd2": 4.6, "sd3": 5.4 },
              { "length": 50.0, "sd3neg": 2.4, "sd2neg": 3.0, "sd1neg": 3.4, "median": 3.8, "sd1": 4.2, "sd2": 4.7, "sd3": 5.5 },
              { "length": 50.5, "sd3neg": 2.5, "sd2neg": 3.1, "sd1neg": 3.5, "median": 3.9, "sd1": 4.3, "sd2": 4.8, "sd3": 5.6 },
              { "length": 51.0, "sd3neg": 2.5, "sd2neg": 3.2, "sd1neg": 3.6, "median": 4.0, "sd1": 4.4, "sd2": 4.9, "sd3": 5.7 },
              { "length": 51.5, "sd3neg": 2.6, "sd2neg": 3.3, "sd1neg": 3.7, "median": 4.1, "sd1": 4.5, "sd2": 5.0, "sd3": 5.8 },
              { "length": 52.0, "sd3neg": 2.7, "sd2neg": 3.4, "sd1neg": 3.8, "median": 4.2, "sd1": 4.6, "sd2": 5.1, "sd3": 5.9 },
              { "length": 52.5, "sd3neg": 2.7, "sd2neg": 3.4, "sd1neg": 3.9, "median": 4.3, "sd1": 4.7, "sd2": 5.2, "sd3": 6.0 },
              { "length": 53.0, "sd3neg": 2.8, "sd2neg": 3.5, "sd1neg": 4.0, "median": 4.4, "sd1": 4.8, "sd2": 5.3, "sd3": 6.1 },
              { "length": 53.5, "sd3neg": 2.9, "sd2neg": 3.6, "sd1neg": 4.1, "median": 4.5, "sd1": 4.9, "sd2": 5.4, "sd3": 6.2 },
              { "length": 54.0, "sd3neg": 3.0, "sd2neg": 3.7, "sd1neg": 4.2, "median": 4.7, "sd1": 5.1, "sd2": 5.6, "sd3": 6.4 },
              { "length": 54.5, "sd3neg": 3.0, "sd2neg": 3.8, "sd1neg": 4.3, "median": 4.8, "sd1": 5.2, "sd2": 5.7, "sd3": 6.5 },
              { "length": 55.0, "sd3neg": 3.1, "sd2neg": 3.9, "sd1neg": 4.4, "median": 4.9, "sd1": 5.3, "sd2": 5.8, "sd3": 6.6 },
              { "length": 55.5, "sd3neg": 3.2, "sd2neg": 4.0, "sd1neg": 4.5, "median": 5.0, "sd1": 5.4, "sd2": 5.9, "sd3": 6.7 },
              { "length": 56.0, "sd3neg": 3.3, "sd2neg": 4.1, "sd1neg": 4.6, "median": 5.1, "sd1": 5.5, "sd2": 6.0, "sd3": 6.8 },
              { "length": 56.5, "sd3neg": 3.4, "sd2neg": 4.1, "sd1neg": 4.7, "median": 5.2, "sd1": 5.6, "sd2": 6.1, "sd3": 7.0 },
              { "length": 57.0, "sd3neg": 3.5, "sd2neg": 4.2, "sd1neg": 4.8, "median": 5.3, "sd1": 5.7, "sd2": 6.2, "sd3": 7.1 },
              { "length": 57.5, "sd3neg": 3.6, "sd2neg": 4.3, "sd1neg": 4.9, "median": 5.4, "sd1": 5.8, "sd2": 6.3, "sd3": 7.2 },
              { "length": 58.0, "sd3neg": 3.7, "sd2neg": 4.4, "sd1neg": 5.0, "median": 5.5, "sd1": 5.9, "sd2": 6.4, "sd3": 7.3 },
              { "length": 58.5, "sd3neg": 3.8, "sd2neg": 4.5, "sd1neg": 5.1, "median": 5.6, "sd1": 6.0, "sd2": 6.5, "sd3": 7.5 },
              { "length": 59.0, "sd3neg": 3.9, "sd2neg": 4.6, "sd1neg": 5.2, "median": 5.7, "sd1": 6.1, "sd2": 6.6, "sd3": 7.6 },
              { "length": 59.5, "sd3neg": 4.0, "sd2neg": 4.7, "sd1neg": 5.3, "median": 5.8, "sd1": 6.2, "sd2": 6.7, "sd3": 7.7 },
              { "length": 60.0, "sd3neg": 4.1, "sd2neg": 4.8, "sd1neg": 5.4, "median": 5.9, "sd1": 6.3, "sd2": 6.8, "sd3": 7.8 },

              { "length": 60.5, "sd3neg": 4.2, "sd2neg": 4.9, "sd1neg": 5.5, "median": 6.0, "sd1": 6.4, "sd2": 6.9, "sd3": 7.9 },
              { "length": 61.0, "sd3neg": 4.3, "sd2neg": 5.0, "sd1neg": 5.6, "median": 6.1, "sd1": 6.5, "sd2": 7.0, "sd3": 8.0 },
              { "length": 61.5, "sd3neg": 4.4, "sd2neg": 5.1, "sd1neg": 5.7, "median": 6.2, "sd1": 6.6, "sd2": 7.1, "sd3": 8.1 },
              { "length": 62.0, "sd3neg": 4.5, "sd2neg": 5.2, "sd1neg": 5.8, "median": 6.3, "sd1": 6.7, "sd2": 7.2, "sd3": 8.2 },
              { "length": 62.5, "sd3neg": 4.6, "sd2neg": 5.3, "sd1neg": 5.9, "median": 6.4, "sd1": 6.8, "sd2": 7.3, "sd3": 8.3 },
              { "length": 63.0, "sd3neg": 4.6, "sd2neg": 5.4, "sd1neg": 6.0, "median": 6.5, "sd1": 6.9, "sd2": 7.4, "sd3": 8.4 },
              { "length": 63.5, "sd3neg": 4.7, "sd2neg": 5.5, "sd1neg": 6.1, "median": 6.6, "sd1": 7.0, "sd2": 7.5, "sd3": 8.5 },
              { "length": 64.0, "sd3neg": 4.8, "sd2neg": 5.6, "sd1neg": 6.2, "median": 6.7, "sd1": 7.1, "sd2": 7.6, "sd3": 8.6 },
              { "length": 64.5, "sd3neg": 4.9, "sd2neg": 5.7, "sd1neg": 6.3, "median": 6.8, "sd1": 7.2, "sd2": 7.7, "sd3": 8.7 },
              { "length": 65.0, "sd3neg": 5.0, "sd2neg": 5.8, "sd1neg": 6.4, "median": 6.9, "sd1": 7.3, "sd2": 7.8, "sd3": 8.8 },

              { "length": 65.5, "sd3neg": 5.1, "sd2neg": 5.9, "sd1neg": 6.5, "median": 7.0, "sd1": 7.4, "sd2": 7.9, "sd3": 8.9 },
              { "length": 66.0, "sd3neg": 5.2, "sd2neg": 6.0, "sd1neg": 6.6, "median": 7.1, "sd1": 7.5, "sd2": 8.0, "sd3": 9.0 },
              { "length": 66.5, "sd3neg": 5.3, "sd2neg": 6.1, "sd1neg": 6.7, "median": 7.2, "sd1": 7.6, "sd2": 8.1, "sd3": 9.1 },
              { "length": 67.0, "sd3neg": 5.4, "sd2neg": 6.2, "sd1neg": 6.8, "median": 7.3, "sd1": 7.7, "sd2": 8.2, "sd3": 9.2 },
              { "length": 67.5, "sd3neg": 5.5, "sd2neg": 6.3, "sd1neg": 6.9, "median": 7.4, "sd1": 7.8, "sd2": 8.3, "sd3": 9.3 },
              { "length": 68.0, "sd3neg": 5.6, "sd2neg": 6.4, "sd1neg": 7.0, "median": 7.5, "sd1": 7.9, "sd2": 8.4, "sd3": 9.4 },
              { "length": 68.5, "sd3neg": 5.7, "sd2neg": 6.5, "sd1neg": 7.1, "median": 7.6, "sd1": 8.0, "sd2": 8.5, "sd3": 9.5 },
              { "length": 69.0, "sd3neg": 5.8, "sd2neg": 6.6, "sd1neg": 7.2, "median": 7.7, "sd1": 8.1, "sd2": 8.6, "sd3": 9.6 },
              { "length": 69.5, "sd3neg": 5.9, "sd2neg": 6.7, "sd1neg": 7.3, "median": 7.8, "sd1": 8.2, "sd2": 8.7, "sd3": 9.7 },
              { "length": 70.0, "sd3neg": 6.0, "sd2neg": 6.8, "sd1neg": 7.4, "median": 7.9, "sd1": 8.3, "sd2": 8.8, "sd3": 9.8 },

              { "length": 70.5, "sd3neg": 6.1, "sd2neg": 6.9, "sd1neg": 7.5, "median": 8.0, "sd1": 8.4, "sd2": 8.9, "sd3": 9.9 },
              { "length": 71.0, "sd3neg": 6.2, "sd2neg": 7.0, "sd1neg": 7.6, "median": 8.1, "sd1": 8.5, "sd2": 9.0, "sd3": 10.0 },
              { "length": 71.5, "sd3neg": 6.3, "sd2neg": 7.1, "sd1neg": 7.7, "median": 8.2, "sd1": 8.6, "sd2": 9.1, "sd3": 10.1 },
              { "length": 72.0, "sd3neg": 6.4, "sd2neg": 7.2, "sd1neg": 7.8, "median": 8.3, "sd1": 8.7, "sd2": 9.2, "sd3": 10.2 },
              { "length": 72.5, "sd3neg": 6.5, "sd2neg": 7.3, "sd1neg": 7.9, "median": 8.4, "sd1": 8.8, "sd2": 9.3, "sd3": 10.3 },
              { "length": 73.0, "sd3neg": 6.6, "sd2neg": 7.4, "sd1neg": 8.0, "median": 8.5, "sd1": 8.9, "sd2": 9.4, "sd3": 10.4 },
              { "length": 73.5, "sd3neg": 6.7, "sd2neg": 7.5, "sd1neg": 8.1, "median": 8.6, "sd1": 9.0, "sd2": 9.5, "sd3": 10.5 },
              { "length": 74.0, "sd3neg": 6.8, "sd2neg": 7.6, "sd1neg": 8.2, "median": 8.7, "sd1": 9.1, "sd2": 9.6, "sd3": 10.6 },
              { "length": 74.5, "sd3neg": 6.9, "sd2neg": 7.7, "sd1neg": 8.3, "median": 8.8, "sd1": 9.2, "sd2": 9.7, "sd3": 10.7 },
              { "length": 75.0, "sd3neg": 7.0, "sd2neg": 7.8, "sd1neg": 8.4, "median": 8.9, "sd1": 9.3, "sd2": 9.8, "sd3": 10.8 },

              { "length": 75.5, "sd3neg": 7.1, "sd2neg": 7.9, "sd1neg": 8.5, "median": 9.0, "sd1": 9.4, "sd2": 9.9, "sd3": 10.9 },
              { "length": 76.0, "sd3neg": 7.2, "sd2neg": 8.0, "sd1neg": 8.6, "median": 9.1, "sd1": 9.5, "sd2": 10.0, "sd3": 11.0 },
              { "length": 76.5, "sd3neg": 7.3, "sd2neg": 8.1, "sd1neg": 8.7, "median": 9.2, "sd1": 9.6, "sd2": 10.1, "sd3": 11.1 },
              { "length": 77.0, "sd3neg": 7.4, "sd2neg": 8.2, "sd1neg": 8.8, "median": 9.3, "sd1": 9.7, "sd2": 10.2, "sd3": 11.2 },
              { "length": 77.5, "sd3neg": 7.5, "sd2neg": 8.3, "sd1neg": 8.9, "median": 9.4, "sd1": 9.8, "sd2": 10.3, "sd3": 11.3 },
              { "length": 78.0, "sd3neg": 7.6, "sd2neg": 8.4, "sd1neg": 9.0, "median": 9.5, "sd1": 9.9, "sd2": 10.4, "sd3": 11.4 },
              { "length": 78.5, "sd3neg": 7.7, "sd2neg": 8.5, "sd1neg": 9.1, "median": 9.6, "sd1": 10.0, "sd2": 10.5, "sd3": 11.5 },
              { "length": 79.0, "sd3neg": 7.8, "sd2neg": 8.6, "sd1neg": 9.2, "median": 9.7, "sd1": 10.1, "sd2": 10.6, "sd3": 11.6 },
              { "length": 79.5, "sd3neg": 7.9, "sd2neg": 8.7, "sd1neg": 9.3, "median": 9.8, "sd1": 10.2, "sd2": 10.7, "sd3": 11.7 },
              { "length": 80.0, "sd3neg": 8.0, "sd2neg": 8.8, "sd1neg": 9.4, "median": 9.9, "sd1": 10.3, "sd2": 10.8, "sd3": 11.8 },

              { "length": 80.5, "sd3neg": 8.1, "sd2neg": 8.9, "sd1neg": 9.5, "median": 10.0, "sd1": 10.4, "sd2": 10.9, "sd3": 11.9 },
              { "length": 81.0, "sd3neg": 8.2, "sd2neg": 9.0, "sd1neg": 9.6, "median": 10.1, "sd1": 10.5, "sd2": 11.0, "sd3": 12.0 },
              { "length": 81.5, "sd3neg": 8.3, "sd2neg": 9.1, "sd1neg": 9.7, "median": 10.2, "sd1": 10.6, "sd2": 11.1, "sd3": 12.1 },
              { "length": 82.0, "sd3neg": 8.4, "sd2neg": 9.2, "sd1neg": 9.8, "median": 10.3, "sd1": 10.7, "sd2": 11.2, "sd3": 12.2 },
              { "length": 82.5, "sd3neg": 8.5, "sd2neg": 9.3, "sd1neg": 9.9, "median": 10.4, "sd1": 10.8, "sd2": 11.3, "sd3": 12.3 },
              { "length": 83.0, "sd3neg": 8.6, "sd2neg": 9.4, "sd1neg": 10.0, "median": 10.5, "sd1": 10.9, "sd2": 11.4, "sd3": 12.4 },
              { "length": 83.5, "sd3neg": 8.7, "sd2neg": 9.5, "sd1neg": 10.1, "median": 10.6, "sd1": 11.0, "sd2": 11.5, "sd3": 12.5 },
              { "length": 84.0, "sd3neg": 8.8, "sd2neg": 9.6, "sd1neg": 10.2, "median": 10.7, "sd1": 11.1, "sd2": 11.6, "sd3": 12.6 },
              { "length": 84.5, "sd3neg": 8.9, "sd2neg": 9.7, "sd1neg": 10.3, "median": 10.8, "sd1": 11.2, "sd2": 11.7, "sd3": 12.7 },
              { "length": 85.0, "sd3neg": 9.0, "sd2neg": 9.8, "sd1neg": 10.4, "median": 10.9, "sd1": 11.3, "sd2": 11.8, "sd3": 12.8 },

              { "length": 85.5, "sd3neg": 9.1, "sd2neg": 9.9, "sd1neg": 10.5, "median": 11.0, "sd1": 11.4, "sd2": 11.9, "sd3": 12.9 },
              { "length": 86.0, "sd3neg": 9.2, "sd2neg": 10.0, "sd1neg": 10.6, "median": 11.1, "sd1": 11.5, "sd2": 12.0, "sd3": 13.0 },
              { "length": 86.5, "sd3neg": 9.3, "sd2neg": 10.1, "sd1neg": 10.7, "median": 11.2, "sd1": 11.6, "sd2": 12.1, "sd3": 13.1 },
              { "length": 87.0, "sd3neg": 9.4, "sd2neg": 10.2, "sd1neg": 10.8, "median": 11.3, "sd1": 11.7, "sd2": 12.2, "sd3": 13.2 },
              { "length": 87.5, "sd3neg": 9.5, "sd2neg": 10.3, "sd1neg": 10.9, "median": 11.4, "sd1": 11.8, "sd2": 12.3, "sd3": 13.3 },
              { "length": 88.0, "sd3neg": 9.6, "sd2neg": 10.4, "sd1neg": 11.0, "median": 11.5, "sd1": 11.9, "sd2": 12.4, "sd3": 13.4 },
              { "length": 88.5, "sd3neg": 9.7, "sd2neg": 10.5, "sd1neg": 11.1, "median": 11.6, "sd1": 12.0, "sd2": 12.5, "sd3": 13.5 },
              { "length": 89.0, "sd3neg": 9.8, "sd2neg": 10.6, "sd1neg": 11.2, "median": 11.7, "sd1": 12.1, "sd2": 12.6, "sd3": 13.6 },
              { "length": 89.5, "sd3neg": 9.9, "sd2neg": 10.7, "sd1neg": 11.3, "median": 11.8, "sd1": 12.2, "sd2": 12.7, "sd3": 13.7 },
              { "length": 90.0, "sd3neg": 10.0, "sd2neg": 10.8, "sd1neg": 11.4, "median": 11.9, "sd1": 12.3, "sd2": 12.8, "sd3": 13.8 },

              { "length": 90.5, "sd3neg": 10.1, "sd2neg": 10.9, "sd1neg": 11.5, "median": 12.0, "sd1": 12.4, "sd2": 12.9, "sd3": 13.9 },
              { "length": 91.0, "sd3neg": 10.2, "sd2neg": 11.0, "sd1neg": 11.6, "median": 12.1, "sd1": 12.5, "sd2": 13.0, "sd3": 14.0 },
              { "length": 91.5, "sd3neg": 10.3, "sd2neg": 11.1, "sd1neg": 11.7, "median": 12.2, "sd1": 12.6, "sd2": 13.1, "sd3": 14.1 },
              { "length": 92.0, "sd3neg": 10.4, "sd2neg": 11.2, "sd1neg": 11.8, "median": 12.3, "sd1": 12.7, "sd2": 13.2, "sd3": 14.2 },
              { "length": 92.5, "sd3neg": 10.5, "sd2neg": 11.3, "sd1neg": 11.9, "median": 12.4, "sd1": 12.8, "sd2": 13.3, "sd3": 14.3 },
              { "length": 93.0, "sd3neg": 10.6, "sd2neg": 11.4, "sd1neg": 12.0, "median": 12.5, "sd1": 12.9, "sd2": 13.4, "sd3": 14.4 },
              { "length": 93.5, "sd3neg": 10.7, "sd2neg": 11.5, "sd1neg": 12.1, "median": 12.6, "sd1": 13.0, "sd2": 13.5, "sd3": 14.5 },
              { "length": 94.0, "sd3neg": 10.8, "sd2neg": 11.6, "sd1neg": 12.2, "median": 12.7, "sd1": 13.1, "sd2": 13.6, "sd3": 14.6 },
              { "length": 94.5, "sd3neg": 10.9, "sd2neg": 11.7, "sd1neg": 12.3, "median": 12.8, "sd1": 13.2, "sd2": 13.7, "sd3": 14.7 },
              { "length": 95.0, "sd3neg": 11.0, "sd2neg": 11.8, "sd1neg": 12.4, "median": 12.9, "sd1": 13.3, "sd2": 13.8, "sd3": 14.8 },

              { "length": 95.5, "sd3neg": 11.1, "sd2neg": 11.9, "sd1neg": 12.5, "median": 13.0, "sd1": 13.4, "sd2": 13.9, "sd3": 14.9 },
              { "length": 96.0, "sd3neg": 11.2, "sd2neg": 12.0, "sd1neg": 12.6, "median": 13.1, "sd1": 13.5, "sd2": 14.0, "sd3": 15.0 },
              { "length": 96.5, "sd3neg": 11.3, "sd2neg": 12.1, "sd1neg": 12.7, "median": 13.2, "sd1": 13.6, "sd2": 14.1, "sd3": 15.1 },
              { "length": 97.0, "sd3neg": 11.4, "sd2neg": 12.2, "sd1neg": 12.8, "median": 13.3, "sd1": 13.7, "sd2": 14.2, "sd3": 15.2 },
              { "length": 97.5, "sd3neg": 11.5, "sd2neg": 12.3, "sd1neg": 12.9, "median": 13.4, "sd1": 13.8, "sd2": 14.3, "sd3": 15.3 },
              { "length": 98.0, "sd3neg": 11.6, "sd2neg": 12.4, "sd1neg": 13.0, "median": 13.5, "sd1": 13.9, "sd2": 14.4, "sd3": 15.4 },
              { "length": 98.5, "sd3neg": 11.7, "sd2neg": 12.5, "sd1neg": 13.1, "median": 13.6, "sd1": 14.0, "sd2": 14.5, "sd3": 15.5 },
              { "length": 99.0, "sd3neg": 11.8, "sd2neg": 12.6, "sd1neg": 13.2, "median": 13.7, "sd1": 14.1, "sd2": 14.6, "sd3": 15.6 },
              { "length": 99.5, "sd3neg": 11.9, "sd2neg": 12.7, "sd1neg": 13.3, "median": 13.8, "sd1": 14.2, "sd2": 14.7, "sd3": 15.7 },
              { "length": 100.0, "sd3neg": 12.0, "sd2neg": 12.8, "sd1neg": 13.4, "median": 13.9, "sd1": 14.3, "sd2": 14.8, "sd3": 15.8 },

              { "length": 100.5, "sd3neg": 12.1, "sd2neg": 12.9, "sd1neg": 13.5, "median": 14.0, "sd1": 14.4, "sd2": 14.9, "sd3": 15.9 },
              { "length": 101.0, "sd3neg": 12.2, "sd2neg": 13.0, "sd1neg": 13.6, "median": 14.1, "sd1": 14.5, "sd2": 15.0, "sd3": 16.0 },
              { "length": 101.5, "sd3neg": 12.3, "sd2neg": 13.1, "sd1neg": 13.7, "median": 14.2, "sd1": 14.6, "sd2": 15.1, "sd3": 16.1 },
              { "length": 102.0, "sd3neg": 12.4, "sd2neg": 13.2, "sd1neg": 13.8, "median": 14.3, "sd1": 14.7, "sd2": 15.2, "sd3": 16.2 },
              { "length": 102.5, "sd3neg": 12.5, "sd2neg": 13.3, "sd1neg": 13.9, "median": 14.4, "sd1": 14.8, "sd2": 15.3, "sd3": 16.3 },
              { "length": 103.0, "sd3neg": 12.6, "sd2neg": 13.4, "sd1neg": 14.0, "median": 14.5, "sd1": 14.9, "sd2": 15.4, "sd3": 16.4 },
              { "length": 103.5, "sd3neg": 12.7, "sd2neg": 13.5, "sd1neg": 14.1, "median": 14.6, "sd1": 15.0, "sd2": 15.5, "sd3": 16.5 },
              { "length": 104.0, "sd3neg": 12.8, "sd2neg": 13.6, "sd1neg": 14.2, "median": 14.7, "sd1": 15.1, "sd2": 15.6, "sd3": 16.6 },
              { "length": 104.5, "sd3neg": 12.9, "sd2neg": 13.7, "sd1neg": 14.3, "median": 14.8, "sd1": 15.2, "sd2": 15.7, "sd3": 16.7 },
              { "length": 105.0, "sd3neg": 13.0, "sd2neg": 13.8, "sd1neg": 14.4, "median": 14.9, "sd1": 15.3, "sd2": 15.8, "sd3": 16.8 },

              { "length": 105.5, "sd3neg": 13.1, "sd2neg": 13.9, "sd1neg": 14.5, "median": 15.0, "sd1": 15.4, "sd2": 15.9, "sd3": 16.9 },
              { "length": 106.0, "sd3neg": 13.2, "sd2neg": 14.0, "sd1neg": 14.6, "median": 15.1, "sd1": 15.5, "sd2": 16.0, "sd3": 17.0 },
              { "length": 106.5, "sd3neg": 13.3, "sd2neg": 14.1, "sd1neg": 14.7, "median": 15.2, "sd1": 15.6, "sd2": 16.1, "sd3": 17.1 },
              { "length": 107.0, "sd3neg": 13.4, "sd2neg": 14.2, "sd1neg": 14.8, "median": 15.3, "sd1": 15.7, "sd2": 16.2, "sd3": 17.2 },
              { "length": 107.5, "sd3neg": 13.5, "sd2neg": 14.3, "sd1neg": 14.9, "median": 15.4, "sd1": 15.8, "sd2": 16.3, "sd3": 17.3 },
              { "length": 108.0, "sd3neg": 13.6, "sd2neg": 14.4, "sd1neg": 15.0, "median": 15.5, "sd1": 15.9, "sd2": 16.4, "sd3": 17.4 },
              { "length": 108.5, "sd3neg": 13.7, "sd2neg": 14.5, "sd1neg": 15.1, "median": 15.6, "sd1": 16.0, "sd2": 16.5, "sd3": 17.5 },
              { "length": 109.0, "sd3neg": 13.8, "sd2neg": 14.6, "sd1neg": 15.2, "median": 15.7, "sd1": 16.1, "sd2": 16.6, "sd3": 17.6 },
              { "length": 109.5, "sd3neg": 13.9, "sd2neg": 14.7, "sd1neg": 15.3, "median": 15.8, "sd1": 16.2, "sd2": 16.7, "sd3": 17.7 },
              { "length": 110.0, "sd3neg": 12.6, "sd2neg": 13.9, "sd1neg": 15.3, "median": 17.0, "sd1": 19.0, "sd2": 21.3, "sd3": 23.9 }
            ],
            "P": [
              { "length": 45.0, "sd3neg": 1.8, "sd2neg": 2.0, "sd1neg": 2.3, "median": 2.7, "sd1": 3.1, "sd2": 3.6, "sd3": 4.1 },
              { "length": 45.5, "sd3neg": 1.9, "sd2neg": 2.1, "sd1neg": 2.4, "median": 2.8, "sd1": 3.2, "sd2": 3.7, "sd3": 4.3 },
              { "length": 46.0, "sd3neg": 2.0, "sd2neg": 2.2, "sd1neg": 2.5, "median": 2.9, "sd1": 3.3, "sd2": 3.8, "sd3": 4.4 },
              { "length": 46.5, "sd3neg": 2.0, "sd2neg": 2.3, "sd1neg": 2.6, "median": 3.0, "sd1": 3.4, "sd2": 3.9, "sd3": 4.5 },
              { "length": 47.0, "sd3neg": 2.1, "sd2neg": 2.4, "sd1neg": 2.7, "median": 3.1, "sd1": 3.5, "sd2": 4.0, "sd3": 4.7 },
              { "length": 47.5, "sd3neg": 2.1, "sd2neg": 2.5, "sd1neg": 2.8, "median": 3.2, "sd1": 3.6, "sd2": 4.2, "sd3": 4.8 },
              { "length": 48.0, "sd3neg": 2.2, "sd2neg": 2.6, "sd1neg": 2.9, "median": 3.3, "sd1": 3.7, "sd2": 4.3, "sd3": 5.0 },
              { "length": 48.5, "sd3neg": 2.3, "sd2neg": 2.7, "sd1neg": 3.0, "median": 3.4, "sd1": 3.9, "sd2": 4.4, "sd3": 5.1 },
              { "length": 49.0, "sd3neg": 2.3, "sd2neg": 2.8, "sd1neg": 3.1, "median": 3.5, "sd1": 4.0, "sd2": 4.6, "sd3": 5.3 },
              { "length": 49.5, "sd3neg": 2.4, "sd2neg": 2.9, "sd1neg": 3.2, "median": 3.6, "sd1": 4.1, "sd2": 4.7, "sd3": 5.4 },
              { "length": 50.0, "sd3neg": 2.5, "sd2neg": 3.0, "sd1neg": 3.3, "median": 3.7, "sd1": 4.2, "sd2": 4.8, "sd3": 5.6 },
              { "length": 50.5, "sd3neg": 2.6, "sd2neg": 3.1, "sd1neg": 3.4, "median": 3.8, "sd1": 4.3, "sd2": 5.0, "sd3": 5.7 },
              { "length": 51.0, "sd3neg": 2.6, "sd2neg": 3.2, "sd1neg": 3.5, "median": 3.9, "sd1": 4.4, "sd2": 5.1, "sd3": 5.9 },
              { "length": 51.5, "sd3neg": 2.7, "sd2neg": 3.3, "sd1neg": 3.6, "median": 4.0, "sd1": 4.5, "sd2": 5.2, "sd3": 6.0 },
              { "length": 52.0, "sd3neg": 2.8, "sd2neg": 3.4, "sd1neg": 3.7, "median": 4.1, "sd1": 4.6, "sd2": 5.3, "sd3": 6.2 },
              { "length": 52.5, "sd3neg": 2.9, "sd2neg": 3.5, "sd1neg": 3.8, "median": 4.2, "sd1": 4.7, "sd2": 5.5, "sd3": 6.3 },
              { "length": 53.0, "sd3neg": 3.0, "sd2neg": 3.6, "sd1neg": 3.9, "median": 4.3, "sd1": 4.8, "sd2": 5.6, "sd3": 6.5 },
              { "length": 53.5, "sd3neg": 3.1, "sd2neg": 3.7, "sd1neg": 4.1, "median": 4.4, "sd1": 5.0, "sd2": 5.7, "sd3": 6.6 },
              { "length": 54.0, "sd3neg": 3.2, "sd2neg": 3.8, "sd1neg": 4.2, "median": 4.6, "sd1": 5.1, "sd2": 5.9, "sd3": 6.8 },
              { "length": 54.5, "sd3neg": 3.3, "sd2neg": 3.9, "sd1neg": 4.3, "median": 4.7, "sd1": 5.2, "sd2": 6.0, "sd3": 7.0 },
              { "length": 55.0, "sd3neg": 3.4, "sd2neg": 4.0, "sd1neg": 4.4, "median": 4.8, "sd1": 5.3, "sd2": 6.2, "sd3": 7.1 },
              { "length": 55.5, "sd3neg": 3.5, "sd2neg": 4.1, "sd1neg": 4.5, "median": 4.9, "sd1": 5.4, "sd2": 6.3, "sd3": 7.3 },
              { "length": 56.0, "sd3neg": 3.6, "sd2neg": 4.2, "sd1neg": 4.6, "median": 5.0, "sd1": 5.6, "sd2": 6.5, "sd3": 7.5 },
              { "length": 56.5, "sd3neg": 3.7, "sd2neg": 4.3, "sd1neg": 4.8, "median": 5.2, "sd1": 5.7, "sd2": 6.6, "sd3": 7.6 },
              { "length": 57.0, "sd3neg": 3.8, "sd2neg": 4.4, "sd1neg": 4.9, "median": 5.3, "sd1": 5.8, "sd2": 6.8, "sd3": 7.8 },
              { "length": 57.5, "sd3neg": 3.9, "sd2neg": 4.5, "sd1neg": 5.0, "median": 5.4, "sd1": 5.9, "sd2": 6.9, "sd3": 8.0 },
              { "length": 58.0, "sd3neg": 4.0, "sd2neg": 4.6, "sd1neg": 5.1, "median": 5.5, "sd1": 6.0, "sd2": 7.1, "sd3": 8.1 },
              { "length": 58.5, "sd3neg": 4.1, "sd2neg": 4.7, "sd1neg": 5.2, "median": 5.7, "sd1": 6.2, "sd2": 7.2, "sd3": 8.3 },
              { "length": 59.0, "sd3neg": 4.2, "sd2neg": 4.8, "sd1neg": 5.4, "median": 5.8, "sd1": 6.3, "sd2": 7.4, "sd3": 8.5 },
              { "length": 59.5, "sd3neg": 4.3, "sd2neg": 4.9, "sd1neg": 5.5, "median": 5.9, "sd1": 6.4, "sd2": 7.5, "sd3": 8.7 },
              { "length": 60.0, "sd3neg": 4.4, "sd2neg": 5.0, "sd1neg": 5.6, "median": 6.0, "sd1": 6.5, "sd2": 7.7, "sd3": 8.8 },
              { "length": 60.5, "sd3neg": 4.5, "sd2neg": 5.1, "sd1neg": 5.7, "median": 6.2, "sd1": 6.7, "sd2": 7.8, "sd3": 9.0 },
              { "length": 61.0, "sd3neg": 4.6, "sd2neg": 5.2, "sd1neg": 5.8, "median": 6.3, "sd1": 6.8, "sd2": 8.0, "sd3": 9.2 },
              { "length": 61.5, "sd3neg": 4.7, "sd2neg": 5.3, "sd1neg": 5.9, "median": 6.4, "sd1": 6.9, "sd2": 8.1, "sd3": 9.3 },
              { "length": 62.0, "sd3neg": 4.8, "sd2neg": 5.4, "sd1neg": 6.0, "median": 6.5, "sd1": 7.0, "sd2": 8.3, "sd3": 9.5 },
              { "length": 62.5, "sd3neg": 4.9, "sd2neg": 5.5, "sd1neg": 6.2, "median": 6.7, "sd1": 7.2, "sd2": 8.4, "sd3": 9.7 },
              { "length": 63.0, "sd3neg": 5.0, "sd2neg": 5.6, "sd1neg": 6.3, "median": 6.8, "sd1": 7.3, "sd2": 8.6, "sd3": 9.8 },
              { "length": 63.5, "sd3neg": 5.1, "sd2neg": 5.7, "sd1neg": 6.4, "median": 6.9, "sd1": 7.4, "sd2": 8.7, "sd3": 10.0 },
              { "length": 64.0, "sd3neg": 5.2, "sd2neg": 5.8, "sd1neg": 6.5, "median": 7.0, "sd1": 7.5, "sd2": 8.9, "sd3": 10.2 },
              { "length": 64.5, "sd3neg": 5.3, "sd2neg": 5.9, "sd1neg": 6.6, "median": 7.1, "sd1": 7.6, "sd2": 9.0, "sd3": 10.3 },
              { "length": 65.0, "sd3neg": 5.4, "sd2neg": 6.0, "sd1neg": 6.7, "median": 7.3, "sd1": 7.8, "sd2": 9.2, "sd3": 10.5 },
              { "length": 65.5, "sd3neg": 5.5, "sd2neg": 6.1, "sd1neg": 6.8, "median": 7.4, "sd1": 7.9, "sd2": 9.3, "sd3": 10.7 },
              { "length": 66.0, "sd3neg": 5.6, "sd2neg": 6.2, "sd1neg": 6.9, "median": 7.5, "sd1": 8.0, "sd2": 9.5, "sd3": 10.9 },
              { "length": 66.5, "sd3neg": 5.7, "sd2neg": 6.3, "sd1neg": 7.0, "median": 7.6, "sd1": 8.2, "sd2": 9.6, "sd3": 11.0 },
              { "length": 67.0, "sd3neg": 5.8, "sd2neg": 6.4, "sd1neg": 7.1, "median": 7.7, "sd1": 8.3, "sd2": 9.8, "sd3": 11.2 },
              { "length": 67.5, "sd3neg": 5.9, "sd2neg": 6.5, "sd1neg": 7.2, "median": 7.9, "sd1": 8.4, "sd2": 9.9, "sd3": 11.4 },
              { "length": 68.0, "sd3neg": 6.0, "sd2neg": 6.6, "sd1neg": 7.4, "median": 8.0, "sd1": 8.6, "sd2": 10.1, "sd3": 11.6 },
              { "length": 68.5, "sd3neg": 6.1, "sd2neg": 6.7, "sd1neg": 7.5, "median": 8.1, "sd1": 8.7, "sd2": 10.3, "sd3": 11.7 },
              { "length": 69.0, "sd3neg": 6.2, "sd2neg": 6.8, "sd1neg": 7.6, "median": 8.2, "sd1": 8.8, "sd2": 10.4, "sd3": 11.9 },
              { "length": 69.5, "sd3neg": 6.3, "sd2neg": 6.9, "sd1neg": 7.7, "median": 8.3, "sd1": 8.9, "sd2": 10.6, "sd3": 12.1 },
              { "length": 70.0, "sd3neg": 6.4, "sd2neg": 7.0, "sd1neg": 7.8, "median": 8.4, "sd1": 9.1, "sd2": 10.7, "sd3": 12.3 },
              { "length": 70.5, "sd3neg": 6.5, "sd2neg": 7.1, "sd1neg": 7.9, "median": 8.6, "sd1": 9.2, "sd2": 10.9, "sd3": 12.5 },
              { "length": 71.0, "sd3neg": 6.6, "sd2neg": 7.2, "sd1neg": 8.0, "median": 8.7, "sd1": 9.3, "sd2": 11.0, "sd3": 12.7 },
              { "length": 71.5, "sd3neg": 6.7, "sd2neg": 7.3, "sd1neg": 8.1, "median": 8.8, "sd1": 9.5, "sd2": 11.2, "sd3": 12.9 },
              { "length": 72.0, "sd3neg": 6.8, "sd2neg": 7.4, "sd1neg": 8.2, "median": 8.9, "sd1": 9.6, "sd2": 11.3, "sd3": 13.1 },
              { "length": 72.5, "sd3neg": 6.9, "sd2neg": 7.5, "sd1neg": 8.3, "median": 9.0, "sd1": 9.7, "sd2": 11.5, "sd3": 13.3 },
              { "length": 73.0, "sd3neg": 7.0, "sd2neg": 7.6, "sd1neg": 8.4, "median": 9.2, "sd1": 9.9, "sd2": 11.6, "sd3": 13.5 },
              { "length": 73.5, "sd3neg": 7.1, "sd2neg": 7.7, "sd1neg": 8.5, "median": 9.3, "sd1": 10.0, "sd2": 11.8, "sd3": 13.7 },
              { "length": 74.0, "sd3neg": 7.2, "sd2neg": 7.8, "sd1neg": 8.6, "median": 9.4, "sd1": 10.1, "sd2": 12.0, "sd3": 13.9 },
              { "length": 74.5, "sd3neg": 7.3, "sd2neg": 7.9, "sd1neg": 8.7, "median": 9.6, "sd1": 10.3, "sd2": 12.1, "sd3": 14.1 },
              { "length": 75.0, "sd3neg": 7.4, "sd2neg": 8.0, "sd1neg": 8.8, "median": 9.7, "sd1": 10.4, "sd2": 12.3, "sd3": 14.3 },
              { "length": 75.5, "sd3neg": 7.5, "sd2neg": 8.1, "sd1neg": 8.9, "median": 9.8, "sd1": 10.6, "sd2": 12.5, "sd3": 14.6 },
              { "length": 76.0, "sd3neg": 7.6, "sd2neg": 8.3, "sd1neg": 9.1, "median": 10.0, "sd1": 10.7, "sd2": 12.7, "sd3": 14.8 },
              { "length": 76.5, "sd3neg": 7.7, "sd2neg": 8.4, "sd1neg": 9.2, "median": 10.1, "sd1": 10.9, "sd2": 12.9, "sd3": 15.0 },
              { "length": 77.0, "sd3neg": 7.8, "sd2neg": 8.5, "sd1neg": 9.3, "median": 10.3, "sd1": 11.0, "sd2": 13.0, "sd3": 15.2 },
              { "length": 77.5, "sd3neg": 7.9, "sd2neg": 8.6, "sd1neg": 9.4, "median": 10.4, "sd1": 11.2, "sd2": 13.2, "sd3": 15.4 },
              { "length": 78.0, "sd3neg": 8.0, "sd2neg": 8.7, "sd1neg": 9.5, "median": 10.6, "sd1": 11.3, "sd2": 13.4, "sd3": 15.6 },
              { "length": 78.5, "sd3neg": 8.1, "sd2neg": 8.8, "sd1neg": 9.6, "median": 10.7, "sd1": 11.5, "sd2": 13.6, "sd3": 15.9 },
              { "length": 79.0, "sd3neg": 8.2, "sd2neg": 8.9, "sd1neg": 9.7, "median": 10.9, "sd1": 11.7, "sd2": 13.7, "sd3": 16.1 },
              { "length": 79.5, "sd3neg": 8.3, "sd2neg": 9.0, "sd1neg": 9.8, "median": 11.0, "sd1": 11.8, "sd2": 13.9, "sd3": 16.3 },
              { "length": 80.0, "sd3neg": 8.4, "sd2neg": 9.1, "sd1neg": 9.9, "median": 11.2, "sd1": 12.0, "sd2": 14.1, "sd3": 16.6 },
              { "length": 80.5, "sd3neg": 8.5, "sd2neg": 9.2, "sd1neg": 10.1, "median": 11.3, "sd1": 12.1, "sd2": 14.3, "sd3": 16.8 },
              { "length": 81.0, "sd3neg": 8.6, "sd2neg": 9.3, "sd1neg": 10.2, "median": 11.5, "sd1": 12.3, "sd2": 14.5, "sd3": 17.1 },
              { "length": 81.5, "sd3neg": 8.7, "sd2neg": 9.4, "sd1neg": 10.3, "median": 11.6, "sd1": 12.4, "sd2": 14.7, "sd3": 17.3 },
              { "length": 82.0, "sd3neg": 8.8, "sd2neg": 9.6, "sd1neg": 10.4, "median": 11.8, "sd1": 12.6, "sd2": 14.8, "sd3": 17.6 },
              { "length": 82.5, "sd3neg": 8.9, "sd2neg": 9.7, "sd1neg": 10.6, "median": 11.9, "sd1": 12.7, "sd2": 15.0, "sd3": 17.8 },
              { "length": 83.0, "sd3neg": 9.0, "sd2neg": 9.8, "sd1neg": 10.7, "median": 12.1, "sd1": 12.9, "sd2": 15.2, "sd3": 18.1 },
              { "length": 83.5, "sd3neg": 9.1, "sd2neg": 9.9, "sd1neg": 10.8, "median": 12.2, "sd1": 13.1, "sd2": 15.4, "sd3": 18.4 },
              { "length": 84.0, "sd3neg": 9.2, "sd2neg": 10.0, "sd1neg": 10.9, "median": 12.4, "sd1": 13.3, "sd2": 15.6, "sd3": 18.6 },
              { "length": 84.5, "sd3neg": 9.3, "sd2neg": 10.1, "sd1neg": 11.1, "median": 12.5, "sd1": 13.4, "sd2": 15.8, "sd3": 18.9 },
              { "length": 85.0, "sd3neg": 9.4, "sd2neg": 10.2, "sd1neg": 11.2, "median": 12.7, "sd1": 13.6, "sd2": 16.0, "sd3": 19.2 },
              { "length": 85.5, "sd3neg": 9.5, "sd2neg": 10.3, "sd1neg": 11.3, "median": 12.8, "sd1": 13.8, "sd2": 16.2, "sd3": 19.5 },
              { "length": 86.0, "sd3neg": 9.6, "sd2neg": 10.4, "sd1neg": 11.5, "median": 13.0, "sd1": 14.0, "sd2": 16.4, "sd3": 19.7 },
              { "length": 86.5, "sd3neg": 9.7, "sd2neg": 10.5, "sd1neg": 11.6, "median": 13.1, "sd1": 14.2, "sd2": 16.7, "sd3": 20.0 },
              { "length": 87.0, "sd3neg": 9.8, "sd2neg": 10.6, "sd1neg": 11.7, "median": 13.3, "sd1": 14.4, "sd2": 16.9, "sd3": 20.3 },
              { "length": 87.5, "sd3neg": 9.9, "sd2neg": 10.7, "sd1neg": 11.8, "median": 13.4, "sd1": 14.6, "sd2": 17.1, "sd3": 20.6 },
              { "length": 88.0, "sd3neg": 10.0, "sd2neg": 10.8, "sd1neg": 12.0, "median": 13.6, "sd1": 14.8, "sd2": 17.3, "sd3": 20.9 },
              { "length": 88.5, "sd3neg": 10.1, "sd2neg": 10.9, "sd1neg": 12.1, "median": 13.7, "sd1": 14.9, "sd2": 17.6, "sd3": 21.2 },
              { "length": 89.0, "sd3neg": 10.2, "sd2neg": 11.1, "sd1neg": 12.2, "median": 13.9, "sd1": 15.1, "sd2": 17.8, "sd3": 21.4 },
              { "length": 89.5, "sd3neg": 10.4, "sd2neg": 11.2, "sd1neg": 12.4, "median": 14.0, "sd1": 15.3, "sd2": 18.0, "sd3": 21.7 },
              { "length": 90.0, "sd3neg": 10.5, "sd2neg": 11.3, "sd1neg": 12.5, "median": 14.2, "sd1": 15.5, "sd2": 18.2, "sd3": 22.0 },
              { "length": 90.5, "sd3neg": 10.6, "sd2neg": 11.4, "sd1neg": 12.7, "median": 14.3, "sd1": 15.7, "sd2": 18.4, "sd3": 22.3 },
              { "length": 91.0, "sd3neg": 10.7, "sd2neg": 11.5, "sd1neg": 12.8, "median": 14.5, "sd1": 15.9, "sd2": 18.7, "sd3": 22.6 },
              { "length": 91.5, "sd3neg": 10.9, "sd2neg": 11.7, "sd1neg": 13.0, "median": 14.6, "sd1": 16.0, "sd2": 18.9, "sd3": 22.9 },
              { "length": 92.0, "sd3neg": 11.0, "sd2neg": 11.8, "sd1neg": 13.1, "median": 14.8, "sd1": 16.2, "sd2": 19.1, "sd3": 23.2 },
              { "length": 92.5, "sd3neg": 11.1, "sd2neg": 11.9, "sd1neg": 13.3, "median": 15.0, "sd1": 16.4, "sd2": 19.4, "sd3": 23.5 },
              { "length": 93.0, "sd3neg": 11.3, "sd2neg": 12.1, "sd1neg": 13.4, "median": 15.1, "sd1": 16.6, "sd2": 19.6, "sd3": 23.8 },
              { "length": 93.5, "sd3neg": 11.4, "sd2neg": 12.2, "sd1neg": 13.6, "median": 15.3, "sd1": 16.8, "sd2": 19.9, "sd3": 24.1 },
              { "length": 94.0, "sd3neg": 11.5, "sd2neg": 12.3, "sd1neg": 13.7, "median": 15.5, "sd1": 17.0, "sd2": 20.1, "sd3": 24.4 },
              { "length": 94.5, "sd3neg": 11.7, "sd2neg": 12.5, "sd1neg": 13.9, "median": 15.6, "sd1": 17.2, "sd2": 20.3, "sd3": 24.7 },
              { "length": 95.0, "sd3neg": 11.8, "sd2neg": 12.6, "sd1neg": 14.0, "median": 15.8, "sd1": 17.4, "sd2": 20.6, "sd3": 25.0 },
              { "length": 95.5, "sd3neg": 11.9, "sd2neg": 12.7, "sd1neg": 14.2, "median": 16.0, "sd1": 17.6, "sd2": 20.8, "sd3": 25.3 },
              { "length": 96.0, "sd3neg": 12.1, "sd2neg": 12.9, "sd1neg": 14.3, "median": 16.1, "sd1": 17.8, "sd2": 21.1, "sd3": 25.7 },
              { "length": 96.5, "sd3neg": 12.2, "sd2neg": 13.0, "sd1neg": 14.5, "median": 16.3, "sd1": 18.0, "sd2": 21.3, "sd3": 26.0 },
              { "length": 97.0, "sd3neg": 12.4, "sd2neg": 13.2, "sd1neg": 14.7, "median": 16.5, "sd1": 18.2, "sd2": 21.6, "sd3": 26.3 },
              { "length": 97.5, "sd3neg": 12.5, "sd2neg": 13.3, "sd1neg": 14.8, "median": 16.7, "sd1": 18.4, "sd2": 21.8, "sd3": 26.6 },
              { "length": 98.0, "sd3neg": 12.7, "sd2neg": 13.5, "sd1neg": 15.0, "median": 16.9, "sd1": 18.7, "sd2": 22.1, "sd3": 27.0 },
              { "length": 98.5, "sd3neg": 12.8, "sd2neg": 13.6, "sd1neg": 15.2, "median": 17.1, "sd1": 18.9, "sd2": 22.4, "sd3": 27.3 },
              { "length": 99.0, "sd3neg": 13.0, "sd2neg": 13.8, "sd1neg": 15.3, "median": 17.3, "sd1": 19.1, "sd2": 22.6, "sd3": 27.6 },
              { "length": 99.5, "sd3neg": 13.1, "sd2neg": 13.9, "sd1neg": 15.5, "median": 17.5, "sd1": 19.3, "sd2": 22.9, "sd3": 28.0 },
              { "length": 100.0, "sd3neg": 13.3, "sd2neg": 14.1, "sd1neg": 15.7, "median": 17.7, "sd1": 19.6, "sd2": 23.2, "sd3": 28.3 },
              { "length": 100.5, "sd3neg": 13.5, "sd2neg": 14.2, "sd1neg": 15.9, "median": 17.9, "sd1": 19.8, "sd2": 23.5, "sd3": 28.7 },
              { "length": 101.0, "sd3neg": 13.6, "sd2neg": 14.4, "sd1neg": 16.0, "median": 18.1, "sd1": 20.0, "sd2": 23.8, "sd3": 29.0 },
              { "length": 101.5, "sd3neg": 13.8, "sd2neg": 14.5, "sd1neg": 16.2, "median": 18.3, "sd1": 20.3, "sd2": 24.0, "sd3": 29.4 },
              { "length": 102.0, "sd3neg": 14.0, "sd2neg": 14.7, "sd1neg": 16.4, "median": 18.5, "sd1": 20.5, "sd2": 24.3, "sd3": 29.7 },
              { "length": 102.5, "sd3neg": 14.1, "sd2neg": 14.8, "sd1neg": 16.6, "median": 18.7, "sd1": 20.7, "sd2": 24.6, "sd3": 30.1 },
              { "length": 103.0, "sd3neg": 14.3, "sd2neg": 15.0, "sd1neg": 16.7, "median": 18.9, "sd1": 21.0, "sd2": 24.9, "sd3": 30.4 },
              { "length": 103.5, "sd3neg": 14.5, "sd2neg": 15.1, "sd1neg": 16.9, "median": 19.1, "sd1": 21.2, "sd2": 25.2, "sd3": 30.8 },
              { "length": 104.0, "sd3neg": 14.6, "sd2neg": 15.3, "sd1neg": 17.1, "median": 19.3, "sd1": 21.4, "sd2": 25.5, "sd3": 31.1 },
              { "length": 104.5, "sd3neg": 14.8, "sd2neg": 15.4, "sd1neg": 17.3, "median": 19.5, "sd1": 21.7, "sd2": 25.8, "sd3": 31.5 },
              { "length": 105.0, "sd3neg": 15.0, "sd2neg": 15.6, "sd1neg": 17.4, "median": 19.7, "sd1": 21.9, "sd2": 26.1, "sd3": 31.8 },
              { "length": 105.5, "sd3neg": 15.2, "sd2neg": 15.8, "sd1neg": 17.6, "median": 19.9, "sd1": 22.1, "sd2": 26.4, "sd3": 32.2 },
              { "length": 106.0, "sd3neg": 15.3, "sd2neg": 15.9, "sd1neg": 17.8, "median": 20.1, "sd1": 22.4, "sd2": 26.7, "sd3": 32.6 },
              { "length": 106.5, "sd3neg": 15.5, "sd2neg": 16.1, "sd1neg": 18.0, "median": 20.3, "sd1": 22.6, "sd2": 27.0, "sd3": 33.0 },
              { "length": 107.0, "sd3neg": 15.7, "sd2neg": 16.2, "sd1neg": 18.2, "median": 20.5, "sd1": 22.9, "sd2": 27.3, "sd3": 33.4 },
              { "length": 107.5, "sd3neg": 15.9, "sd2neg": 16.4, "sd1neg": 18.3, "median": 20.8, "sd1": 23.1, "sd2": 27.6, "sd3": 33.8 },
              { "length": 108.0, "sd3neg": 16.1, "sd2neg": 16.6, "sd1neg": 18.5, "median": 21.0, "sd1": 23.4, "sd2": 27.9, "sd3": 34.2 },
              { "length": 108.5, "sd3neg": 16.3, "sd2neg": 16.7, "sd1neg": 18.7, "median": 21.2, "sd1": 23.6, "sd2": 28.2, "sd3": 34.6 },
              { "length": 109.0, "sd3neg": 16.5, "sd2neg": 16.9, "sd1neg": 18.9, "median": 21.4, "sd1": 23.9, "sd2": 28.5, "sd3": 35.0 },
              { "length": 109.5, "sd3neg": 16.6, "sd2neg": 17.1, "sd1neg": 19.1, "median": 21.7, "sd1": 24.1, "sd2": 28.8, "sd3": 35.4 },
              { "length": 110.0, "sd3neg": 16.8, "sd2neg": 17.2, "sd1neg": 19.3, "median": 22.0, "sd1": 24.4, "sd2": 29.2, "sd3": 35.8 }
            ]
          }
        ],
        "balita":[
          {
            "L": [
              { "length": 45.0, "sd3neg": 1.9, "sd2neg": 2.1, "sd1neg": 2.4, "median": 2.8, "sd1": 3.2, "sd2": 3.7, "sd3": 4.2 },
              { "length": 45.5, "sd3neg": 2.0, "sd2neg": 2.2, "sd1neg": 2.5, "median": 2.9, "sd1": 3.3, "sd2": 3.8, "sd3": 4.4 },
              { "length": 46.0, "sd3neg": 2.0, "sd2neg": 2.3, "sd1neg": 2.6, "median": 3.0, "sd1": 3.4, "sd2": 3.9, "sd3": 4.5 },
              { "length": 46.5, "sd3neg": 2.1, "sd2neg": 2.4, "sd1neg": 2.7, "median": 3.1, "sd1": 3.5, "sd2": 4.0, "sd3": 4.6 },
              { "length": 47.0, "sd3neg": 2.1, "sd2neg": 2.5, "sd1neg": 2.8, "median": 3.2, "sd1": 3.6, "sd2": 4.1, "sd3": 4.8 },
              ...
              { "length": 110.0, "sd3neg": 12.6, "sd2neg": 13.9, "sd1neg": 15.3, "median": 17.0, "sd1": 19.0, "sd2": 21.3, "sd3": 23.9 }
            ],
            "P": [
              { "length": 45.0, "sd3neg": 1.8, "sd2neg": 2.0, "sd1neg": 2.3, "median": 2.7, "sd1": 3.1, "sd2": 3.6, "sd3": 4.1 },
              { "length": 45.5, "sd3neg": 1.9, "sd2neg": 2.1, "sd1neg": 2.4, "median": 2.8, "sd1": 3.2, "sd2": 3.7, "sd3": 4.3 },
              { "length": 46.0, "sd3neg": 2.0, "sd2neg": 2.2, "sd1neg": 2.5, "median": 2.9, "sd1": 3.3, "sd2": 3.8, "sd3": 4.4 },
              { "length": 46.5, "sd3neg": 2.0, "sd2neg": 2.3, "sd1neg": 2.6, "median": 3.0, "sd1": 3.4, "sd2": 3.9, "sd3": 4.5 },
              { "length": 47.0, "sd3neg": 2.1, "sd2neg": 2.4, "sd1neg": 2.7, "median": 3.1, "sd1": 3.5, "sd2": 4.0, "sd3": 4.7 },
              ...
              { "length": 110.0, "sd3neg": 12.0, "sd2neg": 13.2, "sd1neg": 14.5, "median": 16.1, "sd1": 17.9, "sd2": 20.0, "sd3": 22.3 }
            ]
          }
        ]
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

    return {
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
    /* formatToNamaBulan(periode) {
      // input: "2025-03"
      const [year, month] = periode.split('-')

      const bulanNama = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
      ]

      const nama = bulanNama[parseInt(month) - 1]

      return `${nama} ${year}`  // output: "Maret 2025"
    },

    getRangeFromBulanTahun(bulanTahun) {
      // input: "Maret 2025"
      const [bulanNama, tahun] = bulanTahun.split(" ")

      const bulanMap = {
        Januari: 0, Februari: 1, Maret: 2, April: 3, Mei: 4, Juni: 5,
        Juli: 6, Agustus: 7, September: 8, Oktober: 9, November: 10, Desember: 11
      }

      const monthIndex = bulanMap[bulanNama]

      const start = new Date(tahun, monthIndex, 1)
      const end = new Date(tahun, monthIndex + 1, 0)

      return {
        startDate: start.toISOString().slice(0, 10),
        endDate: end.toISOString().slice(0, 10)
      }
    }, */
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
    calcSD(L, M, S, sd) {
      if (L === 0) {
        return M * Math.exp(S * sd);
      }
      return M * Math.pow(1 + L * S * sd, 1 / L);
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

      //console.log(kelahiranList[0]);

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
      })

      //this.renderKMSChart()
      //console.log('selectedAnak detail:', this.selectedAnak)
    },
    renderKMSChart(type) {
      const data = this.selectedAnak.riwayat_penimbangan || []

      const labels = data.map(d => d.usia)

      // ============================
      // Ambil SD sesuai grafik
      // ============================
      let minus3SD, minus2SD, minus1SD, medianSD, plus1SD, plus2SD, plus3SD, real

      if (type === 'bbu') {
        minus3SD = data.map(d => d.sd_bbu_min3)
        minus2SD = data.map(d => d.sd_bbu_min2)
        minus1SD = data.map(d => d.sd_bbu_min1)
        medianSD = data.map(d => d.sd_bbu_med)
        plus1SD  = data.map(d => d.sd_bbu_plus1)
        plus2SD  = data.map(d => d.sd_bbu_plus2)
        plus3SD  = data.map(d => d.sd_bbu_plus3)
        real     = data.map(d => d.bb)
      }

      if (type === 'tbu') {
        minus3SD = data.map(d => d.sd_tbu_min3)
        minus2SD = data.map(d => d.sd_tbu_min2)
        minus1SD = data.map(d => d.sd_tbu_min1)
        medianSD = data.map(d => d.sd_tbu_med)
        plus1SD  = data.map(d => d.sd_tbu_plus1)
        plus2SD  = data.map(d => d.sd_tbu_plus2)
        plus3SD  = data.map(d => d.sd_tbu_plus3)
        real     = data.map(d => d.tb)
      }

      if (type === 'bbtb') {
        minus3SD = data.map(d => d.sd_bbtb_min3)
        minus2SD = data.map(d => d.sd_bbtb_min2)
        minus1SD = data.map(d => d.sd_bbtb_min1)
        medianSD = data.map(d => d.sd_bbtb_med)
        plus1SD  = data.map(d => d.sd_bbtb_plus1)
        plus2SD  = data.map(d => d.sd_bbtb_plus2)
        plus3SD  = data.map(d => d.sd_bbtb_plus3)
        real     = data.map(d => d.bb)
      }

      // ============================
      // Destroy chart lama
      // ============================
      if (this.chartInstance) {
        this.chartInstance.destroy()
      }

      const ctx = this.$refs[`chart_${type}`].getContext('2d')

      // ============================
      // Dataset Model KMS WHO
      // ============================
      const datasets = [

        // ===== ZONA MERAH (-3 → -2) =====
        {
          data: minus3SD,
          backgroundColor: 'rgba(255, 0, 0, 0.12)',
          borderWidth: 0,
          pointRadius: 0,
          fill: '+1'
        },

        {
          label: '-2 SD',
          data: minus2SD,
          borderColor: '#cc0000',
          borderWidth: 1,
          pointRadius: 0,
          fill: false
        },

        // ===== ZONA KUNING (-2 → -1) =====
        {
          data: minus2SD,
          backgroundColor: 'rgba(255, 193, 7, 0.18)',
          borderWidth: 0,
          pointRadius: 0,
          fill: '+1'
        },

        {
          label: '-1 SD',
          data: minus1SD,
          borderColor: '#e6a800',
          borderWidth: 1,
          pointRadius: 0,
          fill: false
        },

        // ===== ZONA HIJAU (-1 → +1) =====
        {
          data: minus1SD,
          backgroundColor: 'rgba(0, 200, 83, 0.18)',
          borderWidth: 0,
          pointRadius: 0,
          fill: '+2' // sampai +1 SD
        },

        {
          label: '0 SD',
          data: medianSD,
          borderColor: '#4caf50',
          borderWidth: 1,
          pointRadius: 0,
        },

        {
          label: '+1 SD',
          data: plus1SD,
          borderColor: '#ffcc00',
          borderWidth: 1,
          pointRadius: 0,
          fill: false
        },

        // ===== Tambahan Garis SD Atas =====
        {
          label: '+2 SD',
          data: plus2SD,
          borderColor: '#999',
          borderWidth: 1,
          pointRadius: 0,
        },
        {
          label: '+3 SD',
          data: plus3SD,
          borderColor: '#777',
          borderWidth: 1,
          pointRadius: 0,
        },

        // ===== GARIS ANAK =====
        {
          label: (type === 'bbu' ? 'BB' : type === 'tbu' ? 'TB' : 'BB/TB'),
          data: real,
          borderColor: '#0066ff',
          backgroundColor: '#0066ff',
          borderWidth: 3,
          pointRadius: 5,
          tension: 0.3
        }
      ]

      // ============================
      // CREATE CHART
      // ============================
      this.chartInstance = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
              labels: { usePointStyle: true }
            }
          },
          scales: {
            y: {
              beginAtZero: false,
              grid: { color: '#eee' }
            },
            x: {
              grid: { color: '#eee' }
            }
          }
        }
      })
    }

    /* renderKMSChart() {
      if (!this.selectedAnak?.riwayat_penimbangan?.length) return

      this.$nextTick(() => {
        const riwayat = this.selectedAnak.riwayat_penimbangan.sort((a, b) =>
          a.tanggal.localeCompare(b.tanggal),
        )
        if (!riwayat.length) return

        const gender = this.selectedAnak.gender
        const lastRecord = riwayat[riwayat.length - 1]
        const usiaAnak = Number(this.selectedAnak.usia)
        const bbAnak = Number(lastRecord.bb)
        const tbAnak = Number(lastRecord.tb)

        const wfa = gender === 'L' ? this.wfaBoys : this.wfaGirls
        const hfa = gender === 'L' ? this.hfaBoys : this.hfaGirls
        const C = this.kmsColors

        // === Utility warna → RGBA ===
        this.hexA = (hex, alpha) => {
          const bigint = parseInt(hex.replace('#', ''), 16)
          const r = (bigint >> 16) & 255
          const g = (bigint >> 8) & 255
          const b = bigint & 255
          return `rgba(${r},${g},${b},${alpha})`
        }

        const expandWH = (arr) => ({
          tinggi: arr.map(d => d.h),
          sd_3: arr.map(d => this.calcSD(d.L, d.M, d.S, -3)),
          sd_2: arr.map(d => this.calcSD(d.L, d.M, d.S, -2)),
          sd_1: arr.map(d => this.calcSD(d.L, d.M, d.S, -1)),
          median: arr.map(d => this.calcSD(d.L, d.M, d.S, 0)),
          sd1: arr.map(d => this.calcSD(d.L, d.M, d.S, 1)),
          sd2: arr.map(d => this.calcSD(d.L, d.M, d.S, 2)),
          sd3: arr.map(d => this.calcSD(d.L, d.M, d.S, 3)),
        })


        // === Ekspansi WHO dataset ===
        const expandWHO = (arr) => ({
          usia: arr.map(d => d.m),
          sd_3: arr.map(d => this.calcSD(d.L, d.M, d.S, -3)),
          sd_2: arr.map(d => this.calcSD(d.L, d.M, d.S, -2)),
          sd_1: arr.map(d => this.calcSD(d.L, d.M, d.S, -1)),
          median: arr.map(d => this.calcSD(d.L, d.M, d.S, 0)),
          sd1: arr.map(d => this.calcSD(d.L, d.M, d.S, 1)),
          sd2: arr.map(d => this.calcSD(d.L, d.M, d.S, 2)),
          sd3: arr.map(d => this.calcSD(d.L, d.M, d.S, 3)),
        })


        const bbData = expandWHO(wfa)
        const tbData = expandWHO(hfa)
        // eslint-disable-next-line no-undef
        const bbTbData = expandWH(gender === 'L' ? this.whfaBoys : this.whfaGirls)

        // === Kurva area (dengan warna) ===
        const makeDatasets = (D, C) => [
          {
            label: '-3SD',
            data: D.sd_3,
            borderColor: C.bottom,
            backgroundColor: this.hexA(C.bottom, 0.2),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 1,
          },
          {
            label: '-2SD',
            data: D.sd_2,
            borderColor: C.midBottom,
            backgroundColor: this.hexA(C.midBottom, 0.2),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 2,
          },
          {
            label: '-1SD',
            data: D.sd_1,
            borderColor: C.mid,
            backgroundColor: this.hexA(C.mid, 0.2),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 3,
          },
          {
            label: 'Median',
            data: D.median,
            borderColor: C.midMed,
            backgroundColor: this.hexA(C.midMed, 0.15),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 4,
          },
          {
            label: '+1SD',
            data: D.sd1,
            borderColor: C.midTop,
            backgroundColor: this.hexA(C.midTop, 0.15),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 5,
          },
          {
            label: '+2SD',
            data: D.sd2,
            borderColor: C.top,
            backgroundColor: this.hexA(C.top, 0.15),
            fill: '+1',
            tension: 0.2,
            pointRadius: 0,
            order: 6,
          },
          {
            label: '+3SD',
            data: D.sd3,
            borderColor: C.top,
            backgroundColor: this.hexA(C.top, 0.1),
            fill: false,
            tension: 0.2,
            pointRadius: 0,
            order: 7,
          },
        ]

        // === Titik anak ===
        const makePointHeight = (D, tinggi, nilai) => {
          let nearestIndex = 0
          let minDiff = Infinity
          D.tinggi.forEach((t, i) => {
            const diff = Math.abs(t - tinggi)
            if (diff < minDiff) {
              minDiff = diff
              nearestIndex = i
            }
          })

          const pointData = Array(D.tinggi.length).fill(null)
          pointData[nearestIndex] = nilai

          return {
            label: 'Anak',
            data: pointData,
            borderColor: '#fff',
            backgroundColor: gender === 'L' ? '#007bff' : '#ff4b5c',
            pointRadius: 8,
            borderWidth: 2,
            pointStyle: 'circle',
            showLine: false,
            order: 9999,
          }
        }

        // eslint-disable-next-line no-unused-vars
        const makePoint = (D, usiaAnak, nilai, labelY) => {
          // Cari usia terdekat
          let nearestIndex = 0
          let minDiff = Infinity
          D.usia.forEach((u, i) => {
            const diff = Math.abs(u - usiaAnak)
            if (diff < minDiff) {
              minDiff = diff
              nearestIndex = i
            }
          })

          // Data titik tunggal
          const pointData = Array(D.usia.length).fill(null)
          pointData[nearestIndex] = nilai

          return {
            label: 'Anak',
            data: pointData,
            borderColor: '#fff',
            backgroundColor: gender === 'L' ? '#007bff' : '#ff4b5c',
            pointRadius: 8,
            borderWidth: 2,
            pointStyle: 'circle',
            showLine: false,
            clip: false, // penting biar tidak terpotong oleh area
            order: 9999, // paling depan
            z: 9999,
          }
        }

        // === Chart Builder ===
        const buildChart = (ctx, dataObj, labelY, nilaiAnak, usiaAnak, minY, maxY) => {
          return new Chart(ctx, {
            type: 'line',
            data: {
              labels: dataObj.usia,
              datasets: [
                ...makeDatasets(dataObj, C),
                makePoint(dataObj, usiaAnak, nilaiAnak, labelY),
              ],
            },
            options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                legend: { display: false },
                datalabels: { display: false },
                tooltip: {
                  backgroundColor: 'rgba(0,0,0,0.8)',
                  titleFont: { weight: 'bold', size: 13 },
                  bodyFont: { size: 12 },
                  padding: 8,
                  displayColors: false,
                  callbacks: {
                    label: () => `Umur: ${usiaAnak} bln, ${labelY}: ${nilaiAnak}`,
                  },
                },
              },
              interaction: {
                mode: 'nearest',
                intersect: false,
              },
              elements: {
                line: {
                  borderWidth: 2,
                },
                point: {
                  radius: 0,
                },
              },
              scales: {
                x: {
                  title: { display: true, text: 'Umur (bulan)' },
                  min: 0,
                  max: 60,
                },
                y: {
                  title: { display: true, text: labelY },
                  min: minY,
                  max: maxY,
                },
              },
            },
          })
        }

        // === BB/U Chart ===
        const ctxBB = this.$refs.chartBB?.getContext('2d')
        if (ctxBB) {
          if (this.chartBB) this.chartBB.destroy()
          this.chartBB = buildChart(ctxBB, bbData, 'Berat Badan (kg)', bbAnak, usiaAnak, 0, 25)
        }

        // === TB/U Chart ===
        const ctxTB = this.$refs.chartTB?.getContext('2d')
        if (ctxTB) {
          if (this.chartTB) this.chartTB.destroy()
          this.chartTB = buildChart(ctxTB, tbData, 'Tinggi Badan (cm)', tbAnak, usiaAnak, 45, 120)
        }

        // === BB/TB Chart ===
        const ctxBBTB = this.$refs.chartBBTB?.getContext('2d')
          if (ctxBBTB) {
            if (this.chartBBTB) this.chartBBTB.destroy()

            this.chartBBTB = new Chart(ctxBBTB, {
              type: 'line',
              data: {
                labels: bbTbData.tinggi,
                datasets: [
                  ...makeDatasets(bbTbData, C),
                  makePointHeight(bbTbData, tbAnak, bbAnak) // tinggi → bb
                ],
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { display: false },
                  datalabels:{ display: false },
                  tooltip: {
                    callbacks: {
                      label: () => `TB: ${tbAnak} cm, BB: ${bbAnak} kg`,
                    },
                  },
                },
                scales: {
                  x: {
                    title: { display: true, text: 'Tinggi Badan (cm)' },
                  },
                  y: {
                    title: { display: true, text: 'Berat Badan (kg)' },
                    min: 0,
                    max: 25,
                  },
                },
              },
            })
          }

      })
    }, */
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
