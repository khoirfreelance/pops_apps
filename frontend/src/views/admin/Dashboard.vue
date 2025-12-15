<!-- eslint-disable vue/no-use-v-if-with-v-for -->
<template>
  <div class="wrapper">
    <!-- 🔄 Spinner Overlay -->
    <transition name="fade">
      <div v-if="isLoading" class="spinner-overlay d-flex justify-content-center align-items-center">
        <div class="spinner-border text-primary" role="status" style="width: 4rem; height: 4rem">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    </transition>

    <!-- Header -->
    <HeaderAdmin />

    <div class="content flex-grow-1 d-flex flex-column flex-md-row" :class="{
      'sidebar-collapsed': isCollapsed,
      'sidebar-expanded': !isCollapsed,
    }">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />

      <div class="flex-grow-1 d-flex flex-column">
        <!-- Content -->
        <div class="py-4 container-fluid">
          <!-- Welcome Card -->
          <Welcome />

          <!-- Statistic Cards -->
          <div class="mt-3">
            <div class="row justify-content-center g-2">
              <div v-for="(stat, index) in stats" :key="index"
                class="col-xl-1_9 col-lg-2_custom col-md-3 col-sm-3 col-6">
                <div class="stat-card shadow-sm rounded h-100">
                  <h6 class="text-muted pt-2 ps-2" style="font-size: 16px;">{{ stat.title }}</h6>
                  <div class="card-body d-flex align-items-center justify-content-between px-2">
                    <!-- Text -->
                    <h4 class="fw-bold mb-0">{{ stat.value }}</h4>
                    <!-- Icon -->
                    <div class="icon-wrap d-flex align-items-center justify-content-center mb-1">
                      <i :class="[stat.icon]"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Judul Laporan -->
          <div class="text-center mt-4">
            <div class="bg-additional text-white py-1 px-4 d-inline-block rounded-top">
              <div class="title mb-0 text-capitalize fw-bold" style="font-size: 23px">
                Laporan Status Gizi Desa {{ kelurahan }} Periode {{ periodeLabel }}
              </div>
            </div>
          </div>

          <!-- <div class="d-md-none mt-2 sticky-filter">
            <button
              class="btn btn-success filter-floating-btn d-md-none"
              @click="showFilterMobile = !showFilterMobile"
            >
              <i class="bi bi-funnel"></i>
            </button>
          </div> -->

          <!-- Filter Form -->
          <div class="bg-light border rounded-bottom shadow-sm px-4 py-3 mt-0 d-none d-md-block sticky-filter"
            v-show="!isMobile || showFilterMobile">
            <form class="row g-3 align-items-end" @submit.prevent="applyFilter">
              <!-- Kelurahan/Desa -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label fs-md-1" style="font-weight: 600;">Kel/Desa</label>
                <select v-model="filters.kelurahan" class="form-select text-muted small uniform-input" disabled>
                  <option :value="kelurahan" class="small">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">Posyandu</label>
                <select v-model="filters.posyandu" class="form-select text-muted uniform-input"
                  @change="handlePosyanduChange">
                  <option value="">All</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">RW</label>
                <select v-model="filters.rw" class="form-select text-muted uniform-input" @change="handleRWChange"
                  :disabled="rwReadonly">
                  <option value="">All</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">RT</label>
                <select v-model="filters.rt" class="form-select text-muted uniform-input" :disabled="rtReadonly">
                  <option value="">All</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">Periode</label>
                <select v-model="filters.periode" class="form-select uniform-input">
                  <option value="">All</option>
                  <option v-for="p in periodeOptions" :key="p.value" :value="p.value">
                    {{ p.label }}
                  </option>
                </select>
              </div>

              <!-- Tombol Cari -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2 d-grid">
                <button type="submit" class="btn btn-gradient fw-semibold uniform-input">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
              </div>
            </form>
          </div>

          <!-- Mobile Filter -->
          <!-- Floating Button -->
          <button class="btn btn-primary filter-float-btn rounded-pill shadow-lg px-4 py-2"
            @click="mobileFilterOpen = true">
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
              <!-- Kelurahan/Desa -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label fs-md-1" style="font-weight: 600;">Kel/Desa</label>
                <select v-model="filters.kelurahan" class="form-select text-muted small uniform-input" disabled>
                  <option :value="kelurahan" class="small">{{ kelurahan }}</option>
                </select>
              </div>

              <!-- Posyandu -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">Posyandu</label>
                <select v-model="filters.posyandu" class="form-select text-muted uniform-input"
                  @change="handlePosyanduChange">
                  <option value="">All</option>
                  <option v-for="item in posyanduList" :key="item.id" :value="item.nama_posyandu">
                    {{ item.nama_posyandu }}
                  </option>
                </select>
              </div>

              <!-- RW -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">RW</label>
                <select v-model="filters.rw" class="form-select text-muted uniform-input" @change="handleRWChange"
                  :disabled="rwReadonly">
                  <option value="">All</option>
                  <option v-for="rw in rwList" :key="rw" :value="rw">{{ rw }}</option>
                </select>
              </div>

              <!-- RT -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">RT</label>
                <select v-model="filters.rt" class="form-select text-muted uniform-input" :disabled="rtReadonly">
                  <option value="">All</option>
                  <option v-for="rt in rtList" :key="rt" :value="rt">{{ rt }}</option>
                </select>
              </div>

              <!-- Periode -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2">
                <label class="form-label" style="font-weight: 600;">Periode</label>
                <select v-model="filters.periode" class="form-select uniform-input">
                  <option value="">All</option>
                  <option v-for="p in periodeOptions" :key="p.value" :value="p.value">
                    {{ p.label }}
                  </option>
                </select>
              </div>

              <!-- Tombol Cari -->
              <div class="col-12 col-sm-6 col-md-4 col-lg-4 col-xl-2 d-grid">
                <button type="submit" class="btn btn-gradient fw-semibold uniform-input">
                  <i class="bi bi-filter me-1"></i> Terapkan
                </button>
              </div>
            </form>
          </div>

          <!-- Main -->
          <div class="d-flex justify-content-center mt-4">
            <ul class="nav nav-pills d-flex flex-wrap justify-content-center gap-2 w-100" id="myTab" role="tablist"
              style="max-width: 800px">
              <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link active w-100 text-truncate" id="anak-tab" data-bs-toggle="tab"
                  data-bs-target="#anak-tab-pane" type="button" role="tab" aria-controls="anak-tab-pane"
                  aria-selected="true" @click="setMenu('anak')">
                  Status Gizi Anak
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-truncate" id="bumil-tab" data-bs-toggle="tab"
                  data-bs-target="#bumil-tab-pane" type="button" role="tab" aria-controls="bumil-tab-pane"
                  aria-selected="false" @click="setMenu('bumil')">
                  Status Kesehatan Ibu Hamil
                </button>
              </li>

              <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-truncate" id="catin-tab" data-bs-toggle="tab"
                  data-bs-target="#catin-tab-pane" type="button" role="tab" aria-controls="catin-tab-pane"
                  aria-selected="false" @click="setMenu('catin')">
                  Calon Pengantin Berisiko
                </button>
              </li>
            </ul>
          </div>
          <div class="tab-content" id="myTabContent">
            <!-- Tab Anak -->
            <div class="tab-pane fade show active" id="anak-tab-pane" role="tabpanel" tabindex="0">
              <div id="giziAnakExport">
                <div class="container-fluid px-0 my-3">
                  <!-- Row utama tanpa gutter -->
                  <div class="row gx-2">
                    <!-- RINGKASAN STATUS GIZI -->
                    <div class="col-md-12 d-flex flex-column">
                      <!-- Judul -->
                      <h2 class="ringkasan-header text-success mb-4">
                        Ringkasan Status Gizi Bulan {{ bulanIni }}
                      </h2>

                      <!-- Row isi -->
                      <div class="row g-2">
                        <!-- g-2 lebih kecil daripada gx-3 gy-3 -->

                        <!-- GIZI CARDS -->
                        <div class="col-12 col-xl-10 col-md-12">
                          <div class="row">
                            <div v-for="(item, index) in kesehatanData.anak" :key="index" class="col-6 col-lg-4 g-3">
                              <div class="card shadow-sm border-0 h-100"
                                :class="`border-start border-4 border-${item.color}`">
                                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                                  <div>
                                    <h2 class="fw-bold mb-1">{{ item.title }}</h2>
                                    <h3 class="mb-0" :class="`text-${item.color}`">
                                      {{ item.percent }}
                                    </h3>
                                  </div>
                                  <h3 class="fw-bold mb-0" :class="`text-${item.color}`">
                                    {{ item.value }}
                                  </h3>
                                </div>
                                <div class="card-footer bg-transparent border-0 pt-0">
                                  <canvas :ref="'chart-' + index" height="120"></canvas>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- TOTAL ANAK -->
                        <div class="col-12 col-xl-2 col-md-12 d-flex">
                          <div class="card h-100 shadow-sm border w-100">
                            <div class="card-body d-flex flex-column justify-content-between py-4">

                              <!-- Judul -->
                              <h2 class="fw-bold text-center mb-0">
                                Total <br> Anak Balita
                              </h2>

                              <!-- Angka besar -->
                              <h3 class="fw-bold text-success display-4 text-center my-4">
                                {{ totalAnak }}
                              </h3>

                              <!-- Icon di bawah -->
                              <div class="text-center">
                                <i class="bi bi-people-fill display-5 text-dark"></i>
                              </div>

                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>

                  <div class="row gx-2">
                    <div id="infoBoxes" class="infoBoxes col-md-12 p-3 small">
                      <div v-for="(box, idx) in infoBoxes" :key="idx" class="alert py-2 mb-2"
                        :class="`alert-${box.type}`">
                        <!-- Title + Chevron -->
                        <div class="fw-bold color-black d-flex justify-content-between align-items-center"
                          @click="toggle(idx)" style="cursor: pointer">
                          {{ box.title }}
                          <i class="bi bi-chevron-down" :class="{ rotate: box.open }"></i>
                        </div>

                        <!-- Description -->
                        <transition name="fade">
                          <div v-if="box.open" class="mt-1">
                            <span v-html="box.desc"></span>
                          </div>
                        </transition>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Pie Chart-->
                <div class="container-fluid">
                  <!-- Header -->
                  <h2 class="ringkasan-header text-success mb-4">
                    Komposisi Status Gizi Bulan {{ bulanIni }}
                  </h2>

                  <!-- ========================================= -->
                  <!-- SECTION 1: Master Charts (3 Cards) -->
                  <!-- ========================================= -->
                  <div class="row g-3 mb-4">
                    <!-- Card BB/U -->
                    <div class="col-12 col-md-6 col-xl-4">
                      <div class="card border-primary shadow-sm h-100 cursor-pointer"
                        :class="{ 'border-3': selectedChart === 'bb' }" @click="selectChart('bb')">
                        <div class="card-body p-3 text-center">
                          <h5 class="fw-bold text-primary mb-3">
                            Berat Badan / Usia
                          </h5>

                          <div style="max-width: 280px; margin: 0 auto;"
                            class="d-flex justify-content-center align-items-center">
                            <canvas ref="pieChart_bb"></canvas>
                          </div>

                          <p class="mt-3 mb-2 small text-muted">Klik Chart untuk Melihat Data</p>

                          <!-- Legend -->
                          <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mt-3">
                            <div v-for="(row, index) in dataTable_bb" :key="index"
                              class="d-flex align-items-center gap-1">
                              <i class="fa-solid fa-circle" style="font-size: 8px;" :style="{ color: row.color }"></i>
                              <span style="font-size: 10px;" class="text-additional">{{ row.status }}</span>
                            </div>
                          </div>

                          <button class="btn btn-sm btn-link text-decoration-none mt-2">
                            <i class="fa-solid fa-chevron-down"></i> Detail
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Card TB/U -->
                    <div class="col-12 col-md-6 col-xl-4">
                      <div class="card border-primary shadow-sm h-100 cursor-pointer"
                        :class="{ 'border-3': selectedChart === 'tb' }" @click="selectChart('tb')">
                        <div class="card-body p-3 text-center">
                          <h5 class="fw-bold text-primary mb-3">
                            Tinggi Badan / Usia
                          </h5>

                          <div style="max-width: 280px; margin: 0 auto;"
                            class="d-flex justify-content-center align-items-center">
                            <canvas ref="pieChart_tb"></canvas>
                          </div>

                          <p class="mt-3 mb-2 small text-muted">Klik Chart untuk Melihat Data</p>

                          <!-- Legend -->
                          <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mt-3">
                            <div v-for="(row, index) in dataTable_tb" :key="index"
                              class="d-flex align-items-center gap-1">
                              <i class="fa-solid fa-circle" style="font-size: 8px;" :style="{ color: row.color }"></i>
                              <span style="font-size: 10px;" class="text-additional">{{ row.status }}</span>
                            </div>
                          </div>

                          <button class="btn btn-sm btn-link text-decoration-none mt-2">
                            <i class="fa-solid fa-chevron-down"></i> Detail
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Card BB/TB -->
                    <div class="col-12 col-md-6 col-xl-4">
                      <div class="card border-primary shadow-sm h-100 cursor-pointer"
                        :class="{ 'border-3': selectedChart === 'bbtb' }" @click="selectChart('bbtb')">
                        <div class="card-body p-3 text-center">
                          <h5 class="fw-bold text-primary mb-3">
                            Berat Badan / Tinggi Badan
                          </h5>

                          <div style="max-width: 280px; margin: 0 auto;"
                            class="d-flex justify-content-center align-items-center">
                            <canvas ref="pieChart_status"></canvas>
                          </div>

                          <p class="mt-3 mb-2 small text-muted">Klik Chart untuk Melihat Data</p>

                          <!-- Legend -->
                          <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap mt-3">
                            <div v-for="(row, index) in dataTable_bbtb" :key="index"
                              class="d-flex align-items-center gap-1">
                              <i class="fa-solid fa-circle" style="font-size: 8px;" :style="{ color: row.color }"></i>
                              <span style="font-size: 10px;" class="text-additional">{{ row.status }}</span>
                            </div>
                          </div>

                          <button class="btn btn-sm btn-link text-decoration-none mt-2">
                            <i class="fa-solid fa-chevron-down"></i> Detail
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- ========================================= -->
                  <!-- SECTION 2: Detail Section (Conditional) -->
                  <!-- ========================================= -->
                  <transition name="slide-down">
                    <div v-if="selectedChart" class="row" id="detail-section">
                      <div class="col-12">
                        <div class="card border-success shadow-lg">
                          <div
                            class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                              <i class="fa-solid fa-chart-pie me-2"></i>
                              Detail: {{ detailTitle }}
                            </h5>
                            <button @click="closeDetail" class="btn btn-sm btn-light">
                              <i class="fa-solid fa-times"></i> Tutup
                            </button>
                          </div>

                          <div class="card-body p-4">
                            <div class="row g-4">
                              <!-- Kolom Kiri: Table -->
                              <div class="col-12 col-lg-12">
                                <div class="table-responsive">
                                  <table class="table table-hover table-bordered align-middle mb-0">
                                    <thead class="table-light">
                                      <tr>
                                        <th class="text-center" width="60">Ket</th>
                                        <th>Status</th>
                                        <th class="text-center" width="100">Jumlah</th>
                                        <th class="text-center" width="100">Persen</th>
                                        <th class="text-center" width="100">Tren</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr v-for="(row, index) in detailData" :key="index">
                                        <td class="text-center">
                                          <i class="fa-solid fa-circle" :style="{ color: row.color }"></i>
                                        </td>
                                        <td>
                                          <span class="fw-semibold">{{ row.status }}</span>
                                        </td>
                                        <td class="text-center">
                                          <span class="badge bg-primary">{{ row.jumlah }}</span>
                                        </td>
                                        <td class="text-center">
                                          <span class="fw-bold">{{ row.persen }}%</span>
                                        </td>
                                        <td class="text-center" :class="row.trenClass">
                                          <span v-if="row.tren !== '-'">
                                            <i :class="row.trenIcon"></i> {{ row.tren }}
                                          </span>
                                          <span v-else class="text-muted">-</span>
                                        </td>
                                      </tr>
                                    </tbody>
                                    <tfoot>
                                      <tr>
                                        <td colspan="5" class="text-center pt-3">
                                          <a :href="detailLink" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-chart-line me-1"></i>
                                            Lihat Grafik Selengkapnya
                                          </a>
                                        </td>
                                      </tr>
                                    </tfoot>
                                  </table>
                                </div>
                              </div>

                              <!-- Kolom Kanan: Chart Detail -->
                              <!-- <div class="col-12 col-lg-5 d-flex align-items-center justify-content-center">
                                <div style="max-width: 100%; width: 350px;">
                                  <canvas ref="pieChart_detail"></canvas>
                                </div>
                              </div> -->
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </transition>
                </div>

                <!-- Ringkasan -->
                <div class="container-fluid my-3">
                  <div class="row">
                    <div class="col-12 d-flex justify-content-between mb-2">
                      <h2 class="ringkasan-header text-primary">Anak Dengan Masalah Gizi Ganda</h2>
                    </div>

                    <!-- CARD UTAMA -->
                    <div class="col-12">
                      <div class="card shadow-sm border-0 rounded-4">
                        <!-- HEADER -->
                        <div class="text-center position-relative mb-0">
                          <h1 class="fw-bold text-white pt-2 pb-5 px-2 rounded-bottom-5 d-inline-block bg-primary"
                            style="width: 55% !important" id="text-title-card-header-h1">
                            {{ totalKasus }} dengan Masalah Gizi Ganda
                          </h1>

                          <!-- TAB NAV -->
                          <div class="container position-relative" style="margin-top: -2.5rem">
                            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                              <a href="#"
                                class="small fw-semibold rounded-pill border border-danger bg-light shadow-sm btn btn-outline-danger text-danger"
                                style="border-bottom-width: 5px !important; cursor: default;">
                                <span class="small text-danger" id="text-title-card-span">Anak belum dapat Intervensi
                                  <br />
                                  {{ totalBelum }}</span>
                              </a>

                              <a href="#"
                                class="small fw-semibold rounded-pill border border-primary bg-light shadow-sm btn btn-outline-primary text-primary"
                                style="border-bottom-width: 5px !important; cursor: default;">
                                <span class="small text-success" id="text-title-card-span">Anak sudah dapat Intervensi
                                  <br />
                                  {{ totalSudah }}</span>
                              </a>
                            </div>
                          </div>
                        </div>

                        <div class="row g-3 mt-3">

                          <!-- CHART 1 (Left) -->
                          <div class="col-12 col-md-6 col-lg-6">
                            <div class="card shadow border-0 p-3 d-flex flex-column justify-content-between h-100">
                              <h3 class="text-center text-success mb-2">
                                Jumlah anak tidak membaik<br />
                                <span class="fw-semibold">dalam {{ filterPeriode }} bulan terakhir</span>
                              </h3>
                              <div class="chart-wrapper flex-grow-1">
                                <canvas ref="lineChart"></canvas>
                              </div>
                            </div>
                          </div>

                          <!-- CHART 3 (Right) -->
                          <div class="col-12 col-md-6 col-lg-6">
                            <div class="card shadow border-0 p-3 d-flex flex-column justify-content-between h-100">
                              <h3 class="text-center text-success mb-2">
                                Top 5 Posyandu<br />
                                <span class="fw-semibold">dengan anak tidak membaik dalam {{ filterPeriode }} bulan
                                  terakhir</span>
                              </h3>
                              <div class="chart-wrapper flex-grow-1">
                                <canvas ref="barChart"></canvas>
                              </div>
                            </div>
                          </div>

                          <!-- CHART 2: DIAGRAM INTERVENSI (Pindah ke bawah, Full Width) -->
                          <div class="col-12">
                            <div class="card shadow border-0 p-4 intervensi-container">
                              <h3 class="text-center text-success mb-3">Diagram Intervensi</h3>
                              <div class="chart-wrapper-large">
                                <canvas ref="funnelChart"></canvas>
                              </div>
                            </div>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 mt-3">

                    <!-- Tombol buka/tutup -->
                    <div class="text-center my-3" @click="showTable = !showTable" style="cursor:pointer;" data-html2canvas-ignore
