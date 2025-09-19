<template>
  <div class="dashboard-wrapper">
    <!-- Header -->
    <HeaderAdmin :is-collapsed="isCollapsed" @toggle-sidebar="toggleSidebar" />
    <div class="d-flex flex-column flex-md-row">
      <!-- Sidebar -->
      <NavbarAdmin :is-collapsed="isCollapsed" />
      <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <!-- Main Content -->
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
                <h1 class="text-light mb-5">
                  <span class="text-light fw-normal fs-6">Selamat datang,</span> <br />
                  {{ username }}
                </h1>
                <p class="text-light small">
                  <span class="bg-light rounded-circle p-2"
                    ><i class="bi bi-calendar text-primary"></i
                  ></span>
                  &nbsp; Anda memiliki
                  <router-link to="/admin/jadwal" class="fw-bold text-light text-decoration-none">
                    1 jadwal intervensi
                  </router-link>
                  hari ini.
                </p>
              </div>

              <!-- Kanan: Gambar -->
              <div class="mt-3 mt-md-0">
                <img
                  src="/src/assets/admin.png"
                  alt="Welcome"
                  class="img-fluid welcome-img"
                  style="max-width: 280px"
                />
              </div>
            </div>
          </div>

          <!-- Statistic Cards -->
          <div class="row justify-content-center mt-4">
            <div
              v-for="(stat, index) in stats"
              :key="index"
              class="col-6 col-md-4 col-lg-3 col-xl stat-col p-2"
            >
              <div class="card stat-card h-100 border border-1 border-primary shadow-sm">
                <div
                  class="card-body p-3 d-flex flex-column align-items-center justify-content-center"
                >
                  <!-- Icon wrapper -->
                  <div class="icon-wrapper mb-2">
                    <i :class="stat.icon + ' fs-4'"></i>
                  </div>

                  <!-- Title -->
                  <h6 class="card-title text-muted text-uppercase small mb-1">
                    {{ stat.title }}
                  </h6>

                  <!-- Value -->
                  <p class="fw-bold fs-5 mb-0 text-dark">
                    {{ stat.value }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Filter Form -->
          <div class="mt-5">
            <div class="card border-0 shadow-sm p-3 bg-light">
              <h3 class="text-primary fw-bold py-2">Filter:</h3>
              <form class="row g-3 align-items-end">
                <div class="col-3">
                  <label for="kecamatan">Kecamatan</label>
                  <select name="kecamatan" id="kecamatan" class="form-select">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                  </select>
                </div>
                <div class="col-3">
                  <label for="kelurahan">Kelurahan</label>
                  <select name="kelurahan" id="kelurahan" class="form-select">
                    <option value="1">Januari</option>
                    <option value="2">Februari</option>
                    <option value="3">Maret</option>
                  </select>
                </div>
                <div class="col-4">
                  <label for="periode"> Periode</label>
                  <input
                    type="text"
                    id="daterange"
                    class="form-control"
                    placeholder="Pilih rentang tanggal"
                    readonly
                  />
                </div>
                <div class="col-2">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Cari
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Tabs -->
          <div class="mt-5">
            <div class="d-flex justify-content-center">
              <ul class="nav nav-pills mb-3" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link active"
                    id="home-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#home-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="home-tab-pane"
                    aria-selected="true"
                  >
                    Status Gizi Anak
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="profile-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#profile-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="profile-tab-pane"
                    aria-selected="false"
                  >
                    Status Kesehatan Ibu Hamil
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button
                    class="nav-link"
                    id="contact-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#contact-tab-pane"
                    type="button"
                    role="tab"
                    aria-controls="contact-tab-pane"
                    aria-selected="false"
                  >
                    Calon Pengantin Berisiko
                  </button>
                </li>
              </ul>
            </div>
            <div class="tab-content" id="myTabContent">
              <!-- Tab 1 -->
              <div
                class="tab-pane fade show active"
                id="home-tab-pane"
                role="tabpanel"
                tabindex="0"
              >
                <!-- Title -->
                <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                  <h2 class="fw-bold">Status Gizi Anak</h2>
                  <a href="/admin/grafik" class="text-decoration-none">Selengkapnya...</a>
                </div>

                <!-- PIE CHART-->
                <div class="row mb-4">
                  <div class="col-12 col-md-6">
                    <!-- Berat Badan / Usia -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <h4 class="fw-bold text-primary">Berat Badan / Usia</h4>
                      <div class="row">
                        <!-- Table -->
                        <div class="col-12 col-md-8">
                          <table class="table table-borderless align-middle">
                            <tbody>
                              <tr>
                                <td class="text-additional fw-bold">Status</td>
                                <td class="text-muted fw-bold">Jumlah</td>
                                <td class="text-muted fw-bold">Persen</td>
                                <td class="text-muted fw-bold">Tren</td>
                              </tr>
                              <tr v-for="(row, index) in dataTable_bb" :key="index">
                                <td>{{ row.status }}</td>
                                <td>{{ row.jumlah }}</td>
                                <td>{{ row.persen }} %</td>
                                <td :class="row.trenClass">
                                  <span v-if="row.tren !== '-'">
                                    <i :class="row.trenIcon"></i> {{ row.tren }}
                                  </span>
                                  <span v-else>-</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>

                        <!-- Chart -->
                        <div class="col-12 col-md-4">
                          <canvas ref="pieChart_bb"></canvas>
                        </div>
                      </div>
                    </div>

                    <!-- Tinggi Badan / Usia -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <h4 class="fw-bold text-primary">Tinggi Badan / Usia</h4>
                      <div class="row">
                        <div class="col-12 col-md-8">
                          <table class="table table-borderless align-middle">
                            <tbody>
                              <tr>
                                <td class="text-additional fw-bold">Status</td>
                                <td class="text-muted fw-bold">Jumlah</td>
                                <td class="text-muted fw-bold">Persen</td>
                                <td class="text-muted fw-bold">Tren</td>
                              </tr>
                              <tr v-for="(row, index) in dataTable_tb" :key="index">
                                <td>{{ row.status }}</td>
                                <td>{{ row.jumlah }}</td>
                                <td>{{ row.persen }} %</td>
                                <td :class="row.trenClass">
                                  <span v-if="row.tren !== '-'">
                                    <i :class="row.trenIcon"></i> {{ row.tren }}
                                  </span>
                                  <span v-else>-</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="col-12 col-md-4">
                          <canvas ref="pieChart_tb"></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <!-- Berat Badan / Tinggi Badan -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <h4 class="fw-bold text-primary">Berat Badan / Tinggi Badan</h4>
                      <div class="row">
                        <div class="col-12">
                          <table class="table table-borderless align-middle">
                            <tbody>
                              <tr>
                                <td class="text-additional fw-bold">Status</td>
                                <td class="text-muted fw-bold">Jumlah</td>
                                <td class="text-muted fw-bold">Persen</td>
                                <td class="text-muted fw-bold">Tren</td>
                              </tr>
                              <tr v-for="(row, index) in dataTable_status" :key="index">
                                <td>{{ row.status }}</td>
                                <td>{{ row.jumlah }}</td>
                                <td>{{ row.persen }} %</td>
                                <td :class="row.trenClass">
                                  <span v-if="row.tren !== '-'">
                                    <i :class="row.trenIcon"></i> {{ row.tren }}
                                  </span>
                                  <span v-else>-</span>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="col-12">
                          <canvas
                            ref="pieChart_status"
                            class="mx-auto d-block"
                            style="max-width: 300px; max-height: 300px"
                          ></canvas>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Statistic -->
                <div class="d-flex justify-content-center">
                  <ul class="nav nav-pills mb-3" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                      <button
                        class="nav-link active"
                        id="bb-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#bb-tab-pane"
                        type="button"
                        role="tab"
                        aria-controls="bb-tab-pane"
                        aria-selected="true"
                      >
                        Berat Badan / Usia
                      </button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button
                        class="nav-link"
                        id="tb-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#tb-tab-pane"
                        type="button"
                        role="tab"
                        aria-controls="tb-tab-pane"
                        aria-selected="false"
                      >
                        Tinggi Badan / Usia
                      </button>
                    </li>
                    <li class="nav-item" role="presentation">
                      <button
                        class="nav-link"
                        id="bbtb-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#bbtb-tab-pane"
                        type="button"
                        role="tab"
                        aria-controls="bbtb-tab-pane"
                        aria-selected="false"
                      >
                        Berat Badan / Tinggi Badan
                      </button>
                    </li>
                  </ul>
                </div>
                <div class="tab-content" id="mysubTabContent">
                  <!-- sub BB Tabs-->
                  <div
                    class="tab-pane fade show active"
                    id="bb-tab-pane"
                    role="tabpanel"
                    tabindex="0"
                  >
                    <!-- Title -->
                    <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                      <h2 class="fw-bold">Berat Badan / Usia</h2>
                    </div>

                    <!-- Statistik Berat Badan / Usia -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <table class="table table-borderless align-middle">
                        <tbody>
                          <tr>
                            <td class="text-additional fw-bold">Status</td>
                            <td class="text-muted fw-bold">Jumlah</td>
                            <td class="text-muted fw-bold">Persen</td>
                            <td class="text-muted fw-bold">Tren</td>
                          </tr>
                          <tr v-for="(row, index) in dataTable_bb" :key="index">
                            <td>{{ row.status }}</td>
                            <td>{{ row.jumlah }}</td>
                            <td>{{ row.persen }} %</td>
                            <td :class="row.trenClass">
                              <span v-if="row.tren !== '-'">
                                <i :class="row.trenIcon"></i> {{ row.tren }}
                              </span>
                              <span v-else>-</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- Statistik berdasarkan kelompok usia dan gender-->
                    <div class="row my-3">
                      <div class="col-12 col-lg-6 col-md-6">
                        <div class="card border border-primary shadow p-3 my-3">
                          <h4 class="text-primary fw-bold">Berdasarkan Kategori Usia</h4>
                          <div class="table-responsive">
                            <canvas ref="usiaChart"></canvas>
                          </div>
                        </div>
                      </div>
                      <div class="col-12 col-lg-6 col-md-6">
                        <div class="card border border-primary shadow p-3 my-3">
                          <h4 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h4>
                          <div class="row justify-content-center">
                            <div
                              class="col-md-6 col-sm-12 col-12 mb-4"
                              v-for="(item, index) in genderData"
                              :key="index"
                            >
                              <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                              <h5 class="title" :class="item.titleClass">{{ item.label }}</h5>
                              <div class="d-flex justify-content-between px-5">
                                <div>
                                  <p v-for="(cat, i) in item.categories" :key="i">{{ cat.name }}</p>
                                </div>
                                <div class="fw-bold text-end" :class="item.valueClass">
                                  <p v-for="(cat, i) in item.categories" :key="i">
                                    {{ cat.value }}
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Statistik berdasarkan 1 tahun terkahir -->
                    <div class="row my-3">
                      <div class="col-12">
                        <div class="card border border-primary shadow p-3 my-3">
                          <div class="table-responsive"><canvas ref="indiChart"></canvas></div>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="card border border-primary shadow p-3 my-3">
                          <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle text-center">
                              <thead class="table-light">
                                <tr>
                                  <th>Indikator</th>
                                  <th v-for="(bulan, idx) in bulanLabels" :key="idx">
                                    {{ bulan }}
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(values, indikator) in indikatorData" :key="indikator">
                                  <td class="fw-bold">{{ indikator }}</td>
                                  <td v-for="(val, idx) in values" :key="idx">{{ val }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- sub TB Tabs-->
                  <div
                    class="tab-pane fade"
                    id="tb-tab-pane"
                    role="tabpanel"
                    tabindex="0"
                  >
                    <!-- Title -->
                    <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                      <h2 class="fw-bold">Tinggi Badan / Usia</h2>
                    </div>

                    <!-- Statistik Tinggi Badan / Usia -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <table class="table table-borderless align-middle">
                        <tbody>
                          <tr>
                            <td class="text-additional fw-bold">Status</td>
                            <td class="text-muted fw-bold">Jumlah</td>
                            <td class="text-muted fw-bold">Persen</td>
                            <td class="text-muted fw-bold">Tren</td>
                          </tr>
                          <tr v-for="(row, index) in dataTable_tb" :key="index">
                            <td>{{ row.status }}</td>
                            <td>{{ row.jumlah }}</td>
                            <td>{{ row.persen }} %</td>
                            <td :class="row.trenClass">
                              <span v-if="row.tren !== '-'">
                                <i :class="row.trenIcon"></i> {{ row.tren }}
                              </span>
                              <span v-else>-</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- Statistik berdasarkan kelompok usia dan gender-->
                    <div class="row my-3">
                      <div class="col-12 col-lg-6 col-md-6">
                        <div class="card border border-primary shadow p-3 my-3">
                          <h4 class="text-primary fw-bold">Berdasarkan Kategori Usia</h4>
                          <div class="table-responsive">
                            <canvas ref="usiaChart_tb"></canvas>
                          </div>
                        </div>
                      </div>
                      <div class="col-12 col-lg-6 col-md-6">
                        <div class="card border border-primary shadow p-3 my-3">
                          <h4 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h4>
                          <div class="row justify-content-center">
                            <div
                              class="col-md-6 col-sm-12 col-12 mb-4"
                              v-for="(item, index) in genderData_tb"
                              :key="index"
                            >
                              <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                              <h5 class="title" :class="item.titleClass">{{ item.label }}</h5>
                              <div class="d-flex justify-content-between px-5">
                                <div>
                                  <p v-for="(cat, i) in item.categories" :key="i">{{ cat.name }}</p>
                                </div>
                                <div class="fw-bold text-end" :class="item.valueClass">
                                  <p v-for="(cat, i) in item.categories" :key="i">
                                    {{ cat.value }}
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Statistik berdasarkan 1 tahun terkahir -->
                    <div class="row my-3">
                      <div class="col-12">
                        <div class="card border border-primary shadow p-3 my-3">
                          <div class="table-responsive">
                            <canvas ref="indiChart_tb"></canvas>
                          </div>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="card border border-primary shadow p-3 my-3">
                          <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle text-center">
                              <thead class="table-light">
                                <tr>
                                  <th>Indikator</th>
                                  <th v-for="(bulan, idx) in bulanLabels" :key="idx">
                                    {{ bulan }}
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(values, indikator) in indikatorData_tb" :key="indikator">
                                  <td class="fw-bold">{{ indikator }}</td>
                                  <td v-for="(val, idx) in values" :key="idx">{{ val }}</td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- sub BB/TB Tabs-->
                  <div
                    class="tab-pane fade"
                    id="bbtb-tab-pane"
                    role="tabpanel"
                    tabindex="0"
                  >
                    <!-- Title -->
                    <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                      <h2 class="fw-bold">Berat Badan / Tinggi Badan</h2>
                    </div>

                    <!-- Statistik BB /TB -->
                    <div class="card border border-primary shadow p-3 my-3">
                      <table class="table table-borderless align-middle">
                        <tbody>
                          <tr>
                            <td class="text-additional fw-bold">Status</td>
                            <td class="text-muted fw-bold">Jumlah</td>
                            <td class="text-muted fw-bold">Persen</td>
                            <td class="text-muted fw-bold">Tren</td>
                          </tr>
                          <tr v-for="(row, index) in dataTable_status" :key="index">
                            <td>{{ row.status }}</td>
                            <td>{{ row.jumlah }}</td>
                            <td>{{ row.persen }} %</td>
                            <td :class="row.trenClass">
                              <span v-if="row.tren !== '-'">
                                <i :class="row.trenIcon"></i> {{ row.tren }}
                              </span>
                              <span v-else>-</span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- Statistik berdasarkan kelompok usia dan gender-->
                    <div class="row my-3">
                      <div class="col-12 col-lg-6 col-md-6">
                        <div class="card border border-primary shadow p-3 my-3">
                          <h4 class="text-primary fw-bold">Berdasarkan Kategori Usia</h4>
                          <div class="table-responsive">
                            <canvas ref="usiaChart_bbtb"></canvas>
                          </div>
                        </div>
                      </div>
                      <div class="col-12 col-lg-6 col-md-6">
                        <div class="card border border-primary shadow p-3 my-3">
                          <h4 class="fw-bold mb-4 text-primary">Berdasarkan Jenis Kelamin</h4>
                          <div class="row justify-content-center">
                            <div
                              class="col-md-6 col-sm-12 col-12 mb-4"
                              v-for="(item, index) in genderData_bbtb"
                              :key="index"
                            >
                              <div :class="['circle', item.circleClass]">{{ item.total }}</div>
                              <h5 class="title" :class="item.titleClass">{{ item.label }}</h5>
                              <div class="d-flex justify-content-between px-5">
                                <div>
                                  <p v-for="(cat, i) in item.categories" :key="i">{{ cat.name }}</p>
                                </div>
                                <div class="fw-bold text-end" :class="item.valueClass">
                                  <p v-for="(cat, i) in item.categories" :key="i">
                                    {{ cat.value }}
                                  </p>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Statistik berdasarkan 1 tahun terkahir -->
                    <div class="row my-3">
                      <div class="col-12">
                        <div class="card border border-primary shadow p-3 my-3">
                          <div class="table-responsive">
                            <canvas ref="indiChart_bbtb"></canvas>
                          </div>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="card border border-primary shadow p-3 my-3">
                          <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle text-center">
                              <thead class="table-light">
                                <tr>
                                  <th>Indikator</th>
                                  <th v-for="(bulan, idx) in bulanLabels" :key="idx">
                                    {{ bulan }}
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="(values, indikator) in indikatorData_bbtb" :key="indikator">
                                  <td class="fw-bold">{{ indikator }}</td>
                                  <td v-for="(val, idx) in values" :key="idx">{{ val }}</td>
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

              <!-- Tab 2 -->
              <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" tabindex="0">
                <!-- Title -->
                <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                  <h2 class="fw-bold">Status Kesehatan Ibu Hamil</h2>
                </div>

                <div class="card border-0 shadow-sm p-3">No Data Available</div>

                <div class="card border border-primary shadow p-3 my-3">
                  <div class="table-responsive">
                    <canvas ref="indiChart_pregnancy"></canvas>
                  </div>
                </div>

                <div class="card border-0 shadow-sm p-3">
                  <div class="table-responsive">
                    <table class="table table-striped">
                      <thead>
                        <tr>
                          <th>Indikator</th>
                          <th v-for="(bulan, idx) in bulanLabels" :key="idx">
                            {{ bulan }}
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(indikator, idx) in pregnancy_data" :key="idx">
                          <td>{{ indikator.nama }}</td>
                          <td v-for="(value, i) in indikator.values" :key="i">{{ value }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Tab 3 -->
              <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" tabindex="0">
                <!-- Title -->
                <div class="d-flex justify-content-between align-items-center my-3 mt-5">
                  <h2 class="fw-bold">Status Kesehatan Calon Pengantin</h2>
                </div>

                <div class="card border-0 shadow-sm p-3">
                  <p class="text-warning mb-3">
                    *Data perhitungan berdasarkan tanggal terdaftar > filter bulan > tanggal
                    menikah.
                  </p>
                  <table class="table table-borderless align-middle">
                    <tbody>
                      <tr>
                        <td class="text-muted fw-bold">Status</td>
                        <td class="text-muted fw-bold">Jumlah</td>
                        <td class="text-muted fw-bold">Persen</td>
                        <td class="text-muted fw-bold">Tren</td>
                      </tr>
                      <tr>
                        <td>Calon Pengantin Beresiko</td>
                        <td>0</td>
                        <td>0 %</td>
                        <td>0 %</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <CopyRight />
      </div>
    </div>
  </div>
</template>

<script>
import CopyRight from '@/components/CopyRight.vue'
import NavbarAdmin from '@/components/NavbarAdmin.vue'
import HeaderAdmin from '@/components/HeaderAdmin.vue'
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

document.addEventListener('DOMContentLoaded', function () {
  // eslint-disable-next-line no-undef
  new Litepicker({
    element: document.getElementById('daterange'),
    singleMode: false, // biar bisa pilih range (awal - akhir)
    format: 'DD MMM YYYY',
    numberOfMonths: 2, // tampil 2 bulan
    numberOfColumns: 2,
    lang: 'id-ID', // bahasa Indonesia
  })
})

export default {
  // eslint-disable-next-line vue/multi-word-component-names
  name: 'Dashboard',
  components: { NavbarAdmin, CopyRight, HeaderAdmin },
  data() {
    return {
      username: '',
      genderData: [
        {
          label: 'Laki - Laki',
          total: 589,
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success',
          categories: [
            { name: 'Sangat Kurang', value: 20 },
            { name: 'Kurang', value: 74 },
            { name: 'Normal', value: 393 },
            { name: 'Risiko Lebih', value: 11 },
            { name: 'Tidak Naik', value: 71 },
          ],
        },
        {
          label: 'Perempuan',
          total: 553,
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning',
          categories: [
            { name: 'Sangat Kurang', value: 17 },
            { name: 'Kurang', value: 71 },
            { name: 'Normal', value: 389 },
            { name: 'Risiko Lebih', value: 9 },
            { name: 'Tidak Naik', value: 67 },
          ],
        },
      ],
      genderData_tb: [
        {
          label: 'Laki - Laki',
          total: 589,
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success',
          categories: [
            { name: 'Sangat Pendek', value: 20 },
            { name: 'Pendek', value: 74 },
            { name: 'Normal', value: 393 },
            { name: 'Tinggi', value: 11 },
          ],
        },
        {
          label: 'Perempuan',
          total: 553,
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning',
          categories: [
            { name: 'Sangat Pendek', value: 17 },
            { name: 'Pendek', value: 71 },
            { name: 'Normal', value: 389 },
            { name: 'Tinggi', value: 9 },
          ],
        },
      ],
      genderData_bbtb: [
        {
          label: 'Laki - Laki',
          total: 589,
          circleClass: 'male-circle',
          titleClass: 'text-success',
          valueClass: 'text-success',
          categories: [
            { name: 'Gizi Buruk', value: 20 },
            { name: 'Gizi Kurang', value: 74 },
            { name: 'Gizi Baik', value: 393 },
            { name: 'Risiko Gizi Lebih', value: 11 },
            { name: 'Obesitas', value: 11 },
          ],
        },
        {
          label: 'Perempuan',
          total: 553,
          circleClass: 'female-circle',
          titleClass: 'text-warning',
          valueClass: 'text-warning',
          categories: [
            { name: 'Gizi Buruk', value: 20 },
            { name: 'Gizi Kurang', value: 74 },
            { name: 'Gizi Baik', value: 393 },
            { name: 'Risiko Gizi Lebih', value: 11 },
            { name: 'Obesitas', value: 11 },          ],
        },
      ],
      stats: [
        { title: 'Total RW', value: '1,000', icon: 'fa-solid fa-house-chimney-window' },
        { title: 'Total RT', value: '100,000', icon: 'bi bi-house-fill' },
        { title: 'Penduduk', value: '278 M', icon: 'bi bi-people-fill' },
        { title: 'Keluarga', value: '100 M', icon: 'fa-solid fa-people-roof' },
        { title: 'Balita', value: '1,234', icon: 'fa-solid fa-baby' },
        { title: 'Ibu Hamil', value: '56 K', icon: 'fa-solid fa-person-pregnant' },
        { title: 'Calon Pengantin', value: '12 K', icon: 'fa-solid fa-people-arrows' },
        { title: 'Posyandu', value: '8 K', icon: 'fa-solid fa-stethoscope' },
        { title: 'Bidan', value: '1,234', icon: 'fa-solid fa-user-nurse' },
        { title: 'Anggota TPK', value: '56', icon: 'bi bi-person-vcard-fill' },
      ],
      activities: [
        { user: 'Alice', action: 'Created new project', date: '2025-08-13' },
        { user: 'Bob', action: 'Updated profile', date: '2025-08-12' },
        { user: 'Charlie', action: 'Added new user', date: '2025-08-11' },
      ],
      months: [
        'Juli 2025',
        'Juni 2025',
        'Mei 2025',
        'April 2025',
        'Maret 2025',
        'Februari 2025',
        'Januari 2025',
        'Desember 2024',
        'November 2024',
        'Oktober 2024',
        'September 2024',
        'Agustus 2024',
      ],
      pregnancy_data: [
        { nama: 'KEK', values: [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0] },
        { nama: 'Anemia', values: [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45] },
        { nama: 'Resiko', values: [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734] },
        { nama: 'Tinggi', values: [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23] },
      ],
      dataTable_bb: [
        {
          status: 'Sangat Kurang',
          jumlah: 24,
          persen: 2.8,
          tren: '2.80%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Kurang',
          jumlah: 94,
          persen: 10.96,
          tren: '10.96',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Normal',
          jumlah: 725,
          persen: 84.5,
          tren: '84.50 %',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Risiko Lebih',
          jumlah: 15,
          persen: 1.75,
          tren: '1.75%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Tidak Naik',
          jumlah: 287,
          persen: 33.45,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
      ],
      dataTable_tb: [
        {
          status: 'Sangat Pendek',
          jumlah: 21,
          persen: 2.45,
          tren: '0.63%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Pendek',
          jumlah: 149,
          persen: 17.37,
          tren: '0.29%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Normal',
          jumlah: 688,
          persen: 80.19,
          tren: ' 0.92%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Tinggi',
          jumlah: 0,
          persen: 0,
          tren: '-',
          trenClass: 'text-muted',
          trenIcon: '',
        },
      ],
      dataTable_status: [
        {
          status: 'Gizi Buruk',
          jumlah: 4,
          persen: 0.47,
          tren: '2.80%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Gizi Kurang',
          jumlah: 20,
          persen: 2.33,
          tren: '10.96%',
          trenClass: 'text-success',
          trenIcon: 'bi bi-caret-down-fill',
        },
        {
          status: 'Gizi Baik',
          jumlah: 769,
          persen: 89.63,
          tren: ' 84.50%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Risiko Gizi Lebih',
          jumlah: 53,
          persen: 6.18,
          tren: '1.75%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Gizi Lebih',
          jumlah: 8,
          persen: 0.93,
          tren: '33.45%',
          trenClass: 'text-danger',
          trenIcon: 'bi bi-caret-up-fill',
        },
        {
          status: 'Obesitas',
          jumlah: 0,
          persen: 0,
          tren: '-',
          trenClass: 'text-muted',
          trenIcon: '',
        },
      ],
      isCollapsed: false,
      usiaChartInstance: null,
      usiaChartInstance_tb: null,
      usiaChartInstance_bbtb: null,
      kelompokUmur: ['0-5', '6-11', '12-17', '18-23', '24-35', '36-47', '48-60'],
      statusData: {
        'Sangat Kurang': [2, 3, 5, 6, 8, 4, 3],
        Kurang: [4, 6, 7, 8, 10, 7, 6],
        Normal: [10, 20, 75, 68, 170, 150, 110],
        'Risiko Lebih': [1, 1, 2, 2, 3, 2, 1],
        'Tidak Naik': [3, 5, 20, 22, 70, 60, 55],
      },
      statusData_tb: {
        'Sangat Pendek': [2, 3, 5, 6, 8, 4, 3],
        'Pendek': [4, 6, 7, 8, 10, 7, 6],
        Normal: [10, 20, 75, 68, 170, 150, 110],
        'Tinggi': [1, 1, 2, 2, 3, 2, 1],
      },
      statusData_bbtb: {
        'Gizi Buruk': [2, 3, 5, 6, 8, 4, 3],
        'Gizi Kurang': [4, 6, 7, 8, 10, 7, 6],
        'Gizi Baik': [10, 20, 75, 68, 170, 150, 110],
        'Risiko Gizi Lebih': [1, 1, 2, 2, 3, 2, 1],
        'Gizi Lebih': [1, 1, 2, 2, 3, 2, 1],
        'Obesitas': [1, 1, 2, 2, 3, 2, 1],
      },
      indiChartInstance_tb: null,
      indiChartInstance_bbtb: null,
      indiChartInstance: null,
      indiChartPregnancy: null,
      bulanLabels: [],
      indikatorData: {
        'Sangat Kurang': [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0],
        Kurang: [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45],
        Normal: [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734],
        'Risiko Lebih': [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23],
        'Tidak Naik': [199, 290, 206, 286, 294, 238, 0, 0, 0, 0, 90, 40],
      },
      indikatorData_tb: {
        'Sangat Pendek': [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0],
        'Pendek': [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45],
        'Normal': [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734],
        'Tinggi': [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23],
      },
      indikatorData_bbtb: {
        'Gizi Buruk': [29, 37, 20, 26, 24, 22, 24, 23, 56, 79, 10, 0],
        'Gizi Kurang': [134, 134, 126, 129, 134, 110, 94, 23, 67, 80, 12, 45],
        'Gizi Baik': [711, 702, 684, 723, 716, 732, 725, 706, 712, 450, 711, 734],
        'Risiko Gizi Lebih': [25, 20, 23, 22, 16, 14, 15, 90, 16, 50, 11, 23],
        'Gizi Lebih': [215, 20, 23, 22, 136, 164, 15, 90, 16, 50, 11, 23],
        'Obesitas': [205, 20, 23, 122, 216, 14, 15, 90, 16, 50, 11, 23],
      },
    }
  },
  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed
    },
    renderChart(refName, dataSource, colors) {
      const ctx = this.$refs[refName]?.getContext('2d')
      if (!ctx) return

      // hancurkan chart lama kalau ada
      if (this[refName + 'Instance']) {
        this[refName + 'Instance'].destroy()
      }

      this[refName + 'Instance'] = new Chart(ctx, {
        type: 'pie',
        data: {
          labels: dataSource.map((row) => row.status),
          datasets: [
            {
              data: dataSource.map((row) => row.persen),
              backgroundColor: colors,
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            datalabels: {
              color: '#fff',
              formatter: (value, context) => {
                const label = context.chart.data.labels[context.dataIndex]
                return `${label}\n${value}%`
              },
              font: {
                weight: 'bold',
                size: 12,
              },
            },
          },
        },
      })
    },
    renderUsiaChart() {
      const ctx = this.$refs.usiaChart.getContext('2d')

      if (this.usiaChartInstance) {
        this.usiaChartInstance.destroy()
      }

      this.usiaChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.kelompokUmur,
          datasets: [
            {
              label: 'Sangat Kurang',
              data: this.statusData['Sangat Kurang'],
              backgroundColor: '#d9534f',
            },
            {
              label: 'Kurang',
              data: this.statusData['Kurang'],
              backgroundColor: '#f0ad4e',
            },
            {
              label: 'Normal',
              data: this.statusData['Normal'],
              backgroundColor: '#5cb85c',
            },
            {
              label: 'Risiko Lebih',
              data: this.statusData['Risiko Lebih'],
              backgroundColor: '#0275d8',
            },
            {
              label: 'Tidak Naik',
              data: this.statusData['Tidak Naik'],
              backgroundColor: '#9370db',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'right' },
          },
          scales: {
            x: {
              stacked: false,
              title: {
                display: true,
                text: 'Kategori Usia (bulan)',
              },
            },
            y: {
              stacked: false,
              beginAtZero: true,
              title: {
                display: true,
                text: 'Total Individu',
              },
            },
          },
        },
      })
    },
    renderUsiaChart_tb() {
      const ctx = this.$refs.usiaChart_tb.getContext('2d')

      if (this.usiaChartInstance_tb) {
        this.usiaChartInstance_tb.destroy()
      }

      this.usiaChartInstance_tb = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.kelompokUmur,
          datasets: [
            {
              label: 'Sangat Pendek',
              data: this.statusData_tb['Sangat Pendek'],
              backgroundColor: '#d9534f',
            },
            {
              label: 'Pendek',
              data: this.statusData_tb['Pendek'],
              backgroundColor: '#f0ad4e',
            },
            {
              label: 'Normal',
              data: this.statusData_tb['Normal'],
              backgroundColor: '#5cb85c',
            },
            {
              label: 'Tinggi',
              data: this.statusData_tb['Tinggi'],
              backgroundColor: '#0275d8',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'right' },
          },
          scales: {
            x: {
              stacked: false,
              title: {
                display: true,
                text: 'Kategori Usia (bulan)',
              },
            },
            y: {
              stacked: false,
              beginAtZero: true,
              title: {
                display: true,
                text: 'Total Individu',
              },
            },
          },
        },
      })
    },
    renderUsiaChart_bbtb() {
      const ctx = this.$refs.usiaChart_bbtb.getContext('2d')

      if (this.usiaChartInstance_bbtb) {
        this.usiaChartInstance_bbtb.destroy()
      }

      this.usiaChartInstance_bbtb = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: this.kelompokUmur,
          datasets: [
            {
              label: 'Gizi Buruk',
              data: this.statusData_bbtb['Gizi Buruk'],
              backgroundColor: '#d9534f',
            },
            {
              label: 'Gizi Kurang',
              data: this.statusData_bbtb['Gizi Kurang'],
              backgroundColor: '#f0ad4e',
            },
            {
              label: 'Gizi Baik',
              data: this.statusData_bbtb['Gizi Baik'],
              backgroundColor: '#5cb85c',
            },
            {
              label: 'Risiko Gizi Lebih',
              data: this.statusData_bbtb['Risiko Gizi Lebih'],
              backgroundColor: '#0275d8',
            },
            {
              label: 'Gizi Lebih',
              data: this.statusData_bbtb['Gizi Lebih'],
              backgroundColor: 'silver',
            },
            {
              label: 'Obesitas',
              data: this.statusData_bbtb['Obesitas'],
              backgroundColor: 'black',
            },
          ],
        },
        options: {
          responsive: true,
          plugins: {
            legend: { position: 'right' },
          },
          scales: {
            x: {
              stacked: false,
              title: {
                display: true,
                text: 'Kategori Usia (bulan)',
              },
            },
            y: {
              stacked: false,
              beginAtZero: true,
              title: {
                display: true,
                text: 'Total Individu',
              },
            },
          },
        },
      })
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
        labels.push(`${monthNames[d.getMonth()]} ${d.getFullYear()}`)
      }
      return labels
    },
    renderIndiChart() {
      const ctx = this.$refs.indiChart.getContext('2d')

      if (this.indiChartInstance) {
        this.indiChartInstance.destroy()
      }

      this.indiChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: [
            {
              label: 'Sangat Kurang',
              data: this.indikatorData['Sangat Kurang'],
              borderColor: '#d9534f',
              backgroundColor: 'rgba(217, 83, 79, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Kurang',
              data: this.indikatorData['Kurang'],
              borderColor: '#f0ad4e',
              backgroundColor: 'rgba(240, 173, 78, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Normal',
              data: this.indikatorData['Normal'],
              borderColor: '#5cb85c',
              backgroundColor: 'rgba(92, 184, 92, 0.3)',
              fill: true, // area fill untuk Normal
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Risiko Lebih',
              data: this.indikatorData['Risiko Lebih'],
              borderColor: '#0275d8',
              backgroundColor: 'rgba(2, 117, 216, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Tidak Naik',
              data: this.indikatorData['Tidak Naik'],
              borderColor: '#9370db',
              backgroundColor: 'rgba(147, 112, 219, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          interaction: { mode: 'nearest', intersect: false },
          plugins: {
            legend: { position: 'top' },
            tooltip: { enabled: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: 'Jumlah Individu' },
            },
            x: {
              title: { display: true, text: 'Bulan' },
            },
          },
        },
      })
    },
    renderIndiChart_tb() {
      const ctx = this.$refs.indiChart_tb.getContext('2d')

      if (this.indiChartInstance_tb) {
        this.indiChartInstance_tb.destroy()
      }

      this.indiChartInstance_tb = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: [
            {
              label: 'Sangat Pendek',
              data: this.indikatorData_tb['Sangat Pendek'],
              borderColor: '#d9534f',
              backgroundColor: 'rgba(217, 83, 79, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Pendek',
              data: this.indikatorData_tb['Pendek'],
              borderColor: '#f0ad4e',
              backgroundColor: 'rgba(240, 173, 78, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Normal',
              data: this.indikatorData_tb['Normal'],
              borderColor: '#5cb85c',
              backgroundColor: 'rgba(92, 184, 92, 0.3)',
              fill: true, // area fill untuk Normal
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Tinggi',
              data: this.indikatorData_tb['Tinggi'],
              borderColor: '#0275d8',
              backgroundColor: 'rgba(2, 117, 216, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          interaction: { mode: 'nearest', intersect: false },
          plugins: {
            legend: { position: 'top' },
            tooltip: { enabled: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: 'Jumlah Individu' },
            },
            x: {
              title: { display: true, text: 'Bulan' },
            },
          },
        },
      })
    },
    renderIndiChart_bbtb() {
      const ctx = this.$refs.indiChart_bbtb.getContext('2d')

      if (this.indiChartInstance_bbtb) {
        this.indiChartInstance_bbtb.destroy()
      }

      this.indiChartInstance_bbtb = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: [
            {
              label: 'Gizi Buruk',
              data: this.indikatorData_bbtb['Gizi Buruk'],
              borderColor: '#d9534f',
              backgroundColor: 'rgba(217, 83, 79, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Gizi Kurang',
              data: this.indikatorData_bbtb['Gizi Kurang'],
              borderColor: '#f0ad4e',
              backgroundColor: 'rgba(240, 173, 78, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Gizi Baik',
              data: this.indikatorData_bbtb['Gizi Baik'],
              borderColor: '#5cb85c',
              backgroundColor: 'rgba(92, 184, 92, 0.3)',
              fill: true, // area fill untuk Normal
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Risiko Gizi Lebih',
              data: this.indikatorData_bbtb['Risiko Gizi Lebih'],
              borderColor: '#0275d8',
              backgroundColor: 'rgba(2, 117, 216, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Gizi Lebih',
              data: this.indikatorData_bbtb['Gizi Lebih'],
              borderColor: 'silver',
              backgroundColor: 'rgba(2, 117, 216, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
            {
              label: 'Obesitas',
              data: this.indikatorData_bbtb['Obesitas'],
              borderColor: 'black',
              backgroundColor: 'rgba(2, 117, 216, 0.2)',
              fill: false,
              tension: 0.3,
              pointRadius: 4,
              pointHoverRadius: 6,
            },
          ],
        },
        options: {
          responsive: true,
          interaction: { mode: 'nearest', intersect: false },
          plugins: {
            legend: { position: 'top' },
            tooltip: { enabled: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: 'Jumlah Individu' },
            },
            x: {
              title: { display: true, text: 'Bulan' },
            },
          },
        },
      })
    },
    renderIndiChart_pregnancy() {
      const ctx = this.$refs.indiChart_pregnancy.getContext('2d')

      if (this.indiChartInstance_pregnancy) {
        this.indiChartInstance_pregnancy.destroy()
      }

      // Warna beda2 untuk tiap dataset
      const colors = {
        KEK: { border: '#d9534f', bg: 'rgba(217, 83, 79, 0.2)' },
        Anemia: { border: '#f0ad4e', bg: 'rgba(240, 173, 78, 0.2)' },
        Resiko: { border: '#5cb85c', bg: 'rgba(92, 184, 92, 0.3)' },
        Tinggi: { border: '#0275d8', bg: 'rgba(2, 117, 216, 0.2)' },
      }

      const datasets = this.pregnancy_data.map(item => {
        return {
          label: item.nama,
          data: item.values,
          borderColor: colors[item.nama]?.border || '#999',
          backgroundColor: colors[item.nama]?.bg || 'rgba(153,153,153,0.2)',
          fill: item.nama === 'Resiko', // khusus "Resiko" di-fill area
          tension: 0.3,
          pointRadius: 4,
          pointHoverRadius: 6,
        }
      })

      this.indiChartInstance_pregnancy = new Chart(ctx, {
        type: 'line',
        data: {
          labels: this.bulanLabels,
          datasets: datasets,
        },
        options: {
          responsive: true,
          interaction: { mode: 'nearest', intersect: false },
          plugins: {
            legend: { position: 'top' },
            tooltip: { enabled: true },
          },
          scales: {
            y: {
              beginAtZero: true,
              title: { display: true, text: 'Jumlah Individu' },
            },
            x: {
              title: { display: true, text: 'Bulan' },
            },
          },
        },
      })
    }
  },
  computed: {
    background() {
      const config = JSON.parse(localStorage.getItem('siteConfig'))
      return config && config.background ? config.background : null
    },
  },
  created() {
    const storedEmail = localStorage.getItem('userEmail')
    if (storedEmail) {
      // Ambil bagian sebelum @
      let namePart = storedEmail.split('@')[0]

      // Ganti titik/underscore jadi spasi
      namePart = namePart.replace(/[._]/g, ' ')

      // Ubah huruf pertama tiap kata jadi kapital
      this.username = namePart
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
    } else {
      this.username = 'User'
    }
  },
  mounted() {
    this.$nextTick(() => {
      this.bulanLabels = this.getLast12Months() // <- generate bulan realtime
      this.renderUsiaChart()
      this.renderUsiaChart_tb()
      this.renderUsiaChart_bbtb()
      this.renderIndiChart()
      this.renderIndiChart_tb()
      this.renderIndiChart_bbtb()
      this.renderIndiChart_pregnancy()
      this.renderChart('pieChart_bb', this.dataTable_bb, [
        '#66a38c',
        '#338267',
        '#006341',
        '#004b30',
        '#00331f',
      ])
      this.renderChart('pieChart_tb', this.dataTable_tb, [
        '#66a38c',
        '#338267',
        '#006341',
        '#004b30',
        '#00331f',
      ])
      this.renderChart('pieChart_status', this.dataTable_status, [
        '#66a38c',
        '#338267',
        '#006341',
        '#004b30',
        '#00331f',
      ])
    })
  },
}
</script>