>
                      <span v-if="!showTable">▼ Lihat tabel anak</span>
                      <span v-else>▲ Tutup tabel anak</span>
                    </div>

                    <!-- ANIMASI TRANSITION WRAPPER -->
                    <transition name="slide" data-html2canvas-ignore>
                      <div v-show="showTable" class="table-wrapper">

                        <div class="table-responsive">
                          <table class="table table-striped table-sm align-middle">
                            <thead class="table-success">
                              <tr>
                                <th class="text-center">No</th>
                                <th class="text-center" width="300">Nama</th>
                                <th class="text-center">Jenis Intervensi</th>
                                <th class="text-center">Stunting</th>
                                <th class="text-center">Wasting</th>
                                <th class="text-center">Underweight</th>
                                <th class="text-center">BB Stagnan</th>
                                <th class="text-center">Overweight</th>
                              </tr>
                            </thead>

                            <tbody>
                              <tr v-for="(anak, i) in paginatedAnakGabungan" :key="i" class="text-center">
                                <td class="row-data-font-size">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                                <td class="row-data-font-size">{{ anak.nama }}</td>
                                <td class="text-center row-data-font-size">{{ anak.rumusan || '' }}</td>
                                <td class="row-data-font-size"><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                                <td class="row-data-font-size"><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                                <td class="row-data-font-size"><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                                <td class="row-data-font-size"><i v-if="anak.bb_sangat" class="bi bi-check2"></i></td>
                                <td class="row-data-font-size"><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                              </tr>
                            </tbody>

                            <!-- <tfoot>
                              <tr>
                                <td colspan="100%" class="text-end">
                                  <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                                    @click="exportDashboardPdf('giziAnakExport')">
                                    <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                    Export
                                  </button>
                                </td>
                              </tr>
                            </tfoot> -->
                          </table>
                        </div>

                        <!-- Pagination -->
                        <nav id="responsive-pagination">
                          <ul class="pagination justify-content-end">
                            <li class="page-item" :class="{ disabled: currentPage === 1 }">
                              <button class="page-link" @click="currentPage > 1 && currentPage--">
                                &laquo;
                              </button>
                            </li>

                            <li v-for="(page, i) in paginationNumbersAnakGabungan" :key="i" class="page-item"
                              :class="{ active: currentPage === page, disabled: page === '...' }">

                              <button class="page-link" @click="page !== '...' && (currentPage = page)">
                                {{ page }}
                              </button>
                            </li>

                            <li class="page-item" :class="{ disabled: currentPage === totalPagesAnak }">
                              <button class="page-link" @click="currentPage < totalPagesAnak && currentPage++">
                                &raquo;
                              </button>
                            </li>
                          </ul>
                        </nav>

                      </div>
                    </transition>

                  </div>
                  <div class="d-flex justify-content-end" data-html2canvas-ignore >
                    <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                      @click="exportDashboardPdf('giziAnakExport')">
                      <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                      Export
                    </button>
                  </div>
                  <!-- <div class="col-12 mt-3">
                    <div class="text-center my-3" @click="showTable = !showTable" style="cursor:pointer;">
                      <span v-if="!showTable">▼ Lihat tabel anak</span>
                      <span v-else>▲ Tutup tabel anak</span>
                    </div>
                    <div v-show="showTable" class="table-wrapper">
                      <div class="table-responsive">
                        <table class="table table-striped table-sm align-middle">
                          <thead class="table-success">
                            <tr>
                              <th class="text-center">No</th>
                              <th class="text-center" width="300">Nama</th>
                              <th class="text-center">Jenis Intervensi</th>
                              <th class="text-center">Stunting</th>
                              <th class="text-center">Wasting</th>
                              <th class="text-center">Underweight</th>
                              <th class="text-center">BB Stagnan</th>
                              <th class="text-center">Overweight</th>
                            </tr>
                          </thead>

                          <tbody>
                            <tr v-for="(anak, i) in paginatedAnakGabungan" :key="i" class="text-center">
                              <td class="row-data-font-size">{{ (currentPage - 1) * perPage + i + 1 }}</td>
                              <td class="row-data-font-size">{{ anak.nama }}</td>
                              <td class="text-center row-data-font-size">{{ anak.rumusan || '' }}</td>
                              <td class="row-data-font-size"><i v-if="anak.stunting" class="bi bi-check2"></i></td>
                              <td class="row-data-font-size"><i v-if="anak.wasting" class="bi bi-check2"></i></td>
                              <td class="row-data-font-size"><i v-if="anak.underweight" class="bi bi-check2"></i></td>
                              <td class="row-data-font-size"><i v-if="anak.bb_stagnan" class="bi bi-check2"></i></td>
                              <td class="row-data-font-size"><i v-if="anak.overweight" class="bi bi-check2"></i></td>
                            </tr>
                          </tbody>

                          <tfoot>
                            <tr>
                              <td colspan="100%" class="text-end">
                                <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                                  @click="exportDashboardPdf('giziAnakExport')">
                                  <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                  Export
                                </button>
                              </td>
                            </tr>
                          </tfoot>
                        </table>
                      </div>

                      <nav id="responsive-pagination">
                        <ul class="pagination justify-content-end">
                          <li class="page-item" :class="{ disabled: currentPage === 1 }">
                            <button class="page-link" @click="currentPage > 1 && currentPage--">
                              <span aria-hidden="true">&laquo;</span>
                            </button>
                          </li>

                          <li v-for="(page, i) in paginationNumbersAnakGabungan" :key="i" class="page-item"
                            :class="{ active: currentPage === page, disabled: page === '...' }">
                            <button class="page-link" @click="page !== '...' && (currentPage = page)">
                              {{ page }}
                            </button>
                          </li>

                          <li class="page-item" :class="{ disabled: currentPage === totalPagesAnak }">
                            <button class="page-link" @click="currentPage < totalPagesAnak && currentPage++">
                              <span aria-hidden="true">&raquo;</span>
                            </button>
                          </li>
                        </ul>
                      </nav>
                    </div>
                  </div> -->
                </div>
              </div>
            </div>

            <!-- Tab Bumil -->
            <div class="tab-pane fade mt-3" id="bumil-tab-pane" role="tabpanel" tabindex="0">
              <div id="kesehatanBumilExport">
                <!-- card bumil -->
                <div class="col-12">
                  <div class="row row-cols-1 row-cols-sm-2 p-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                    <div v-for="(item, index) in kesehatanData.bumil" :key="index" class="col">
                      <div class="card h-100 shadow-sm border-0 d-flex flex-column"
                        :class="`border-start border-4 border-${item.color}`">
                        <!-- BODY -->
                        <div v-if="index !== kesehatanData.bumil.length - 1"
                          class="card-body d-flex justify-content-between align-items-center">
                          <div>
                            <h6 class="fw-bold mb-1">{{ item.title }}</h6>

                            <p class="mb-0" :class="`text-${item.color}`">
                              {{ item.percent }}
                            </p>

                          </div>

                          <h2 class="fw-bold mb-0" :class="`text-${item.color}`">
                            {{ item.value }}
                          </h2>
                        </div>

                        <div v-else class="card-body d-flex flex-column justify-content-between">
                          <h6 class="fw-bold mb-3">{{ item.title }}</h6>

                          <div class="d-flex justify-content-between align-items-center gap-3">
                            <i class="bi bi-people display-6" :class="`text-${item.color}`"></i>

                            <h1 class="fw-bold display-5 mb-0" :class="`text-${item.color}`">
                              {{ item.value }}
                            </h1>
                          </div>
                        </div>

                        <!-- FOOTER -->
                        <div v-if="index !== kesehatanData.bumil.length - 1"
                          class="card-footer bg-transparent border-0 p-0">
                          <canvas :ref="'chart-bumil-' + index" height="120"></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tren -->
                <div class="container-fluid">
                  <h2 class="ringkasan-header text-primary mb-3">Status Kesehatan Ibu Hamil</h2>

                  <!-- Row utama: tabel & chart berdampingan -->
                  <div class="row g-3">
                    <!-- Table -->
                    <div class="col-12 col-xl-6">
                      <div class="card border border-primary shadow p-3 h-100">
                        <div class="table-responsive">
                          <table class="table table-borderless align-middle">
                            <thead>
                              <tr class="fw-semibold text-additional">
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">
                                  Status</th>
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">
                                  Jumlah</th>
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">
                                  Persen</th>
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">Tren
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(row, index) in dataTable_bumil" :key="index">
                                <td id="text-diagram-table-piechart" class="text-additional small"
                                  style="color: #006341 !important">
                                  {{ row.status }}
                                </td>
                                <td id="text-diagram-table-piechart" class="text-additional small"
                                  style="color: #006341 !important">
                                  {{ row.jumlah ?? 0 }}
                                </td>
                                <td id="text-diagram-table-piechart" class="text-additional small"
                                  style="color: #006341 !important">
                                  {{ row.persen ? row.persen + ' %' : '0 %' }}
                                </td>
                                <td id="text-diagram-table-piechart" class="small" :class="row.trenClass">
                                  <span v-if="row.tren && row.tren !== '-'" style="color: #006341 !important">
                                    <i :class="row.trenIcon"></i> {{ row.tren }}
                                  </span>
                                  <span v-else>-</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>

                    <!-- Canvas Chart -->
                    <div class="col-12 col-xl-6">
                      <div
                        class="card border border-primary shadow p-3 h-100 d-flex align-items-center justify-content-center">
                        <!-- <div class="w-full h-[300px]"> -->
                        <canvas ref="bumilChart" class="w-full h-full" id="canvas-status-bumil"></canvas>
                        <!-- </div> -->
                      </div>
                    </div>
                  </div>

                  <!-- Tabel tambahan bawah -->
                  <div class="row mt-4">
                    <div class="col-12">
                      <div class="card border border-primary shadow p-3">
                        <div class="table-responsive">
                          <table class="table table-bordered table-sm align-middle text-center mb-0">
                            <thead class="table-primary">
                              <tr>
                                <th class="py-3 fw-semibold">Indikator</th>
                                <th v-for="(bulan, idx) in bulanLabels" :key="idx" class="py-3">
                                  {{ bulan }}
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(values, indikator) in indikatorData" :key="indikator">
                                <td class="fw-medium">{{ indikator }}</td>
                                <td v-for="(val, idx) in values" :key="idx">{{ val }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Ringkasan -->
                <div class="container-fluid mt-3">
                  <div class="row">
                    <div class="col-12 d-flex justify-content-start mb-2">
                      <h2 class="ringkasan-header text-primary">
                        Ibu Hamil dengan masalah Kesehatan Ganda
                      </h2>
                    </div>

                    <!-- CARD UTAMA -->
                    <div class="col-12">
                      <div class="card shadow-sm border-0 rounded-4">
                        <!-- HEADER -->
                        <div class="text-center position-relative mb-0">
                          <h2 class="fw-bold text-white pt-2 pb-5 px-2 rounded-bottom-5 d-inline-block bg-primary"
                            style="width: 55% !important">
                            {{ totalKasus }} dengan Masalah Gizi Ganda
                          </h2>

                          <!-- TAB NAV -->
                          <div class="container position-relative" style="margin-top: -2.5rem">
                            <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">
                              <a href="#"
                                class="small fw-semibold rounded-pill border border-danger bg-light shadow-sm btn btn-outline-danger text-danger"
                                style="border-bottom-width: 5px !important; cursor: default;">
                                <span class="small text-danger" id="text-title-card-span">Ibu Hamil belum dapat Intervensi
                                  <br />
                                  {{ totalBelum }}</span>
                              </a>

                              <a href="#"
                                class="small fw-semibold rounded-pill border border-primary bg-light shadow-sm btn btn-outline-primary text-primary"
                                style="border-bottom-width: 5px !important; cursor: default;">
                                <span class="small text-success" id="text-title-card-span">Ibu Hamil sudah dapat Intervensi
                                  <br />
                                  {{ totalSudah }}</span>
                              </a>
                            </div>
                          </div>
                        </div>

                        <!-- ISI GRID -->
                        <div class="row g-2 mt-3">
                          <!-- KIRI ATAS -->
                          <div class="col-md-6 col-sm-12">
                            <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                              <div>
                                <h2 class="text-center text-success mb-2">Grafik Tren Ibu Hamil</h2>
                              </div>
                              <div class="chart-placeholder text-muted text-center mt-auto">
                                <canvas ref="bumilTrendChart" style="
                                    max-height: 280px;
                                    min-height: 200px !important;
                                    height: 100% !important;
                                    width: 100% !important;
                                  "></canvas>
                              </div>
                            </div>
                          </div>

                          <!-- TENGAH ATAS -->
                          <div class="col-md-6 col-sm-12">
                            <div class="card shadow-sm border-0 h-100 p-3 d-flex flex-column justify-content-between">
                              <h2 class="text-center text-success mb-2">Diagram Intervensi</h2>
                              <div class="chart-placeholder text-muted text-center py-4">
                                <canvas v-if="isSudahBumil" ref="sudahBumilChart" style="
                                    max-height: 280px;
                                    min-height: 200px !important;
                                    height: 100% !important;
                                    width: 100% !important;
                                  "></canvas>
                                <canvas v-else ref="belumBumilChart" style="
                                    max-height: 280px;
                                    min-height: 200px !important;
                                    height: 100% !important;
                                    width: 100% !important;
                                  "></canvas>
                              </div>
                            </div>
                          </div>

                          <!-- BAWAH: TABEL -->
                          <div id="bumilTableDashboard" class="card shadow-sm border-0 h-100 p-3 table-responsive">
                            <div v-if="isSudah">
                              <table class="table table-striped table-sm align-middle p-2">
                                <thead class="table-success">
                                  <tr>
                                    <th class="text-center p-2">No</th>
                                    <th class="text-center p-2" width="300">Nama</th>
                                    <th class="text-center p-2">Anemia</th>
                                    <th class="text-center p-2">Kehamilan Berisiko</th>
                                    <th class="text-center p-2">KEK</th>
                                    <th class="text-center p-2">Intervensi</th>
                                    <th class="text-center p-2">RT</th>
                                    <th class="text-center p-2">RW</th>
                                    <th class="text-center p-2">Usia (Tahun)</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-for="(ibu, i) in paginatedBumil" :key="i" v-if="paginatedBumil.length">
                                    <td class="text-center">
                                      {{ (currentPage - 1) * perPage + i + 1 }}
                                    </td>
                                    <td>{{ ibu.nama }}</td>
                                    <td class="text-center">
                                      <i v-if="ibu.raw.data_kunjungan.status_gizi_hb" class="bi bi-check2"></i>
                                    </td>
                                    <td class="text-center">
                                      <i v-if="ibu.raw.data_kunjungan.status_gizi_lila" class="bi bi-check2"></i>
                                    </td>
                                    <td class="text-center">
                                      <i v-if="ibu.raw.data_kunjungan.status_gizi_lila" class="bi bi-check2"></i>
                                    </td>
                                    <td class="text-center">{{ ibu.raw.data_intervensi[0]?.kategori }}</td>
                                    <td class="text-center">{{ ibu.rt }}</td>
                                    <td class="text-center">{{ ibu.rw }}</td>
                                    <td class="text-center">{{ ibu.umur ?? '-' }}</td>
                                  </tr>

                                  <!-- Fallback bila kosong -->
                                  <tr v-else>
                                    <td colspan="100%" class="text-center text-muted p-3">
                                      Tidak ada data untuk 3 bulan terakhir
                                    </td>
                                  </tr>
                                </tbody>
                                <tfoot>
                                  <tr>
                                    <td colspan="100%" class="text-end">
                                      <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                                        @click="exportDashboardPdf('kesehatanBumilExport')">
                                        <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                        Expor
                                      </button>
                                    </td>
                                  </tr>
                                </tfoot>
                              </table>
                              <!-- Pagination -->
                              <nav id="responsive-pagination">
                                <ul class="pagination justify-content-end">
                                  <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                    <button class="page-link" @click="currentPage > 1 && currentPage--">
                                      <span aria-hidden="true">&laquo;</span>
                                    </button>
                                  </li>

                                  <li v-for="(page, i) in paginationNumbersBumil" :key="i" class="page-item" :class="{
                                    active: currentPage === page,
                                    disabled: page === '...',
                                  }">
                                    <button class="page-link" @click="page !== '...' && (currentPage = page)">
                                      {{ page }}
                                    </button>
                                  </li>

                                  <li class="page-item" :class="{ disabled: currentPage === totalPagesBumil }">
                                    <button class="page-link" @click="currentPage < totalPagesBumil && currentPage++">
                                      <span aria-hidden="true">&raquo;</span>
                                    </button>
                                  </li>
                                </ul>
                              </nav>
                            </div>
                            <div v-else>


                              <!-- Tombol buka/tutup -->
                              <div class="text-center my-3" @click="showTableIbuHamil = !showTableIbuHamil"
                                style="cursor:pointer;" data-html2canvas-ignore
>
                                <span v-if="!showTableIbuHamil">▼ Lihat tabel ibu hamil</span>
                                <span v-else>▲ Tutup tabel ibu hamil</span>
                              </div>

                              <!-- ANIMASI TRANSITION WRAPPER -->
                              <transition name="slide" data-html2canvas-ignore
>
                                <div v-show="showTableIbuHamil" class="table-wrapper">

                                  <div id="bumilTableDashboard"
                                    class="card shadow-sm border-0 h-100 p-3 table-responsive">
                                    <div v-if="isSudah">
                                      <table class="table table-striped table-sm align-middle p-2">
                                        <thead class="table-success">
                                          <tr>
                                            <th class="text-center p-2">No</th>
                                            <th class="text-center p-2" width="300">Nama</th>
                                            <th class="text-center p-2">Anemia</th>
                                            <th class="text-center p-2">Kehamilan Berisiko</th>
                                            <th class="text-center p-2">KEK</th>
                                            <th class="text-center p-2">Intervensi</th>
                                            <th class="text-center p-2">RT</th>
                                            <th class="text-center p-2">RW</th>
                                            <th class="text-center p-2">Usia (Tahun)</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          <tr v-for="(ibu, i) in paginatedBumil" :key="i" v-if="paginatedBumil.length">
                                            <td class="text-center">
                                              {{ (currentPage - 1) * perPage + i + 1 }}
                                            </td>
                                            <td>{{ ibu.nama }}</td>
                                            <td class="text-center">
                                              <i v-if="ibu.raw.data_kunjungan.status_gizi_hb" class="bi bi-check2"></i>
                                            </td>
                                            <td class="text-center">
                                              <i v-if="ibu.raw.data_kunjungan.status_gizi_lila"
                                                class="bi bi-check2"></i>
                                            </td>
                                            <td class="text-center">
                                              <i v-if="ibu.raw.data_kunjungan.status_gizi_lila"
                                                class="bi bi-check2"></i>
                                            </td>
                                            <td class="text-center">{{ ibu.raw.data_intervensi[0]?.kategori }}</td>
                                            <td class="text-center">{{ ibu.rt }}</td>
                                            <td class="text-center">{{ ibu.rw }}</td>
                                            <td class="text-center">{{ ibu.umur ?? '-' }}</td>
                                          </tr>

                                          <!-- Fallback bila kosong -->
                                          <tr v-else>
                                            <td colspan="100%" class="text-center text-muted p-3">
                                              Tidak ada data untuk 3 bulan terakhir
                                            </td>
                                          </tr>
                                        </tbody>

                                        <tfoot>
                                          <tr>
                                            <td colspan="100%" class="text-end">
                                              <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                                                @click="exportDashboardPdf('kesehatanBumilExport')">
                                                <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                                Expor
                                              </button>
                                            </td>
                                          </tr>
                                        </tfoot>
                                      </table>
                                      <!-- Pagination -->
                                      <nav id="responsive-pagination">
                                        <ul class="pagination justify-content-center">
                                          <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                            <button class="page-link" @click="currentPage--">
                                              <span aria-hidden="true">&laquo;</span>
                                            </button>
                                          </li>
                                          <li class="page-item" v-for="page in totalPages" :key="page"
                                            :class="{ active: currentPage === page }">
                                            <button class="page-link" @click="currentPage = page">
                                              {{ page }}
                                            </button>
                                          </li>
                                          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                                            <button class="page-link" @click="currentPage++">
                                              <span aria-hidden="true">&raquo;</span>
                                            </button>
                                          </li>
                                        </ul>
                                      </nav>
                                    </div>
                                    <div v-else>
                                      <table class="table table-striped table-sm align-middle p-2">
                                        <thead class="table-success">
                                          <tr>
                                            <th class="text-center p-2">No</th>
                                            <th class="text-center p-2" width="300">Nama</th>
                                            <th class="text-center p-2">Jenis Intervensi</th>
                                            <th class="text-center p-2">Anemia</th>
                                            <th class="text-center p-2">Kehamilan Berisiko</th>
                                            <th class="text-center p-2">KEK</th>
                                            <th class="text-center p-2">RT</th>
                                            <th class="text-center p-2">RW</th>
                                            <th class="text-center p-2">
                                              Usia <span class="fw-normal">(Tahun)</span>
                                            </th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                          <tr v-for="(bumil, i) in paginatedBumil" :key="i">
                                            <td class="text-center">
                                              {{ (currentPage - 1) * perPage + i + 1 }}
                                            </td>
                                            <td>{{ bumil.nama }}</td>
                                            <td class="text-center">{{ bumil.intervensi }}</td>
                                            <td class="text-center">
                                              <i v-if="bumil.anemia" class="bi bi-check2"></i>
                                            </td>
                                            <td class="text-center">
                                              <i v-if="bumil.risiko" class="bi bi-check2"></i>
                                            </td>
                                            <td class="text-center">
                                              <i v-if="bumil.kek" class="bi bi-check2"></i>
                                            </td>
                                            <td class="text-center">{{ bumil.rt }}</td>
                                            <td class="text-center">{{ bumil.rw }}</td>
                                            <td class="text-center">{{ bumil.umur }}</td>
                                          </tr>
                                        </tbody>
                                        <tfoot>
                                          <tr>
                                            <!-- <td colspan="100%" class="text-end">
                                          <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                                            @click="exportDashboardPdf('kesehatanBumilExport')">
                                            <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                            Export
                                          </button>
                                        </td> -->
                                          </tr>
                                        </tfoot>
                                      </table>
                                      <nav>
                                      </nav>
                                    </div>
                                  </div>

                                </div>
                              </transition>

                              <div class="d-flex justify-content-end" data-html2canvas-ignore
>
                                <button class="btn btn-sm btn-outline-primary p-2 mt-2"
                                  @click="exportDashboardPdf('kesehatanBumilExport')">
                                  <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                                  Export
                                </button>
                              </div>

                              <!-- Pagination -->
                              <nav>
                                <!-- <ul class="pagination justify-content-center">
                                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                                  <button class="page-link" @click="currentPage--">Previous</button>
                                </li>
                                <li class="page-item" v-for="page in totalPages_belum" :key="page" :class="{ active: currentPage === page }">
                                  <button class="page-link" @click="currentPage = page">{{ page }}</button>
                                </li>
                                <li class="page-item" :class="{ disabled: currentPage === totalPages_belum }">
                                  <button class="page-link" @click="currentPage++">Next</button>
                                </li>
                              </ul> -->
                              </nav>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab Catin -->
            <div class="tab-pane fade" id="catin-tab-pane" role="tabpanel" tabindex="0">
              <!-- Issue and Stat Card -->
              <!-- <div class="container-fluid my-4">
                <div class="row"> -->
              <div id="catinExport">
                <div class="col-12">
                  <div class="row row-cols-1 row-cols-sm-2 p-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                    <div v-for="(item, index) in kesehatanData.catin" :key="index" class="col">
                      <div class="card h-100 shadow-sm border-0 d-flex flex-column"
                        :class="`border-start border-4 border-${item.color}`">
                        <!-- BODY -->
                        <div v-if="index < 3" class="card-body d-flex justify-content-between align-items-center">
                          <div>
                            <h6 class="fw-bold mb-1">{{ item.title }}</h6>

                            <p class="mb-0" :class="`text-${item.color}`">
                              {{ item.percent }}
                            </p>

                          </div>

                          <h2 class="fw-bold mb-0" :class="`text-${item.color}`">
                            {{ item.value }}
                          </h2>
                        </div>

                        <div v-else class="card-body d-flex flex-column justify-content-between">
                          <h6 class="fw-bold mb-3">{{ item.title }}</h6>

                          <div class="d-flex justify-content-between align-items-center gap-3">
                            <i class="bi bi-people display-6" :class="`text-${item.color}`"></i>

                            <h1 class="fw-bold display-5 mb-0" :class="`text-${item.color}`">
                              {{ item.value }}
                            </h1>
                          </div>
                        </div>

                        <!-- FOOTER -->
                        <div v-if="index < 3" class="card-footer bg-transparent border-0 p-0">
                          <canvas :ref="'chart-catin-' + index" height="120"></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- TREN -->
                <div class="container-fluid">
                  <h2 class="ringkasan-header text-primary mb-3">
                    Status Kesehatan Calon Pengantin
                  </h2>

                  <!-- Row utama: tabel-->
                  <div class="row g-3">
                    <!-- Table -->
                    <div class="col-12">
                      <div class="card border border-primary shadow p-3 h-100">
                        <div class="table-responsive">
                          <table class="table table-borderless align-middle">
                            <thead>
                              <tr class="fw-semibold text-additional">
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">
                                  Status</th>
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">
                                  Jumlah</th>
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">
                                  Persen</th>
                                <th class="text-additional" style="color: #006341 !important;font-weight: bolder;">Tren
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(row, index) in dataTable_catin" :key="index">
                                <td id="text-diagram-table-piechart" class="text-additional small"
                                  style="color: #006341 !important;">
                                  {{ row.status }}
                                </td>
                                <td id="text-diagram-table-piechart" class="text-additional small"
                                  style="color: #006341 !important;">
                                  {{ row.jumlah ?? 0 }}
                                </td>
                                <td id="text-diagram-table-piechart" class="text-additional small"
                                  style="color: #006341 !important;">
                                  {{ row.persen ? row.persen + ' %' : '0 %' }}
                                </td>
                                <td id="text-diagram-table-piechart" class="small" :class="row.trenClass">
                                  <span v-if="row.tren && row.tren !== '-'" style="color: #006341 !important;">
                                    <i :class="row.trenIcon"></i> {{ row.tren }}
                                  </span>
                                  <span v-else>-</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Tabel tambahan bawah -->
                  <div class="row mt-4">
                    <div class="col-12">
                      <div class="card border border-primary shadow p-3">
                        <div class="table-responsive">
                          <table class="table table-bordered table-sm align-middle text-center mb-0">
                            <thead class="table-light">
                              <tr>
                                <th>Indikator</th>
                                <th v-for="(bulan, idx) in bulanLabels" :key="idx">{{ bulan }}</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(values, indikator) in indikatorCatin" :key="indikator">
                                <td class="fw-bold">{{ indikator }}</td>
                                <td v-for="(val, idx) in values" :key="idx">{{ val }}</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </div>
                  </div>
                  <button class="btn btn-sm btn-outline-primary p-2 mt-2" @click="exportDashboardPdf('catinExport')">
                    <i class="bi bi-file-earmark-excel text-primary me-1"></i>
                    Export
                  </button>
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

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Neuton:wght@400;700&display=swap');

/* ============================= */
/* DEFAULT UNTUK LAYAR ≥ 1300px */
/* ============================= */
.form-control,
/* input, textarea */
.form-select,
button {
  font-size: 0.9rem;
}

.row-data-font-size {
  font-size: 14px;
}

label {
  font-size: 0.9rem;
  /* label proporsional */
}

#responsive-chart-container {
  height: 160px;
  /* Tinggi untuk Mobile */
}

.ringkasan-header,
.table-name {
  font-family: 'Neuton', serif;
  font-weight: bold;
  font-size: 24px;
}

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

@media (min-width: 576px) {
  #responsive-chart-container {
    height: 210px;
    /* Tinggi untuk Tablet */
  }
}

@media (min-width: 992px) {
  #responsive-chart-container {
    height: auto;
    /* Tinggi untuk Desktop */
  }
}