<style scoped>
.circle {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: bold;
  margin: 0 auto 15px auto;
  color: #fff;
}
.male-circle {
  background-color: rgba(0, 128, 96, 0.8);
}
.female-circle {
  background-color: rgba(204, 170, 85, 0.6);
}
.title {
  font-weight: bold;
  text-align: center;
  margin-bottom: 15px;
}
@media (min-width: 1200px) {
  .stat-col {
    flex: 0 0 10%;
    max-width: 10%;
  }
}

.dashboard-wrapper {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: #f9f9fb;
  color: #333;
}

/* Card Statistik */
.stat-card {
  border-radius: 1rem;
  transition: all 0.3s ease;
  /* background: #fff; */
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.icon-wrapper {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(0, 123, 255, 0.08); /* soft primary */
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1a7f37;
}

/* Form & Select */
.form-select,
.btn {
  /* border-radius: 0.75rem; */
  transition: all 0.2s ease-in-out;
}
.form-select:focus,
.btn:focus {
  box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

/* Card konten */
.card {
  border-radius: 1rem !important;
  border: none;
  background: #fff;
  transition: box-shadow 0.2s ease;
}
.card:hover {
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

/* Table */
.table {
  font-size: 0.95rem;
}
.table th {
  color: #6c757d;
  font-weight: 600;
}
.table td {
  color: #333;
}
</style>