@media (max-width: 767.98px) {

  /* Menargetkan semua tombol dan tautan di dalam pagination */
  #responsive-pagination .page-item .page-link {
    /* Ukuran font yang lebih besar */
    font-size: 0.8rem;

    /* Padding yang lebih besar untuk tombol yang lebih tinggi/lebar */
    padding: 0.2rem 0.5rem;

    /* Agar sudutnya tetap terlihat bagus */
    /* border-radius: 0.5rem; */
  }

  /* Atur margin agar tombol tidak terlalu berdekatan */
  #responsive-pagination .page-item {
    margin: 0 0px;
  }
}

/* ============================= */
/* UNTUK LAYAR < 1300px */
/* ============================= */
@media (max-width: 1299px) {

  .form-control,
  .form-select,
  button {
    font-size: 0.75rem;
  }

  label {
    font-size: 0.75rem;
    /* label lebih kecil */
  }
}

/* disabled select tetap proporsional */
.form-select:disabled {
  opacity: 1;
  background-color: #f8f9fa;
}

.sticky-filter {
  position: sticky;
  top: 75px;
  /* sesuaikan dengan tinggi HeaderAdmin kamu */
  z-index: 1020;
  /* supaya tetap di atas card/chart */
  background: #f8f9fa;
  /* warna bg-light */
}

.filter-wrapper {
  position: sticky;
  top: 70px;
  z-index: 1050;
  background-color: #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.active-tab {
  background-color: var(--bs-primary) !important;
  color: white !important;
}

.chart-placeholder {
  background-color: #f8f9fa;
  border-radius: 10px;
  min-height: 150px;
}

.svg-line {
  width: 100%;
  height: 40px;
  opacity: 0.9;
}

.border-violet {
  border-color: #4f0891 !important;
}

/* Overweight - Hitam */
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

.stat-card {
  background-color: #fff;
  border-top: 4px solid var(--bs-secondary);
  height: 90px;
  /* proporsional */
  transition: all 0.2s ease-in-out;
  max-width: 120px;

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
  }

  .icon-wrap {
    background-color: var(--bs-secondary);
    color: #fff;
    border-radius: 8px;
    width: 34px;
    height: 34px;
    font-size: 16px;
    flex-shrink: 0;
  }

  h6 {
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    margin: 0;
  }

  h4 {
    font-family: 'Inter', sans-serif;
    color: #000;
    font-size: 1.1rem;
    margin: 0;
  }
}

.stat-text {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: flex-start;
  height: 60px;

  .spacer {
    flex: 1;
  }
}

/* custom kolom 9 per baris */
@media (min-width: 1400px) {
  .col-xl-1_9 {
    flex: 0 0 auto;
    width: 11.11%;
  }
}

/* fallback grid untuk ukuran lain */
@media (min-width: 992px) and (max-width: 1399.98px) {
  .col-lg-2_custom {
    flex: 0 0 auto;
    width: 11.11%;
    /* 5 kolom */
  }

  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;
    max-width: 120px;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 25px !important;
      height: 25px !important;
      font-size: 12px !important;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 10px !important;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 14px !important;
      margin: 0;
    }
  }

  .stat-text {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    height: 60px;

    .spacer {
      flex: 1;
    }
  }
}

@media (min-width: 768px) and (max-width: 991.98px) {
  .col-md-3 {
    flex: 0 0 auto;
    width: 11.11%;
  }

  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;
    max-width: 80px;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 25px !important;
      height: 25px !important;
      font-size: 12px !important;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 6px !important;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 12px !important;
      margin: 0;
    }
  }

  .stat-text {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;
    height: 60px;

    .spacer {
      flex: 1;
    }
  }
}

@media (max-width: 767.98px) {
  .col-sm-4 {
    flex: 0 0 auto;
    width: 33.33%;
    /* 3 kolom */
  }

  .stat-card {
    background-color: #fff;
    border-top: 4px solid var(--bs-secondary);
    height: 90px;
    /* proporsional */
    transition: all 0.2s ease-in-out;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .icon-wrap {
      background-color: var(--bs-secondary);
      color: #fff;
      border-radius: 8px;
      width: 30px !important;
      height: 30px !important;
      font-size: 12px !important;
      flex-shrink: 0;
    }

    h6 {
      font-family: 'Inter', sans-serif;
      font-size: 7px !important;
      margin: 0;
    }

    h4 {
      font-family: 'Inter', sans-serif;
      color: #000;
      font-size: 12px !important;
      margin: 0;
    }
  }

  @media (min-width: 768px) {
    .sticky-filter {
      position: sticky;
      top: 70px;
      z-index: 10;
    }
  }

  /* Mobile: hilangkan sticky & rapikan */
  /* @media (max-width: 767px) {
    .sticky-filter {
      position: static !important;
    }
    .uniform-input {
      height: 44px;
    }
  } */

  @media (max-width: 767px) {
    .sticky-filter {
      top: 100px;
    }
  }

  .filter-floating-btn {
    position: fixed;
    bottom: 20px;
    right: 20px;
    border-radius: 50%;
    width: 55px;
    height: 55px;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999;
  }
}

* {
  font-size: 16px;
}

/* Fade effect */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Rotate icon */
.rotate {
  transform: rotate(180deg);
  transition: transform 0.25s ease;
}

.bi-chevron-down {
  transition: transform 0.25s ease;
}

#text-diagram-table-piechart {
  font-size: 16px;
}

#text-title-table-piechart {
  font-size: 18px;
}

#text-title-card-header-h1 {
  font-size: 18px;
}

#text-title-card-span {
  font-size: 14px;
}

.hide-for-pdf {
  display: none !important;
}

.canvas-status-bumil {
  padding-left: 5px;
}

.intervensi-container {
  max-width: 1200px;
  margin: 0 auto;
}

.chart-wrapper-large {
  position: relative;
  height: 420px;
  /* Lebar besar utk desktop */
}

@media (max-width: 768px) {
  .chart-wrapper-large {
    height: 320px;
    /* Mobile lebih kecil */
  }
}

/* Wrapper smooth slide */
.table-wrapper {
  overflow: hidden;
}

/* Enter & Leave Animation */
.slide-enter-active,
.slide-leave-active {
  transition: max-height 0.5s ease, opacity 0.5s ease;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
  max-height: 2000px;
  /* cukup besar untuk semua konten */
  opacity: 1;
}

.btn-link {
  color: #007bff;
  font-size: 0.875rem;
}

.btn-link:hover {
  color: #0056b3;
  text-decoration: underline !important;
}

.card {
  transition: all 0.3s ease;
}

canvas {
  cursor: pointer;
}

.cursor-pointer {
  cursor: pointer;
  transition: all 0.3s ease;
}

.cursor-pointer:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.border-3 {
  border-width: 3px !important;
  box-shadow: 0 0 15px rgba(13, 110, 253, 0.3);
}

/* Slide Down Animation */
.slide-down-enter-active {
  animation: slideDown 0.4s ease-out;
}

.slide-down-leave-active {
  animation: slideDown 0.3s ease-in reverse;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-hover tbody tr:hover {
  background-color: rgba(13, 110, 253, 0.05);
}
</style>

<script>
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
import Welcome from '@/components/Welcome.vue'
import axios from 'axios'
import { exportExcel } from "@/utils/exportExcel";
import { mapDataAnakBerandaToExcel } from "@/mappers/dataAnakBerandaMapper";
import { mapDataIbuHamilBerandaToExcel } from "@/mappers/dataIbuHamilBerandaMapper";
import {
  Chart,
  PieController,
  ArcElement,
  Tooltip,
  Legend,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  LineController,
  LineElement,
  PointElement,
  Filler,
} from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'

// PORT backend kamu
const API_PORT = 8000

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend)
Chart.register(PieController, ArcElement, Tooltip, Legend, ChartDataLabels)
Chart.register(
  LineController,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
  Filler,
)

function getNowYearMonth() {
  const d = new Date()
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  return `${year}-${month}`
}
export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Dashboard',
  components: { NavbarAdmin, CopyRight, HeaderAdmin, Welcome },
  data() {
    return {
      bulanIni: 'Ini',
      showDetail_bb: false,
      showDetail_tb: false,
      showDetail_bbtb: false,
      showTable: false,
      showTableIbuHamil: false,
      diagramIntervensi: [],
      jmlTotalAnak: 0,
      noIntervensiMessage: '',
      dataLoad_belum: [],
      giziGandaAnak: [],
      dataLoad: [],
      selectedChart: null,
      kesehatanData: {
        anak: [],
        bumil: [],
        catin: [],
      },
      dataTable_bumil: [],
      dataTable_bb: [],
      dataTable_tb: [],
      dataTable_bbtb: [],
      indikatorCatin: [],
      indikatorData: [],
      intervensiData: [],
      sudahBumilChart: null,
      belumBumilChart: null,
      bumilChart: null,
      isSudahBumil: false,
      isSudah: false,
      bulanLabels: [], // diisi daftar bulan
      rawData: [], // data asli anak
      filteredData: [], // data hasil filter
      activeMenu: 'anak', // default tab
      gizi: [],
      totalBumil: 0,
      totalCatin: 0,
      totalAnak: 0,
      sudah: 0,
      belum: 0,
      sudahBumil: 0,
      belumBumil: 0,
      anakMasalah: [],
      filterPeriode: 3,
      lineChart: null,
      funnelChart: null,
      sudahChart: null,
      barChart: null,
      currentPage: 1,
      perPage: 5,
      infoBoxes: [],
      configCacheKey: 'site_config_cache',
      isLoading: true,
      isCollapsed: false,
      username: '',
      today: '',
      thisMonth: '',
      kelurahan: '',
      windowWidth: window.innerWidth,
      stats: [],
      children: [],
      bride: [],
      filters: {
        ref: 'd',
        provinsi:'',
        kota:'',
        kecamatan:'',
        kelurahan: '',
        posyandu: '',
        rw: '',
        rt: '',
        periode: '',
      },
      dev: 0,
      posyanduList: [],
      rwList: [],
      rtList: [],
      periodeOptions: [],
      rwReadonly: true,
      rtReadonly: true,
      kesehatan_catin: [],
      kesehatan_bumil: [],
      intervensi_bumil: [],
      showFilterMobile: false,
      isMobile: false,
      mobileFilterOpen: false,
      periodeLabel: '',
      chartInstances: {
        bb: null,
        bb_detail: null,
        tb: null,
        tb_detail: null,
        bbtb: null,
        bbtb_detail: null
      },
    }
  },
  methods: {
    getPeriodeLabel() {
      if (!this.filters.periode) {
        return new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' })
      }

      const [year, month] = this.filters.periode.split('-')
      const date = new Date(year, month - 1, 1)
      return date.toLocaleString('id-ID', { month: 'long', year: 'numeric' })
    },
    toggle(idx) {
      this.infoBoxes[idx].open = !this.infoBoxes[idx].open
    },
    toggleDetail(type) {
      if (type === 'bb') {
        this.showDetail_bb = !this.showDetail_bb
        this.$nextTick(() => {
          if (this.showDetail_bb) {
            this.renderChart(
              'pieChart_bb_detail',
              this.dataTable_bb,
              this.dataTable_bb.map(r => r.color)
            )
          } else {
            // Destroy chart detail saat collapse
            if (this.chartInstances && this.chartInstances.bb_detail) {
              this.chartInstances.bb_detail.destroy()
              this.chartInstances.bb_detail = null
            }
          }
        })
      }
      else if (type === 'tb') {
        this.showDetail_tb = !this.showDetail_tb
        this.$nextTick(() => {
          if (this.showDetail_tb) {
            this.renderChart(
              'pieChart_tb_detail',
              this.dataTable_tb,
              this.dataTable_tb.map(r => r.color)
            )
          } else {
            if (this.chartInstances && this.chartInstances.tb_detail) {
              this.chartInstances.tb_detail.destroy()
              this.chartInstances.tb_detail = null
            }
          }
        })
      }
      else if (type === 'bbtb') {
        this.showDetail_bbtb = !this.showDetail_bbtb
        this.$nextTick(() => {
          if (this.showDetail_bbtb) {
            this.renderChart(
              'pieChart_status_detail',
              this.dataTable_bbtb,
              this.dataTable_bbtb.map(r => r.color)
            )
          } else {
            if (this.chartInstances && this.chartInstances.bbtb_detail) {
              this.chartInstances.bbtb_detail.destroy()
              this.chartInstances.bbtb_detail = null
            }
          }
        })
      }
    },
    async exportDashboardPdf(tagId) {
      const infoBoxes = document.querySelector('#infoBoxes')
      const canvasStatusBumil = document.querySelector('#canvas-status-bumil')
      if (infoBoxes) infoBoxes.classList.add('hide-for-pdf')
      if (canvasStatusBumil) infoBoxes.classList.add('canvas-status-bumil')

      if(tagId === 'giziAnakExport'){
        const excelData = mapDataAnakBerandaToExcel(this.filteredAnakGabungan);

        exportExcel({
          data: excelData,
          fileName: "Data_Gizi_Anak_Beranda.xlsx",
          sheetName: "Gizi Anak Beranda",
        });
      }

      if(tagId === 'kesehatanBumilExport'){
        const excelData = mapDataIbuHamilBerandaToExcel(this.filteredBumil);
        //console.log("excelData", excelData);
        exportExcel({
          data: excelData,
          fileName: "Data_Ibu_Hamil_Beranda.xlsx",
          sheetName: "Gizi Ibu Hamil Beranda",
        });
      }

      // const bumilTable = document.querySelector('#bumilTableDashboard')
      // if (bumilTable) bumilTable.classList.add('hide-for-pdf')
      const element = document.getElementById(tagId)

      // Loading overlay
      const loading = document.createElement('div')
      loading.innerHTML = 'Generating PDF...'
      loading.style = `
    position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(255,255,255,0.9); font-size:30px;
    display:flex; align-items:center; justify-content:center;
    z-index:9999
  `
      document.body.appendChild(loading)

      await new Promise((r) => setTimeout(r, 400))

      // 🚀 Pastikan elemen tidak terpotong scroll
      const originalOverflow = element.style.overflow
      element.style.overflow = 'visible'

      const margin = 10 // margin PDF dalam mm

      html2canvas(element, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        windowWidth: element.scrollWidth,
        windowHeight: element.scrollHeight,
      }).then((canvas) => {
        const imgData = canvas.toDataURL('image/png')

        const pxWidth = canvas.width
        const pxHeight = canvas.height

        const mmWidth = pxWidth * 0.264583
        const mmHeight = pxHeight * 0.264583

        // 🚀 Tambahkan margin di PDF
        const pdf = new jsPDF({
          orientation: 'p',
          unit: 'mm',
          format: [mmWidth + margin * 2, mmHeight + margin * 2],
        })

        pdf.addImage(imgData, 'PNG', margin, margin, mmWidth, mmHeight)
        pdf.save(tagId + '.pdf')

        element.style.overflow = originalOverflow
        document.body.removeChild(loading)

        infoBoxes.classList.remove('hide-for-pdf')
        // bumilTable.classList.remove('hide-for-pdf')
      })
    },
    async generateIndikatorBumilBulanan() {
      try {
        //this.isLoading = true;

        const params = {
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || getNowYearMonth(),
        }

        Object.keys(params).forEach((key) => {
          if (params[key] === '' || params[key] === null || params[key] === undefined) {
            delete params[key]
          }
        })

        const res = await axios.get(`${baseURL}/api/pregnancy/indikator-bulanan`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        const { labels, indikator } = res.data || {}

        // kalau backend kirim kosong, tetap buat struktur default
        if (!labels?.length || !indikator) {
          this.bulanLabels = this.getLast12Months()
          this.indikatorData = {
            KEK: Array(12).fill(0),
            Anemia: Array(12).fill(0),
            Berisiko: Array(12).fill(0),
            Normal: Array(12).fill(0),
          }
          return
        }

        this.bulanLabels = labels
        this.indikatorData = indikator

        //console.log('✅ indikatorData:', this.indikatorData);
      } catch (err) {
        console.error('❌ Gagal memuat indikator bumil bulanan:', err)
        this.bulanLabels = this.getLast12Months()
        this.indikatorData = {
          KEK: Array(12).fill(0),
          Anemia: Array(12).fill(0),
          Berisiko: Array(12).fill(0),
          Normal: Array(12).fill(0),
        }
      } finally {
        //this.isLoading = false;
      }
    },
    // Mandatory
    exportCSV() {
      // ambil data gabungan filtered
      const data = this.filteredAnakGabungan

      if (!data.length) {
        alert('Tidak ada data untuk diexport!')
        return
      }
      // header CSV
      const headers = [
        'No',
        'Nama',
        'NIK',
        'Nama Ortu',
        'RT',
        'RW',
        'Posyandu',
        'Rumusan',
        'Stunting',
        'Wasting',
        'Underweight',
        'BB Stagnan',
        'Overweight',
      ]

      // buat array baris
      const rows = data.map((item, index) => {
        const kunjungan = item.data_kunjungan || {}
        return [
          index + 1,
          item.nama || '',
          item.nik || '',
          kunjungan.nama_ortu || '',
          kunjungan.rt || '',
          kunjungan.rw || '',
          item.posyandu || '',
          item.rumusan || '',
          item.stunting ? '✓' : '',
          item.wasting ? '✓' : '',
          item.underweight ? '✓' : '',
          item.bb_stagnan ? '✓' : '',
          item.overweight ? '✓' : '',
        ]
      })

      // gabungkan header + rows
      const csvContent = [headers, ...rows]
        .map((e) => e.map((v) => `"${v}"`).join(',')) // wrap semua value di tanda kutip agar aman
        .join('\n')

      // buat blob dan link download
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const url = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `data_anak_${new Date().toISOString().slice(0, 10)}.csv`)
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)
    },
    setMenu(type) {
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
        //console.log(wilayah);

        this.kelurahan = wilayah.kelurahan || 'Tidak diketahui'
        this.filters.provinsi = wilayah.provinsi || ''
        this.filters.kota = wilayah.kota || ''
        this.filters.kecamatan = wilayah.kecamatan || ''
        this.filters.kelurahan = this.kelurahan
        this.id_wilayah = wilayah.id_wilayah // pastikan backend kirim ini

        // Setelah dapet id_wilayah, langsung fetch posyandu
        await this.fetchPosyanduByWilayah(this.id_wilayah)
      } catch (error) {
        console.error('Gagal ambil data wilayah user:', error)
        this.kelurahan = '-'
      }
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

        this.posyanduList = res.data?.data || res.data || []
        //console.log("Posyandu list:", this.posyanduList);
      } catch (error) {
        console.error('Gagal mengambil data posyandu:', error)
        this.posyanduList = []
      }
    },
    handlePosyanduChange() {
      const selected = this.posyanduList.find((p) => p.nama_posyandu === this.filters.posyandu)

      if (selected) {
        this.rwList = selected.rw || []
        this.rtList = [] // reset RT
        this.filters.rw = ''
        this.filters.rt = ''
        this.rwReadonly = false
        this.rtReadonly = true
      } else {
        this.rwList = []
        this.rtList = []
        this.filters.rw = ''
        this.filters.rt = ''
        this.rwReadonly = true
        this.rtReadonly = true
      }
    },
    handleRWChange() {
      const selected = this.posyanduList.find((p) => p.nama_posyandu === this.filters.posyandu)

      if (selected) {
        // RT yang terkait RW tertentu
        this.rtList = selected.rt || []
        this.filters.rt = ''
        this.rtReadonly = false
      } else {
        this.rtList = []
        this.filters.rt = ''
        this.rtReadonly = true
      }
    },
    async fetchStats() {
      try {
        const res = await axios.get(`${baseURL}/api/dashboard/stats/`, {
          headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
        })
        const data = res.data
        this.stats = [
          { title: 'RW', value: data.rw, icon: 'bi bi-houses-fill' },
          { title: 'RT', value: data.rt, icon: 'bi bi-house-fill' },
          { title: 'Keluarga Terdaftar', value: data.keluarga, icon: 'fa-solid fa-people-roof' },
          { title: 'TPK', value: data.tpk, icon: 'bi bi-person-vcard' },
          { title: 'Ibu Hamil', value: data.ibu_hamil, icon: 'fa-solid fa-person-pregnant' },
          { title: 'Posyandu', value: data.posyandu, icon: 'bi bi-heart-pulse' },
          { title: 'Bidan', value: data.bidan, icon: 'fa-solid fa-stethoscope' },
          { title: 'Calon Pengantin', value: data.catin, icon: 'bi bi-arrow-through-heart' },
          { title: 'Anak <= 5 Tahun', value: data.anak, icon: 'fa-solid fa-baby' },
        ]
      } catch (e) {
        console.error(e)
      }
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
    getLast12Months() {
      const monthNames = [
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
      const labels = []
      const now = new Date()
      for (let i = 11; i >= 0; i--) {
        const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
        // labels.push(`${monthNames[d.getMonth()]} ${d.getFullYear()}`)
        labels.push(`${monthNames[d.getMonth()]}`)
      }
      return labels
    },
    async applyFilter() {
      //console.log("THIS Filter Bumil", this.filteredBumil);

      if (this.isMobile) {
        this.showFilterMobile = false // auto sembunyikan setelah apply
      }
      this.isLoading = true
      try {
        // UPDATE PERIODE LABEL DI SINI ← !!!
        this.periodeLabel = this.getPeriodeLabel()
        this.bulanIni = this.periodeLabel
        await Promise.all([
          this.hitungStatistik(),
          this.generateDataTable(),
          this.masalahGanda(),
          this.hitungIntervensi(),
          this.generateInfoBoxes(),
          this.generateIndikatorBumilBulanan(),
          this.renderGiziGandaGiziAnak()
        ])

        this.renderFunnelChart()
        this.renderBumilChart()
        this.renderIntervensiBumilChart()
      } catch (e) {
        this.showError('Gagal menerapkan filter', e)
      } finally {
        this.isLoading = false // END LOADING
      }
    },
    async hitungStatistik() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
        }

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` }
        let res = null

        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/status`, { headers, params })
            break
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/status`, { headers, params })
            break
          case 'catin':
            res = await axios.get(`${baseURL}/api/bride/status`, { headers, params })
            break
          default:
            return
        }

        const data = res.data
        //console.log('catin', data)

        const total = data.total || 0
        this.totalAnak = total

        this.kesehatanData[this.activeMenu] = data.counts.map((item) => ({
          title: item.title,
          value: item.value,
          percent: total > 0 ? ((item.value / total) * 100).toFixed(1) + '%' : '0%',
          color: item.color,
          trend: item.trend,
        }))

        //console.log('✅ kesehatanData:', this.kesehatanData);

        // 🔥 render chart setelah semua elemen DOM selesai muncul
        this.$nextTick(() => {
          setTimeout(() => {
            this.kesehatanData[this.activeMenu].forEach((item, index) => {

              let trendFixed = [];

              // Pilih normalize sesuai menu aktif
              if (this.activeMenu === "anak") {
                trendFixed = this.normalizeTrendNumber(item.trend);
                this.rendersvgChart(`chart-${index}`, trendFixed, [item.color]);
              }

              else if (this.activeMenu === "bumil") {
                trendFixed = this.normalizeTrendNumber(item.trend);
                //console.log('trendBumil:',trendFixed);

                this.rendersvgChart_Bumil(`chart-bumil-${index}`, trendFixed, [item.color]);
              }

              else if (this.activeMenu === "catin") {
                trendFixed = this.normalizeTrendNumber(item.trend);
                this.rendersvgChart_Catin(`chart-catin-${index}`, trendFixed, [item.color]);
              }

            });
          }, 80)
        }) //console.log("Refs available:", this.$refs);
      } catch (error) {
        console.error('❌ hitungStatusGizi error:', error)
      }
    },

    normalizeTrendNumber(trend) {
      if (!trend) return []

      // Jika sudah array (seperti menu anak), langsung kembalikan
      if (Array.isArray(trend)) return trend


      // Format khusus CATIN: { months:[], data:[], total:{} }
      // eslint-disable-next-line no-undef
      if (months && trend.data) {
        // eslint-disable-next-line no-undef
        //console.log('normalizeCatinTrend - trend:', months, trend.data  )
        return trend.months.map((bulan, i) => ({
          bulan,
          persen: this.extractNumber(trend.data[i])
        }))
      }

      // Jika object lain, fallback ke Object.values()
      return Object.values(trend)
    },
    normalizeTrendObject(trend) {
      if (!trend || !trend.months || !trend.data) return [];

      return trend.months.map((bulan, i) => {
        const raw = trend.data[i];
        const val = raw && typeof raw === "object" ? Object.values(raw)[0] : 0;

        return {
          bulan,
          persen: Number(val) || 0,
        };
      });
    },
    rendersvgChart(refName, dataTable, colors, labelKey = 'bulan', valueKey = 'jumlah') {
      let ref = this.$refs[refName]
      if (!ref) return

      const canvas = Array.isArray(ref) ? ref[0] : ref
      if (!canvas) return

      const ctx = canvas.getContext('2d')
      if (!ctx) return

      // Destroy instance sebelumnya jika ada
      if (this[refName + 'Instance']) this[refName + 'Instance'].destroy()
      if (!Array.isArray(dataTable) || !dataTable.length) return

      let processedData = dataTable
      //console.log('periode',this.filters.periode);

      if (!this.filters.periode) {
        // Default → buang data terakhir
        processedData = dataTable.slice(0, -1)
      } else {
        // Periode dipilih → buang data pertama
        processedData = dataTable.slice(1)
      }

      // Extract label dan nilai
      const labels = processedData.map((row) => row[labelKey])
      const values = processedData.map((row) => parseFloat(row[valueKey]) || 0)

      // AUTO SCALE: Buat grafik tidak datar
      let minValue = Math.min(...values)
      let maxValue = Math.max(...values)

      const gap = maxValue - minValue

      // Jika datanya sama semua → pakai padding default supaya grafik tidak flat
      const padding = gap === 0 ? 5 : gap * 0.3

      const yMin = minValue - padding
      const yMax = maxValue + padding

      // MAP BOOTSTRAP COLOR → HEX
      const colorMap = {
        primary: '#0d6efd',
        violet: '#4f0891',
        secondary: '#6c757d',
        success: '#198754',
        danger: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0',
        dark: '#212529',
      }

      const borderColor = colorMap[colors[0]] || colors[0] || '#0d6efd'

      // OPTIONAL: Gradient lembut biar grafik cantik
      const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height)
      gradient.addColorStop(0, borderColor + '33') // 20% opacity
      gradient.addColorStop(1, borderColor + '00') // transparent

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              data: values,
              borderColor,
              backgroundColor: gradient,
              borderWidth: 3,
              tension: 0.4, // ✔ lebih smooth & terlihat perubahan
              pointRadius: 0,
              pointHoverRadius: 0,
              fill: true, // ✔ ada gradasi lembut
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: { display: false },
            datalabels: { display: false },
          },

          scales: {
            x: {
              display: false,
              grid: { display: false },
            },
            y: {
              display: false,
              grid: { display: false },
              min: yMin,
              max: yMax,
            },
          },

          elements: {
            line: {
              borderCapStyle: 'round',
              borderJoinStyle: 'round',
            },
          },

          animation: {
            duration: 0,
          },
        },
      })
    },
    rendersvgChart_Bumil(refName, dataTable, colors, labelKey = 'bulan', valueKey = 'jumlah') {
      let ref = this.$refs[refName]
      if (!ref) return

      const canvas = Array.isArray(ref) ? ref[0] : ref
      if (!canvas) return

      const ctx = canvas.getContext('2d')
      if (!ctx) return

      // Destroy instance sebelumnya jika ada
      if (this[refName + 'Instance']) this[refName + 'Instance'].destroy()
      if (!Array.isArray(dataTable) || !dataTable.length) return

      // Extract label dan nilai
      const labels = dataTable.map((row) => row[labelKey])
      const values = dataTable.map((row) => parseFloat(row[valueKey]) || 0)

      // AUTO SCALE: Buat grafik tidak datar
      let minValue = Math.min(...values)
      let maxValue = Math.max(...values)

      const gap = maxValue - minValue

      // Jika datanya sama semua → pakai padding default supaya grafik tidak flat
      const padding = gap === 0 ? 5 : gap * 0.3

      const yMin = minValue - padding
      const yMax = maxValue + padding

      // MAP BOOTSTRAP COLOR → HEX
      const colorMap = {
        primary: '#0d6efd',
        violet: '#4f0891',
        secondary: '#6c757d',
        success: '#198754',
        danger: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0',
        dark: '#212529',
      }

      const borderColor = colorMap[colors[0]] || colors[0] || '#0d6efd'

      // OPTIONAL: Gradient lembut biar grafik cantik
      const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height)
      gradient.addColorStop(0, borderColor + '33') // 20% opacity
      gradient.addColorStop(1, borderColor + '00') // transparent

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              data: values,
              borderColor,
              backgroundColor: gradient,
              borderWidth: 3,
              tension: 0.4, // ✔ lebih smooth & terlihat perubahan
              pointRadius: 0,
              pointHoverRadius: 0,
              fill: true, // ✔ ada gradasi lembut
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: { display: false },
            datalabels: { display: false },
          },

          scales: {
            x: {
              display: false,
              grid: { display: false },
            },
            y: {
              display: false,
              grid: { display: false },
              min: yMin,
              max: yMax,
            },
          },

          elements: {
            line: {
              borderCapStyle: 'round',
              borderJoinStyle: 'round',
            },
          },

          animation: {
            duration: 0,
          },
        },
      })
    },
    rendersvgChart_Catin(refName, dataTable, colors, labelKey = 'bulan', valueKey = 'jumlah') {
      let ref = this.$refs[refName]
      if (!ref) return

      const canvas = Array.isArray(ref) ? ref[0] : ref
      if (!canvas) return

      const ctx = canvas.getContext('2d')
      if (!ctx) return

      // Destroy instance sebelumnya jika ada
      if (this[refName + 'Instance']) this[refName + 'Instance'].destroy()
      if (!Array.isArray(dataTable) || !dataTable.length) return

      // Extract label dan nilai
      const labels = dataTable.map((row) => row[labelKey])
      const values = dataTable.map((row) => parseFloat(row[valueKey]) || 0)

      // AUTO SCALE: Buat grafik tidak datar
      let minValue = Math.min(...values)
      let maxValue = Math.max(...values)

      const gap = maxValue - minValue

      // Jika datanya sama semua → pakai padding default supaya grafik tidak flat
      const padding = gap === 0 ? 5 : gap * 0.3

      const yMin = minValue - padding
      const yMax = maxValue + padding

      // MAP BOOTSTRAP COLOR → HEX
      const colorMap = {
        primary: '#0d6efd',
        violet: '#4f0891',
        secondary: '#6c757d',
        success: '#198754',
        danger: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0',
        dark: '#212529',
      }

      const borderColor = colorMap[colors[0]] || colors[0] || '#0d6efd'

      // OPTIONAL: Gradient lembut biar grafik cantik
      const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height)
      gradient.addColorStop(0, borderColor + '33') // 20% opacity
      gradient.addColorStop(1, borderColor + '00') // transparent

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              data: values,
              borderColor,
              backgroundColor: gradient,
              borderWidth: 3,
              tension: 0.4, // ✔ lebih smooth & terlihat perubahan
              pointRadius: 0,
              pointHoverRadius: 0,
              fill: true, // ✔ ada gradasi lembut
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,

          plugins: {
            legend: { display: false },
            datalabels: { display: false },
          },

          scales: {
            x: {
              display: false,
              grid: { display: false },
            },
            y: {
              display: false,
              grid: { display: false },
              min: yMin,
              max: yMax,
            },
          },

          elements: {
            line: {
              borderCapStyle: 'round',
              borderJoinStyle: 'round',
            },
          },

          animation: {
            duration: 600,
            easing: 'easeOutCubic',
          },
        },
      })
    },
    async generateDataTable() {
      try {
        const params = {
          from: 'dashboard',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
        }

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` }
        let res = null

        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/tren`, { headers, params })
            break
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/tren`, { headers, params })
            break
          case 'catin':
            res = await axios.get(`${baseURL}/api/bride/tren`, { headers, params })
            break
          default:
            return
        }

        // ==================== ANAK ====================
        if (this.activeMenu === 'anak') {
          // ----- BB/U -----
          const bbCurrent = res.data.bb?.data?.current || {}
          const bbLast = res.data.bb?.data?.previous || {}
          const totalCurrent = res.data.bb?.total?.current || 0
          const bbColors = ['#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b']

          this.dataTable_bb = Object.entries(bbCurrent).map(([status, jumlah], idx) => {
            const prevValue = bbLast[status] ?? 0
            const diff = jumlah - prevValue

            let tren = '-',
              trenIcon = '',
              trenClass = ''
            if (prevValue === 0 && jumlah === 0) tren = '-'
            else if (diff > 0) {
              tren = prevValue === 0 ? '100.00%' : ((diff / prevValue) * 100).toFixed(2) + '%'
              trenIcon = 'bi bi-caret-up-fill'
              trenClass = 'text-danger'
            } else if (diff < 0) {
              tren = prevValue === 0 ? '100.00%' : ((diff / prevValue) * 100).toFixed(2) + '%'
              trenIcon = 'bi bi-caret-down-fill'
              trenClass = 'text-success'
            } else {
              tren = '0.00%'
              trenIcon = 'bi bi-caret-right-fill'
              trenClass = 'text-secondary'
            }

            return {
              status,
              jumlah,
              persen: totalCurrent > 0 ? ((jumlah / totalCurrent) * 100).toFixed(1) : 0,
              tren,
              trenIcon,
              trenClass,
              color: bbColors[idx % bbColors.length], // warna slice sinkron dengan chart
            }
          })

          // ----- TB/U -----
          const tbCurrent = res.data.tb?.data?.current || {}
          const tbLast = res.data.tb?.data?.previous || {}
          const totalCurrentTB = res.data.tb?.total?.current || 0
          const tbColors = ['#f7db7f', '#bfbbe4', '#7dae9b', '#e87d7b']

          this.dataTable_tb = Object.entries(tbCurrent).map(([status, jumlah], idx) => {
            const prevValue = tbLast[status] ?? 0
            const diff = jumlah - prevValue

            let tren = '-',
              trenIcon = '',
              trenClass = ''
            if (prevValue === 0 && jumlah === 0) tren = '-'
            else if (diff > 0) {
              tren = prevValue === 0 ? '100.00%' : ((diff / prevValue) * 100).toFixed(2) + '%'
              trenIcon = 'bi bi-caret-up-fill'
              trenClass = 'text-danger'
            } else if (diff < 0) {
              tren = prevValue === 0 ? '100.00%' : ((diff / prevValue) * 100).toFixed(2) + '%'
              trenIcon = 'bi bi-caret-down-fill'
              trenClass = 'text-success'
            } else {
              tren = '0.00%'
              trenIcon = 'bi bi-caret-right-fill'
              trenClass = 'text-secondary'
            }

            return {
              status,
              jumlah,
              persen: totalCurrentTB > 0 ? ((jumlah / totalCurrentTB) * 100).toFixed(1) : 0,
              tren,
              trenIcon,
              trenClass,
              color: tbColors[idx % tbColors.length],
            }
          })

          // ----- BB/TB -----
          const bbtbCurrent = res.data.bbtb?.data?.current || {}
          const bbtbLast = res.data.bbtb?.data?.previous || {}
          const totalCurrentBBTB = res.data.bbtb?.total?.current || 0
          const bbtbColors = ['#f5ebb9', '#f7db7f', '#7dae9b', '#bfbbe4', '#e87d7b', '#eaafdd']

          this.dataTable_bbtb = Object.entries(bbtbCurrent).map(([status, jumlah], idx) => {
            const prevValue = bbtbLast[status] ?? 0
            const diff = jumlah - prevValue

            let tren = '-',
              trenIcon = '',
              trenClass = ''
            if (prevValue === 0 && jumlah === 0) tren = '-'
            else if (diff > 0) {
              tren = prevValue === 0 ? '100.00%' : ((diff / prevValue) * 100).toFixed(2) + '%'
              trenIcon = 'bi bi-caret-up-fill'
              trenClass = 'text-danger'
            } else if (diff < 0) {
              tren = prevValue === 0 ? '100.00%' : ((diff / prevValue) * 100).toFixed(2) + '%'
              trenIcon = 'bi bi-caret-down-fill'
              trenClass = 'text-success'
            } else {
              tren = '0.00%'
              trenIcon = 'bi bi-caret-right-fill'
              trenClass = 'text-secondary'
            }

            return {
              status,
              jumlah,
              persen: totalCurrentBBTB > 0 ? ((jumlah / totalCurrentBBTB) * 100).toFixed(1) : 0,
              tren,
              trenIcon,
              trenClass,
              color: bbtbColors[idx % bbtbColors.length],
            }
          })

          // ==================== Render Pie Chart ====================
          this.$nextTick(() => {
            this.renderChart(
              'pieChart_bb',
              this.dataTable_bb,
              this.dataTable_bb.map((r) => r.color),
            )
            this.renderChart(
              'pieChart_tb',
              this.dataTable_tb,
              this.dataTable_tb.map((r) => r.color),
            )
            this.renderChart(
              'pieChart_status',
              this.dataTable_bbtb,
              this.dataTable_bbtb.map((r) => r.color),
            )
          })
        }

        // ==================== BUMIL & CATIN (tidak perlu warna pie dinamis) ====================
        if (this.activeMenu === 'bumil') {
          this.dataTable_bumil = (res.data.dataTable_bumil || []).map((row) => ({
            status: row.status || '-',
            jumlah: row.jumlah ?? 0,
            persen: row.persen ?? 0,
            tren: row.tren ?? '-',
            trenIcon: row.trenIcon ?? '',
            trenClass: row.trenClass ?? '',
          }))
        }

        if (this.activeMenu === 'catin') {
          const dataCatin = res.data.dataTable_catin
          this.dataTable_catin = Array.isArray(dataCatin)
            ? dataCatin
            : Object.values(dataCatin || {}).map((row) => ({
              status: row.status || '-',
              jumlah: row.jumlah ?? 0,
              persen: row.persen ?? 0,
              tren: row.tren ?? '-',
              trenIcon: row.trenIcon ?? '',
              trenClass: row.trenClass ?? '',
            }))
        }
      } catch (e) {
        this.showError('Error Ambil Data', e)
      }
    },
    toggleSudah(val) {
      this.isSudah = val
      this.isUdahBumil = val
    },
    async masalahGanda() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
        }

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` }

        //let res = null;

        switch (this.activeMenu) {
          case 'anak':
            await axios.get(`${baseURL}/api/children/case`, { headers, params })
            break
          case 'bumil':
            await axios.get(`${baseURL}/api/pregnancy/case`, { headers, params })
            break
          default:
            return
        }
        /* this.totalKasus = res.data.totalCase
        this.totalSudah = res.data.sudahIntervensi || 0;
        this.totalBelum = res.data.belumIntervensi || 0; */
      } catch (e) {
        this.showError('Error Ambil Data', e)
      }
    },
    async hitungIntervensi() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
        }

        const headers = { Authorization: `Bearer ${localStorage.getItem('token')}` }

        let res = null
        switch (this.activeMenu) {
          case 'anak':
            res = await axios.get(`${baseURL}/api/children/intervensi`, { headers, params })
            break
          case 'bumil':
            res = await axios.get(`${baseURL}/api/pregnancy/intervensi`, { headers, params })
            break
          default:
            return
        }

        this.totalKasus = res.data.grouping.total_case
        this.totalSudah = res.data.grouping.punya_keduanya || 0
        this.totalBelum = res.data.grouping.hanya_kunjungan || 0

        //this

        // 💚 anak
        if (this.activeMenu === 'anak') {
          this.dataLoad = res.data.detail.punya_keduanya.map((item) => this.mapToAnak(item))
          this.dataLoad_belum = res.data.detail.hanya_kunjungan.map((item) => this.mapToAnak(item))
        }

        // 💚 bumil
        if (this.activeMenu === 'bumil') {
          this.dataLoad = res.data.detail.punya_keduanya.map((item) => this.mapToBumil(item))
          this.dataLoad.push(...res.data.detail.hanya_kunjungan.map((item) => this.mapToBumil(item)))
          this.$nextTick(() => {
            this.renderBumilTrendChart(res.data.tren)
          })
        }
      } catch (e) {
        this.showError('Error Ambil Data', e)
      }
    },
    parseDate(str) {
      if (!str) return null

      // Jika sudah valid
      const d = new Date(str)
      if (!isNaN(d)) return d

      // Coba format DD-MM-YYYY atau DD/MM/YYYY
      let parts = str.includes('-') ? str.split('-') : str.split('/')
      if (parts.length === 3) {
        let [d, m, y] = parts
        return new Date(Number(y), Number(m) - 1, Number(d))
      }

      return null // gagal parse
    },

    // only anak
    async generateInfoBoxes() {
      try {
        const params = {
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
        }
        const res = await axios.get(`${baseURL}/api/children/info-boxes`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        this.infoBoxes = res.data.boxes || []
      } catch (e) {
        this.showError('Error Ambil Data', e)
      }
    },
    selectChart(type) {
      this.selectedChart = type

      this.$nextTick(() => {
        this.renderDetailChart()

        setTimeout(() => {
          const detailEl = document.getElementById('detail-section')
          if (detailEl) {
            detailEl.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            })
          }
        }, 150)
      })
    },

    // ✅ METHOD BARU: Close Detail
    closeDetail() {
      // Destroy chart detail sebelum close
      if (this.chartInstances.detail) {
        this.chartInstances.detail.destroy()
        this.chartInstances.detail = null
      }

      this.selectedChart = null
    },

    // ✅ METHOD BARU: Render Detail Chart
    renderDetailChart() {
      const canvas = this.$refs.pieChart_detail
      if (!canvas) return

      const ctx = canvas.getContext('2d')

      // Destroy old chart
      if (this.chartInstances.detail) {
        this.chartInstances.detail.destroy()
      }

      const data = this.detailData
      if (!data.length) return

      const labels = data.map(row => row.status)
      const values = data.map(row => parseFloat(row.persen) || 0)
      const colors = data.map(row => row.color)

      this.chartInstances.detail = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 15
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  const label = context.label || ''
                  const value = context.parsed || 0
                  return `${label}: ${value}%`
                }
              }
            },
            datalabels: {
              color: '#fff',
              font: {
                size: 12,
                weight: 'bold'
              },
              formatter: (value) => {
                return value > 0 ? `${value}%` : ''
              }
            }
          }
        },
        plugins: [ChartDataLabels]
      })
    },

    // ✅ MODIFIKASI renderChart() yang sudah ada - TIDAK PERLU DIUBAH BANYAK
    // Hanya pastikan menggunakan chartInstances yang benar
    renderChart(refName, dataTable, colors, labelKey = 'status', valueKey = 'persen') {
      let canvasRef = this.$refs[refName]

      if (!canvasRef) {
        console.warn(`⚠️ Canvas ref "${refName}" tidak ditemukan`)
        return
      }

      const ctx = canvasRef.getContext('2d')
      if (!ctx) return

      // Tentukan instance key
      const instanceKey = refName.replace('pieChart_', '').replace('_status', 'bbtb')

      // Destroy old chart
      if (this.chartInstances[instanceKey]) {
        this.chartInstances[instanceKey].destroy()
      }

      if (!Array.isArray(dataTable) || !dataTable.length) {
        console.warn(`⚠️ Tidak ada data untuk chart ${refName}`)
        return
      }

      const labels = dataTable.map((row) => row[labelKey])
      const values = dataTable.map((row) => parseFloat(row[valueKey]) || 0)

      this.chartInstances[instanceKey] = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: labels,
          datasets: [{
            data: values,
            backgroundColor: colors,
            borderWidth: 0,
            hoverOffset: 12,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          layout: { padding: 10 },
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                title: function () {
                  return ''     // Mematikan title
                },
                label: function (context) {
                  const label = context.label || ''
                  const value = context.parsed || 0

                  // Memecah label per ±25 karakter agar tidak kepotong
                  const maxLength = 25
                  const wrappedLabel = label.match(new RegExp('.{1,' + maxLength + '}', 'g'))

                  return [...wrappedLabel, `${value}%`]
                }
              }
            },
            datalabels: {
              display: false
            }
          },
          onClick: (event, elements) => {
            if (!elements.length) return
            const index = elements[0].index
            const item = dataTable[index]

            // Mapping status untuk routing
            let state = ''
            if (item.status === 'Berat Badan Sangat Kurang (Severely Underweight)')
              state = 'Severely Underweight'
            else if (item.status === 'Berat Badan Kurang (Underweight)') state = 'Underweight'
            else if (item.status === 'Berat Badan Normal') state = 'Normal'
            else if (item.status === 'Risiko Berat Badan Lebih') state = 'Risiko BB Lebih'
            else if (item.status === 'Sangat Pendek (Severely Stunted)') state = 'Severely Stunted'
            else if (item.status === 'Pendek (Stunted)') state = 'Stunted'
            else if (item.status === 'Normal') state = 'Normal'
            else if (item.status === 'Tinggi') state = 'Tinggi'
            else if (item.status === 'Gizi Buruk (Severely Wasted)') state = 'Severely Wasted'
            else if (item.status === 'Gizi Kurang (Wasted)') state = 'Wasted'
            else if (item.status === 'Gizi Baik (Normal)') state = 'Normal'
            else if (item.status === 'Berisiko Gizi Lebih (Possible Risk of Overweight)')
              state = 'Possible Risk of Overweight'
            else if (item.status === 'Gizi Lebih (Overweight)') state = 'Overweight'
            else if (item.status === 'Obesitas (Obese)') state = 'Obese'

            const status = state

            let tipe = ''
            if (refName.includes('bb')) tipe = 'BB/U'
            else if (refName.includes('tb')) tipe = 'TB/U'
            else if (refName.includes('status')) tipe = 'BB/TB'

            this.$router.push({
              path: '/admin/anak',
              query: { tipe, status, ...this.filters }
            })
          }
        },
        plugins: [ChartDataLabels]
      })

      // Simpan juga di old pattern untuk backward compatibility
      this[refName + 'Instance'] = this.chartInstances[instanceKey]
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
        const value = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}` // ✅ pakai tanda "-"
        const label = `${bulan[d.getMonth()]} ${d.getFullYear()}`
        result.push({ label, value })
      }

      this.periodeOptions = result
      //console.log('Periode options:', this.periodeOptions)
    },
    mapToAnak(item) {
      const k = item.data_kunjungan || {}
      const intervensi = Array.isArray(item.data_intervensi) ? item.data_intervensi : []

      return {
        nik: item.nik,
        nama: item.nama,
        posyandu: item.posyandu,
        kelurahan: item.kelurahan,

        // tambahkan rumusan
        rumusan: intervensi.length ? intervensi[0].kategori : '-',

        stunting: k.tb_u && k.tb_u !== 'Normal',
        wasting: k.bb_tb && k.bb_tb !== 'Normal',
        underweight: k.bb_u && k.bb_u !== 'Normal',

        bb_stagnan: k.bb_tb && k.bb_tb.includes('Severely'),
        overweight: k.bb_tb && k.bb_tb.includes('Overweight'),

        data_kunjungan: k,
        raw: item,
      }
    },
    async renderGiziGandaGiziAnak() {
      const params = {
        posyandu: this.filters.posyandu || '',
        rw: this.filters.rw || '',
        rt: this.filters.rt || '',
        periode: this.filters.periode || '',
        provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
      }
      const res = await axios.get(`${baseURL}/api/children/get-children-double-problem`, {
        params: params,
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`,
        },
      })
      this.giziGandaAnak = res.data   // simpan data backend

      this.renderLineChart()
      this.renderBarChart()
    },
    renderLineChart() {
      if (!this.giziGandaAnak || !this.giziGandaAnak.count) return

      const labels = this.giziGandaAnak.count.label         // ["Agu 2025", "Sep 2025", ...]
      const values = this.giziGandaAnak.count.count         // [12, 9, 6, 5]

      if (this.lineChart) this.lineChart.destroy()

      const ctx = this.$refs.lineChart?.getContext('2d')
      if (!ctx) return

      this.lineChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Jumlah anak tidak membaik',
              data: values,
              borderColor: 'goldenrod',
              backgroundColor: 'rgba(255, 215, 0, 0.3)',
              fill: true,
              tension: 0.3,
              pointRadius: 4,
              pointBackgroundColor: 'goldenrod',
            }
          ]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true
            },
            x: {
              ticks: {
                autoSkip: false,
                font: { size: 14 }
              }
            }
          }
        }
      })
    },
    renderBarChart() {
      if (!this.giziGandaAnak || !this.giziGandaAnak.posyandu) return

      const labels = this.giziGandaAnak.posyandu.name
      const values = this.giziGandaAnak.posyandu.count

      if (this.barChart) this.barChart.destroy()

      const ctx = this.$refs.barChart.getContext('2d')
      this.barChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Anak Tidak Membaik',
              data: values,
              backgroundColor: 'rgba(255, 99, 132, 0.6)',
              borderRadius: 8,
              maxBarThickness: 40,
            }
          ]
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true },
            x: {
              ticks: {
                autoSkip: false,
                font: { size: 11 }
              }
            }
          }
        }
      })
    },
    async renderFunnelChart() {
      this.$nextTick(() => {
        const canvas = this.$refs.funnelChart
        if (!canvas) return

        const ctx = canvas.getContext('2d')

        const dataSudah = this.dataLoad || [] // anak punya keduanya
        const dataBelum = this.dataLoad_belum || [] // anak hanya_kunjungan

        if (!dataSudah.length && !dataBelum.length) return

        // 🔎 Tentukan periode filter
        let targetYear, targetMonth
        if (this.filters?.periode) {
          const [y, m] = this.filters.periode.split('-').map(Number)
          targetYear = y
          targetMonth = m
        } else {
          const now = new Date()
          const defaultDate = new Date(now.getFullYear(), now.getMonth() - 1, 1)
          // eslint-disable-next-line no-unused-vars
          targetYear = defaultDate.getFullYear()
          // eslint-disable-next-line no-unused-vars
          targetMonth = defaultDate.getMonth() + 1
        }

        // 🧩 Daftar kategori ASLI (Digunakan untuk logic counter)
        const jenisList = ['MBG', 'KIE', 'Bansos', 'PMT', 'Lainnya', 'Belum Mendapatkan Bantuan']

        // 🧩 Daftar kategori UNTUK DISPLAY (Menggunakan Array untuk Wrapping Text pada Chart)
        const jenisListDisplay = [
          'MBG',
          'KIE',
          'Bansos',
          'PMT',
          ['Bantuan', 'Lainnya'],
          ['Belum Mendapatkan', 'Bantuan'],
        ]

        // ==========================
        // 1️⃣ Inisialisasi counter kategori
        // ==========================
        const counter = Object.fromEntries(jenisList.map((j) => [j, 0]))

        // ==========================
        // 2️⃣ Hitung intervensi anak yang punya keduanya
        // ==========================
        dataSudah.forEach((anak) => {
          const intervensi = anak.raw?.data_intervensi
          if (!Array.isArray(intervensi) || !intervensi.length) return

          intervensi.forEach((itv) => {
            const kategori = itv.kategori?.trim() || 'Lainnya'
            if (counter[kategori] !== undefined) {
              counter[kategori]++
            } else {
              counter['Lainnya']++
            }
          })
        })

        // ==========================
        // 3️⃣ Pastikan total 'Sudah' = totalKasus (grouping.punya_keduanya)
        // ==========================
        const totalSudahChart = Object.keys(counter)
          .filter((k) => k !== 'Belum Mendapatkan Bantuan')
          .reduce((sum, k) => sum + counter[k], 0)

        const diff = (this.totalSudah || 0) - totalSudahChart
        if (diff > 0) {
          counter['Lainnya'] += diff // selisih masuk Lainnya
        }

        // ==========================
        // 4️⃣ Hitung Belum Mendapatkan Bantuan dari dataBelum
        // ==========================
        counter['Belum Mendapatkan Bantuan'] = dataBelum.length || 0

        // ==========================
        // 5️⃣ Render chart
        // ==========================
        const counts = jenisList.map((j) => counter[j])

        if (this.funnelChart) this.funnelChart.destroy()

        const isMobile = window.innerWidth < 576;
        const isTablet = window.innerWidth >= 576 && window.innerWidth < 992;

        let labelFont;

        if (isMobile) labelFont = 10;
        else if (isTablet) labelFont = 12;
        else labelFont = 12;

        this.funnelChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: jenisListDisplay,
            datasets: [
              {
                data: counts,
                backgroundColor: ['#006341', '#007d52', '#009562', '#6fa287', '#6d8b7b', '#ea7f7f'],
              },
            ],
          },
          options: {
            indexAxis: 'y',
            maintainAspectRatio: false,
            responsive: true,

            layout: {
              padding: {
                left: 0,
                right: 0,
              },
            },

            scales: {
              y: {
                ticks: {
                  autoSkip: false,
                  font: { size: labelFont },
                },
                grid: {
                  drawBorder: false,
                },
              },

              x: {
                offset: false,        // ⬅ hilangkan padding kanan-kiri default
                beginAtZero: true,
                suggestedMin: 0,
                suggestedMax: Math.max(...counts) + 2,  // ⬅ biar bar max memenuhi area

                grid: {
                  drawBorder: false,
                  color: 'rgba(0,0,0,0.1)',
                  borderDash: [4, 4],
                },

                ticks: {
                  callback: (v) => v, // tampilkan semua angka
                  autoSkip: false,    // ⬅ angka X tidak diskip
                },
              },
            },

            plugins: {
              legend: { display: false },
              datalabels: {
                color: '#fff',
                anchor: 'center',
                align: 'center',
                font: {
                  weight: 'bold',
                  size: 12,
                },
              },
            },
          },
        });
      })
    },

    // only Bumil
    /* async generateIndikatorBumilBulanan() {
      try {
        //this.isLoading = true;

        const params = {
          kelurahan: this.filters.kelurahan || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || getNowYearMonth(), // contoh: 'Agu 2025'
        }

        const res = await axios.get(`${baseURL}/api/pregnancy/indikator-bulanan`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })
        const { labels, indikator } = res.data || {}

        // kalau backend kirim kosong, tetap buat struktur default
        if (!labels?.length || !indikator) {
          this.bulanLabels = this.getLast12Months()
          this.indikatorData = {
            KEK: Array(12).fill(0),
            Anemia: Array(12).fill(0),
            Berisiko: Array(12).fill(0),
            Normal: Array(12).fill(0),
          }
          return
        }

        this.bulanLabels = labels
        this.indikatorData = indikator

        // render chart
        this.$nextTick(() => {
          this.renderBumilTrendChart()
        })
        //console.log('✅ indikatorData:', this.indikatorData);
      } catch (err) {
        console.error('❌ Gagal memuat indikator bumil bulanan:', err)
        this.bulanLabels = this.getLast12Months()
        this.indikatorData = {
          KEK: Array(12).fill(0),
          Anemia: Array(12).fill(0),
          Berisiko: Array(12).fill(0),
          Normal: Array(12).fill(0),
        }
      } finally {
        //this.isLoading = false;
      }
    }, */
    mapToBumil(item) {
      const intervensi = item.data_intervensi?.length ? item.data_intervensi : []

      const mapped = ['MBG', 'KIE', 'Bansos', 'PMT']

      const normalizeJenis = (rawJenis) => {
        // jika rawJenis ada di jenisList, pakai itu
        if (mapped.includes(rawJenis)) {
          return rawJenis
        }

        // jika tidak ada → anggap "Bantuan Lainnya"
        return 'Bantuan Lainnya'
      }

      return {
        nik: item.nik,
        nama: item.nama,
        kelurahan: item.kelurahan,
        posyandu: item.posyandu,
        rt: item.rt,
        rw: item.rw,
        umur: item.umur ?? '-',
        anemia: item.data_kunjungan?.status_gizi_hb == 'Anemia',
        kek: item.data_kunjungan?.status_gizi_lila == 'KEK',
        risiko: item.data_kunjungan?.status_risiko_usia == 'Berisiko',
        intervensi: intervensi.length ? normalizeJenis(intervensi[0].kategori) : '-',
        raw: {
          kunjungan: item.data_kunjungan || null,
          intervensi: item.data_intervensi || [],
        },
      }
    },
    generateBumilSummary(dataTableBumil) {
      const summary = {
        KEK: { sudahBumil: 0, belumBumil: 0 },
        ANEMIA: { sudahBumil: 0, belumBumil: 0 },
        'Risiko Tinggi': { sudahBumil: 0, belumBumil: 0 }, // Mengganti 'BERISIKO'
      }

      if (!dataTableBumil || dataTableBumil.length === 0) {
        return summary
      }

      dataTableBumil.forEach((ibu) => {
        // KEK
        if (ibu.kek === 'Ya') {
          if (ibu.kek_intervensi === 'Ya') {
            summary.KEK.sudahBumil++
          } else if (ibu.kek_intervensi === 'Tidak') {
            summary.KEK.belumBumil++
          }
        }

        // ANEMIA
        if (ibu.anemia === 'Ya') {
          if (ibu.anemia_intervensi === 'Ya') {
            summary.ANEMIA.sudahBumil++
          } else if (ibu.anemia_intervensi === 'Tidak') {
            summary.ANEMIA.belumBumil++
          }
        }

        // BERISIKO (Risti)
        if (ibu.risti === 'Ya') {
          if (ibu.risti_intervensi === 'Ya') {
            summary['Risiko Tinggi'].sudahBumil++
          } else if (ibu.risti_intervensi === 'Tidak') {
            summary['Risiko Tinggi'].belumBumil++
          }
        }
      })

      return summary
    },
    async renderBumilChart() {
      let apiDataBumil = []

      // 1. Ambil Data dari API
      try {
        const params = {
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
          periode: this.filters.periode || '',
        }

        const res = await axios.get(`${baseURL}/api/pregnancy/intervensi-summary`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        apiDataBumil = res.data.dataTable_bumil || []
      } catch (error) {
        console.error('❌ Gagal mengambil data intervensi ibu hamil:', error)
        apiDataBumil = []
      }

      // 2. Olah Data menjadi Summary (KEK, ANEMIA, BERISIKO)
      const summary = this.generateBumilSummary(apiDataBumil)

      // Log hasil pengolahan data
      //console.log('📊 Summary Hasil Pengolahan:', summary)

      // 3. Setup Chart
      const ctx = this.$refs.bumilChart?.getContext('2d')
      if (!ctx) {
        console.error(
          '⚠️ Canvas element (ref="bumilChart") tidak ditemukan atau tidak memiliki konteks 2D.',
        )
        return
      }
      if (this.bumilChart) this.bumilChart.destroy()

      // Pengecekan data aman dan persiapan data untuk Chart.js
      const safeSummary =
        summary && Object.keys(summary).length
          ? summary
          : {
            KEK: { sudahBumil: 0, belumBumil: 0 },
            ANEMIA: { sudahBumil: 0, belumBumil: 0 },
            BERISIKO: { sudahBumil: 0, belumBumil: 0 },
          }

      const labels = Object.keys(safeSummary)
      let sudahBumil = labels.map((key) => safeSummary[key].sudahBumil)
      let belumBumil = labels.map((key) => safeSummary[key].belumBumil)

      if (sudahBumil.length == 0) {
        sudahBumil = labels.map(() => 0)
      }

      if (belumBumil.length == 0) {
        belumBumil = labels.map(() => 0)
      }
      //console.log('kok label nya cuma 2', labels, belumBumil, sudahBumil);


      // 4. Inisialisasi Chart
      this.bumilChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Belum Dapat Intervensi',
              data: belumBumil,
              backgroundColor: '#C62828',
              borderRadius: 8,
              borderSkipped: false,
              datalabels: { color: '#fff' },
              minBarLength: 20,
            },
            {
              label: 'Sudah Dapat Intervensi',
              data: sudahBumil,
              backgroundColor: '#1B5E20',
              borderRadius: 8,
              borderSkipped: false,
              datalabels: { color: '#fff' },
              minBarLength: 20,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          indexAxis: 'y',
          scales: {
            x: {
              stacked: true,
              beginAtZero: true,
              ticks: { precision: 0 },
            },
            y: {
              stacked: true,
              barPercentage: 0.8,
              categoryPercentage: 0.6, // 👈 Diubah dari 0.7 menjadi 0.6 (Jarak lebih besar)
            },
          },
          plugins: {
            legend: {
              position: 'bottom',
              labels: { boxWidth: 14, padding: 16 },
            },
            tooltip: {
              callbacks: {
                label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.x}`,
              },
            },
          },
        },
      })
    },
    toggleSudahBumil(val) {
      this.isSudahBumil = val
    },
    renderBumilTrendChart(tren) {
      //console.log(tren)
      const ctx = this.$refs.bumilTrendChart?.getContext('2d')

      if (!ctx || !this.indikatorData || !this.bulanLabels) return

      // hapus chart lama kalau ada
      if (this.bumilTrendChartInstance) {
        this.bumilTrendChartInstance.destroy()
      }

      this.bumilTrendChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: tren.labels,
          datasets: [
            {
              label: 'KEK',
              data: tren.series.kek || [],
              borderColor: '#e63946', // merah
              backgroundColor: 'transparent',
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 4,
              pointBackgroundColor: '#e63946',
            },
            {
              label: 'Anemia',
              data: tren.series.anemia || [],
              borderColor: '#d4a017', // kuning
              backgroundColor: 'transparent',
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 4,
              pointBackgroundColor: '#d4a017',
            },
            {
              label: 'Risiko Tinggi',
              data: tren.series.risiko || [],
              borderColor: '#3b3bda', // biru
              backgroundColor: 'transparent',
              tension: 0.3,
              borderWidth: 2,
              pointRadius: 4,
              pointBackgroundColor: '#3b3bda',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                boxWidth: 20,
                padding: 15,
              },
            },
            title: { display: false },
          },
          scales: {
            y: {
              beginAtZero: true,
              // suggestedMax: 60,
              ticks: { stepSize: 10 },
              grid: { color: '#eee' },
            },
            x: {
              grid: { display: false },
            },
          },
        },
      })
    },
    renderIntervensiBumilChart() {
      this.$nextTick(() => {

        // Mendapatkan elemen canvas
        const canvas = this.$refs.belumBumilChart
        if (!canvas) return

        const ctx = canvas.getContext('2d')

        const data = this.dataLoad || []
        //console.log('data Bumil Nih:', data);



        // 🛑 Jika tidak ada intervensi dalam 3 bulan terakhir → tampilkan pesan
        // if (!recentIntervensi.length) {
        //   this.noIntervensiMessage = 'Tidak ada data untuk 3 bulan terakhir'
        //   return
        // }

        // 🔄 Reset pesan kalau ada data
        this.noIntervensiMessage = ''

        // Hitung frekuensi setiap jenis intervensi
        const jenisList = ['MBG', 'KIE', 'Bansos', 'PMT', 'Bantuan Lainnya', 'Belum Mendapat Bantuan']
        const mapped = ['MBG', 'KIE', 'Bansos', 'PMT']
        const normalizeJenis = (rawJenis) => {
          // jika rawJenis ada di jenisList, pakai itu
          if (mapped.includes(rawJenis)) {
            return rawJenis
          }
          if (rawJenis == "-") {
            return "Belum Mendapat Bantuan"
          }
          // jika tidak ada → anggap "Bantuan Lainnya"
          return 'Bantuan Lainnya'
        }


        const counts = jenisList.map(
          (jenis) => data.filter((i) => normalizeJenis(i.intervensi) == jenis).length
        )

        if (this.belumChart) this.belumChart.destroy()

        // Inisialisasi Chart
        this.belumChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: jenisList,
            datasets: [
              {
                data: counts,
                backgroundColor: ['#006341', '#007d52', '#009562', '#6fa287', '#6d8b7b', '#ea7f7f'],
                color: '#FFFFFF',
              },
            ],
          },
          options: {
            indexAxis: 'y',
            plugins: {
              legend: { display: false },
              datalabels: {
                color: '#FFFFFF',
                anchor: 'center',
                align: 'center',
                font: { weight: 'bold' },
                formatter: (value) => value || '0',
              },
            },
            scales: { x: { beginAtZero: true } },
          },
        })
      })
    },

    // only Catin
    async generateIndikatorCatinBulanan() {
      try {
        const params = {
          provinsi: this.filters.provinsi || '',
          kota: this.filters.kota || '',
          kecamatan: this.filters.kecamatan || '',
          kelurahan: this.filters.kelurahan || '',
          posyandu: this.filters.posyandu || '',
          rw: this.filters.rw || '',
          rt: this.filters.rt || '',
        }

        const res = await axios.get(`${baseURL}/api/bride/indikator-bulanan`, {
          params,
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
          },
        })

        const { labels, indikator } = res.data || {}

        // 📌 Jika backend kirim kosong, buat struktur default 12 bulan
        if (!labels?.length || !indikator) {
          this.bulanLabels = this.getLast12Months()
          this.indikatorCatin = {
            KEK: Array(12).fill(0),
            Anemia: Array(12).fill(0),
            Berisiko: Array(12).fill(0),
          }
          return
        }

        // ✔ Jika data ada, langsung assign
        this.bulanLabels = labels
        this.indikatorCatin = indikator

        //console.log('📌 Labels Catin:', this.bulanLabels)
        //console.log('📌 Indikator Catin:', this.indikatorCatin)
      } catch (err) {
        console.error('❌ Gagal memuat indikator catin bulanan:', err)

        // fallback default
        this.bulanLabels = this.getLast12Months()
        this.indikatorCatin = {
          KEK: Array(12).fill(0),
          Anemia: Array(12).fill(0),
          Berisiko: Array(12).fill(0),
        }
      }
    },
  },
  computed: {
    // Sudah
    filteredAnak() {
      if (!this.dataLoad) return []

      const arr = Array.isArray(this.dataLoad) ? this.dataLoad : Object.values(this.dataLoad)

      //console.log('📌 isi dataLoad:', this.dataLoad);

      const q = this.searchQuery?.toLowerCase() ?? ''

      return arr.filter((item) => {
        const nama = item.nama?.toLowerCase() || ''
        const nik = item.nik || ''

        const kunjungan = item.data_kunjungan || {}
        const ortu = kunjungan.nama_ortu?.toLowerCase() || ''
        const rt = kunjungan.rt?.toString() || ''
        const rw = kunjungan.rw?.toString() || ''
        const posyandu = item.posyandu?.toLowerCase() || ''

        return (
          nama.includes(q) ||
          nik.includes(q) ||
          ortu.includes(q) ||
          rt.includes(q) ||
          rw.includes(q) ||
          posyandu.includes(q)
        )
      })
    },
    paginatedAnak() {
      //console.log('⏩ paginatedAnak DIPANGGIL');
      const filtered = this.filteredAnak
      const start = (this.currentPage - 1) * this.perPage
      return filtered.slice(start, start + this.perPage)
    },
    totalPagesAnak() {
      return Math.ceil(this.filteredAnak.length / this.perPage)
    },
    paginationNumbersAnak() {
      const pages = []
      const total = this.totalPagesAnak

      if (total <= 3) {
        for (let i = 1; i <= total; i++) pages.push(i)
        return pages
      }

      if (this.currentPage <= 3) {
        return [1, 2, 3, '...', total]
      }

      if (this.currentPage >= total - 2) {
        return [1, '...', total - 2, total - 1, total]
      }

      return [1, '...', this.currentPage - 1, this.currentPage, this.currentPage + 1, '...', total]
    },
    filteredBumil() {
      if (!this.dataLoad) return []

      const arr = Array.isArray(this.dataLoad) ? this.dataLoad : Object.values(this.dataLoad)

      const q = this.searchQuery?.toLowerCase() ?? ''

      return arr.filter((item) => {
        const nama = item.nama?.toLowerCase() || ''
        const nik = item.nik || ''

        const kunjungan = item.data_kunjungan || {}
        const ortu = kunjungan.nama_ortu?.toLowerCase() || ''
        const rt = kunjungan.rt?.toString() || ''
        const rw = kunjungan.rw?.toString() || ''
        const posyandu = item.posyandu?.toLowerCase() || ''

        return (
          nama.includes(q) ||
          nik.includes(q) ||
          ortu.includes(q) ||
          rt.includes(q) ||
          rw.includes(q) ||
          posyandu.includes(q)
        )
      })
    },
    paginatedBumil() {
      //console.log('⏩ paginatedAnak DIPANGGIL');
      const filtered = this.filteredBumil
      //console.log(filtered);
      const start = (this.currentPage - 1) * this.perPage
      return filtered.slice(start, start + this.perPage)
    },
    totalPagesBumil() {
      return Math.ceil(this.filteredBumil.length / this.perPage)
    },
    paginationNumbersBumil() {
      const pages = []
      const total = this.totalPagesBumil

      if (total <= 3) {
        for (let i = 1; i <= total; i++) pages.push(i)
        return pages
      }

      if (this.currentPage <= 3) {
        return [1, 2, 3, '...', total]
      }

      if (this.currentPage >= total - 2) {
        return [1, '...', total - 2, total - 1, total]
      }

      return [1, '...', this.currentPage - 1, this.currentPage, this.currentPage + 1, '...', total]
    },

    //Belum
    filteredAnak_belum() {
      if (!this.dataLoad_belum) return []

      const arr = Array.isArray(this.dataLoad_belum)
        ? this.dataLoad_belum
        : Object.values(this.dataLoad_belum)

      //console.log('📌 isi dataLoad_belum :', this.dataLoad_belum );

      const q = this.searchQuery?.toLowerCase() ?? ''

      return arr.filter((item) => {
        const nama = item.nama?.toLowerCase() || ''
        const nik = item.nik || ''

        const kunjungan = item.data_kunjungan || {}
        const ortu = kunjungan.nama_ortu?.toLowerCase() || ''
        const rt = kunjungan.rt?.toString() || ''
        const rw = kunjungan.rw?.toString() || ''
        const posyandu = item.posyandu?.toLowerCase() || ''

        return (
          nama.includes(q) ||
          nik.includes(q) ||
          ortu.includes(q) ||
          rt.includes(q) ||
          rw.includes(q) ||
          posyandu.includes(q)
        )
      })
    },
    paginatedAnak_belum() {
      //console.log('⏩ paginatedAnak DIPANGGIL');
      const filtered = this.filteredAnak_belum
      const start = (this.currentPage - 1) * this.perPage
      return filtered.slice(start, start + this.perPage)
    },
    totalPagesAnak_belum() {
      return Math.ceil(this.filteredAnak_belum.length / this.perPage)
    },
    paginationNumbersAnak_belum() {
      const pages = []
      const total = this.totalPagesAnak_belum

      if (total <= 3) {
        for (let i = 1; i <= total; i++) pages.push(i)
        return pages
      }

      if (this.currentPage <= 3) {
        return [1, 2, 3, '...', total]
      }

      if (this.currentPage >= total - 2) {
        return [1, '...', total - 2, total - 1, total]
      }

      return [1, '...', this.currentPage - 1, this.currentPage, this.currentPage + 1, '...', total]
    },
    filteredBumil_belum() {
      if (!this.dataLoad_belum) return []

      const arr = Array.isArray(this.dataLoad_belum)
        ? this.dataLoad_belum
        : Object.values(this.dataLoad_belum)

      const q = this.searchQuery?.toLowerCase() ?? ''

      return arr.filter((item) => {
        const nama = item.nama_ibu?.toLowerCase() || ''
        const nik = item.nik_ibu || ''
        const posyandu = item.posyandu?.toLowerCase() || ''

        // dari data_kunjungan di backend → mapToBumil sudah menyimpan langsung
        const rt = item.rt?.toString() || ''
        const rw = item.rw?.toString() || ''

        return (
          nama.includes(q) ||
          nik.includes(q) ||
          rt.includes(q) ||
          rw.includes(q) ||
          posyandu.includes(q)
        )
      })
    },
    paginatedBumil_belum() {
      //console.log('⏩ paginatedAnak DIPANGGIL');
      const filtered = this.filteredBumil_belum
      const start = (this.currentPage - 1) * this.perPage
      return filtered.slice(start, start + this.perPage)
    },
    totalPagesBumil_belum() {
      return Math.ceil(this.filteredBumil_belum.length / this.perPage)
    },
    paginationNumbersBumil_belum() {
      const pages = []
      const total = this.totalPagesBumil_belum

      if (total <= 3) {
        for (let i = 1; i <= total; i++) pages.push(i)
        return pages
      }

      if (this.currentPage <= 3) {
        return [1, 2, 3, '...', total]
      }

      if (this.currentPage >= total - 2) {
        return [1, '...', total - 2, total - 1, total]
      }

      return [1, '...', this.currentPage - 1, this.currentPage, this.currentPage + 1, '...', total]
    },
    /* periodeLabel() {
      // Jika user pilih ALL → tampilkan bulan berjalan
      if (!this.filters.periode) {
        return new Date().toLocaleString('id-ID', { month: 'long', year: 'numeric' })
      }

      const [year, month] = this.filters.periode.split('-')
      const date = new Date(year, month - 1, 1)
      return date.toLocaleString('id-ID', { month: 'long', year: 'numeric' })
    }, */

    filteredAnakGabungan() {
      if (!this.dataLoad) return []

      // gabungkan data sudah dan belum
      const sudah = Array.isArray(this.dataLoad) ? this.dataLoad : Object.values(this.dataLoad)
      const belum = Array.isArray(this.dataLoad_belum)
        ? this.dataLoad_belum
        : Object.values(this.dataLoad_belum)

      // tambahkan property rumusan untuk yang belum intervensi
      const belumDenganRumusan = belum.map((item) => ({
        ...item,
        rumusan: 'Belum mendapatkan intervensi',
      }))

      const arr = [...sudah, ...belumDenganRumusan]

      // filter search
      const q = this.searchQuery?.toLowerCase() ?? ''
      return arr.filter((item) => {
        const nama = item.nama?.toLowerCase() || ''
        const nik = item.nik || ''
        const kunjungan = item.data_kunjungan || {}
        const ortu = kunjungan.nama_ortu?.toLowerCase() || ''
        const rt = kunjungan.rt?.toString() || ''
        const rw = kunjungan.rw?.toString() || ''
        const posyandu = item.posyandu?.toLowerCase() || ''

        return (
          nama.includes(q) ||
          nik.includes(q) ||
          ortu.includes(q) ||
          rt.includes(q) ||
          rw.includes(q) ||
          posyandu.includes(q)
        )
      })
    },

    paginatedAnakGabungan() {
      const filtered = this.filteredAnakGabungan
      const start = (this.currentPage - 1) * this.perPage
      return filtered.slice(start, start + this.perPage)
    },

    totalPagesAnakGabungan() {
      return Math.ceil(this.filteredAnakGabungan.length / this.perPage)
    },

    paginationNumbersAnakGabungan() {
      const total = this.totalPagesAnakGabungan
      const current = this.currentPage
      // eslint-disable-next-line no-unused-vars
      const delta = 2
      let range = []

      if (total <= 3) {
        for (let i = 1; i <= total; i++) range.push(i)
        return range
      }

      if (current <= 3) return [1, 2, 3, '...', total]
      if (current >= total - 2) return [1, '...', total - 2, total - 1, total]

      return [1, '...', current - 1, current, current + 1, '...', total]
    },

    detailTitle() {
      const titles = {
        'bb': 'Berat Badan / Usia',
        'tb': 'Tinggi Badan / Usia',
        'bbtb': 'Berat Badan / Tinggi Badan'
      }
      return titles[this.selectedChart] || ''
    },

    detailData() {
      const dataMap = {
        'bb': this.dataTable_bb,
        'tb': this.dataTable_tb,
        'bbtb': this.dataTable_bbtb
      }
      return dataMap[this.selectedChart] || []
    },

    detailLink() {
      const linkMap = {
        bb: '/admin/dashboard/detail?tipe=bbu',
        tb: '/admin/dashboard/detail?tipe=tbu',
        bbtb: '/admin/dashboard/detail?tipe=bbtb',
      }

      const base = linkMap[this.selectedChart]
      if (!base) return '#'

      const params = new URLSearchParams()

      Object.entries(this.filters).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
          params.append(key, value)
        }
      })

      return `${base}&${params.toString()}`
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
  async mounted() {
    this.isLoading = true
    this.isMobile = window.innerWidth < 768

    window.addEventListener('resize', () => {
      this.isMobile = window.innerWidth < 768
    })

    try {
      // await this.$nextTick()
      this.periodeLabel = this.getPeriodeLabel()
      this.$nextTick(() => {
        this.renderChart(
          'pieChart_bb',
          this.dataTable_bb,
          this.dataTable_bb.map(r => r.color)
        )
        this.renderChart(
          'pieChart_tb',
          this.dataTable_tb,
          this.dataTable_tb.map(r => r.color)
        )
        this.renderChart(
          'pieChart_status',
          this.dataTable_bbtb,
          this.dataTable_bbtb.map(r => r.color)
        )
      })

      // 1️⃣ Ambil wilayah user
      await this.getWilayahUser()

      console.log(this.filters);

      // 3️⃣ Generate bulan terakhir 12 bulan
      this.bulanLabels = this.getLast12Months()

      // 4️⃣ Jalankan logika indikator bulanan untuk catin

      // set menu default → 'anak'
      this.setMenu('anak')
      //await this.generateIndikatorCatinBulanan();

      // 5️⃣ Set menu default → anak
      await this.hitungStatistik() // hitung ulang sesuai menu 'anak'
      await this.generateInfoBoxes()
      await this.generateDataTable()
      await this.masalahGanda()
      await this.hitungIntervensi()
      await this.generateInfoBoxes()

      this.generateIndikatorBumilBulanan()

      this.renderGiziGandaGiziAnak()
      this.renderFunnelChart()
      //this.renderSudahChart();
      this.renderBumilChart()
      this.rendersvgChart()
      this.rendersvgChart_Bumil()
      this.rendersvgChart_Catin()
      //this.generateIndikatorCatinBulanan();
      // 6️⃣ Generate data table sesuai tipe menu
      //this.generateDataTableCatin();

      // 7️⃣ Fetch stats tambahan jika perlu
      await this.fetchStats()

      // 8️⃣ Generate pilihan periode
      this.generatePeriodeOptions()

      // 9️⃣ Pastikan filter kelurahan sudah terisi
      this.filters.kelurahan = this.kelurahan

      // 11️⃣ Setup resize listener untuk responsive
      this.handleResize()
      window.addEventListener('resize', this.handleResize)
    } catch (err) {
      console.error('❌ Error loading data:', err)
    } finally {
      // Delay kecil biar loading animation nggak blink
      setTimeout(() => {
        this.isLoading = false
      }, 300)
    }
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize)
    window.removeEventListener('resize', this.handleResize)

    // ✅ TAMBAHAN: Destroy semua chart instances
    Object.values(this.chartInstances).forEach(chart => {
      if (chart) chart.destroy()
    })
  },
  watch: {
    'filters.posyandu'(val) {
      this.handlePosyanduChange(val)
    },
    async activeMenu() {
      //console.log('🔄 Menu berganti, refresh data dan chart')
      await this.hitungIntervensi()
      await this.hitungStatistik() // hitung ulang sesuai menu 'anak'
      await this.generateInfoBoxes()
      await this.generateDataTable()
      await this.masalahGanda()

      if (this.activeMenu === 'anak') {
        this.renderGiziGandaGiziAnak()
        this.rendersvgChart()
      }

      if (this.activeMenu === 'bumil') {
        this.rendersvgChart_Bumil()
        this.renderBumilChart()
        this.renderIntervensiBumilChart()
        this.generateIndikatorBumilBulanan()
      }
      if (this.activeMenu === 'catin') {
        this.rendersvgChart_Catin()
        this.generateIndikatorCatinBulanan()
      }

      this.renderFunnelChart()
    },
  },
}
</script>
