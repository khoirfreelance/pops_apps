<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="homepage">

    <!-- =========================
         NAV HEADER
    ========================== -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom py-3">
      <div class="container-fluid px-4 px-lg-5">

        <ul class="navbar-nav align-items-center gap-lg-3 flex-row">

          <!-- HOME -->
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 text-success" href="/">
              <img
                src="/icons/home.png"
                alt="Home"
                class="nav-home-icon"
              />
              <span class="d-none d-md-inline">Home</span>
            </a>
          </li>

          <li class="nav-item mx-1 d-lg-block text-muted">|</li>

          <li class="nav-item">
            <a class="nav-link text-success" href="#kalkulator">
              Kalkulator Mandiri
            </a>
          </li>

        </ul>

      </div>
    </nav>

    <!-- =========================
         HERO / SLIDER SECTION
    ========================== -->
    <section id="home" class="py-5 hero-section">
      <div class="container-fluid px-4 px-lg-5">
        <div class="row align-items-center g-4">

          <!-- TEXT -->
          <div class="col-12 col-lg-6">
            <div
              v-if="slider.main_title"
              class="hero-badge px-3 py-1 rounded-pill bg-success-subtle text-success mb-2"
            >
              {{ slider.main_title }}
            </div>

            <h1 class="hero-title display-6 mb-2">
              {{ slider.title }}
            </h1>

            <p class="hero-desc-primary mb-2">
              {{ slider.description }}
            </p>

            <p class="hero-desc-secondary mb-3">
              {{ slider.subdescription }}
            </p>

            <div class="d-flex flex-wrap gap-2 mt-4">
              <a
                href="#statusgizi"
                class="btn btn-ajukan d-flex align-items-center gap-2 rounded-pill"
              >
                <img src="/icons/data-white.png" alt="Ajukan" class="btn-icon">
                <span>| Lihat Data</span>
              </a>

              <button
                class="btn btn-dashboard d-flex align-items-center gap-2 rounded-pill"
                type="button"
                @click="goDashboard"
              >
                <img src="/icons/login-green.png" alt="Masuk" class="btn-icon">
                <span>| Masuk ke Dashboard</span>
              </button>
            </div>
          </div>


          <!-- SLIDER -->
          <div class="col-12 col-lg-6">
            <div class="rounded-4 overflow-hidden shadow-sm bg-white">

              <div id="heroSlider"
                   class="carousel slide"
                   data-bs-ride="carousel"
                   data-bs-interval="4000">

                <div class="carousel-inner hero-slider">
                  <div
                    class="carousel-item"
                    v-for="(img, index) in sliderImages"
                    :key="img.id"
                    :class="{ active: index === 0 }"
                  >
                    <img :src="img.image_url" class="d-block w-100" :alt="`Slide ${index + 1}`">
                  </div>
                </div>

                <!-- PREV -->
                <button class="carousel-control-prev custom-control"
                        type="button"
                        data-bs-target="#heroSlider"
                        data-bs-slide="prev">
                  <span class="custom-arrow">‹</span>
                </button>

                <!-- NEXT -->
                <button class="carousel-control-next custom-control"
                        type="button"
                        data-bs-target="#heroSlider"
                        data-bs-slide="next">
                  <span class="custom-arrow">›</span>
                </button>

              </div>

            </div>
          </div>


        </div>
      </div>
    </section>

    <!-- =========================
         HEATMAP SECTION
    ========================== -->
    <section id="statusgizi" class="py-5 heatmap-section">
      <div class="container-fluid px-4 px-lg-5">

        <!-- HEADER -->
        <div class="text-center text-white mb-4">

          <!-- BADGE -->
          <div class="heatmap-badge mb-3">
            <i class="fa-solid fa-location-dot me-2"></i>
            Peta Dampingan TF - Bulan {{ currentMonthYear }}
          </div>

          <h2 class="fw-bold mb-2 heatmap-title">
            Peta Wilayah Desa Dampingan
          </h2>

          <p class="mb-0 opacity-90 heatmap-subtitle text-white">
            Arahkan kursor ke pin lokasi untuk melihat ringkasan data wilayah
          </p>
        </div>

        <!-- MAP CONTAINER -->
        <div class="bg-heatmap rounded-4 shadow-sm mx-auto">
          <div class="heatmap-box">
            <IndonesiaSvgMap />
          </div>
        </div>

      </div>
    </section>


    <!-- =========================
         KALKULATOR SECTION
    ========================== -->
    <section id="kalkulator" class="py-5 kalkulator-section">
      <div class="container-fluid px-4 px-lg-5">

        <!-- HEADER -->
        <div class="text-center mb-5">
          <div class="kalkulator-badge mb-4">
            <i class="fa-solid fa-calculator"></i> Kalkulator Mandiri
          </div>
          <h2 class="kalkulator-title mb-2">
            Pantau Pertumbuhan Anak dengan Mudah dan Cepat
          </h2>
          <p class="kalkulator-subtitle">
            Hitung status gizi anak Anda menggunakan standar KMS WHO untuk deteksi dini stunting
          </p>
        </div>

        <!-- CARD -->
        <div class="row justify-content-center">
          <div class="col-12 col-md-8 col-lg-4">
            <KMSChecker />
          </div>
        </div>

      </div>
    </section>

    <!-- =========================
         FOOTER
    ========================== -->
    <footer class="footer">
      <div class="footer-ratio">

        <div class="container-fluid footer-inner">

          <div class="row align-items-center footer-content">
            <!-- LEFT : LOGO -->
            <div class="col-md-4">
              <div class="footer-program text-white mb-2">PROGRAM DARI</div>

              <img
                v-if="footerLogo"
                :src="footerLogo"
                alt="Footer Logo"
                class="footer-logo"
              />

              <!-- fallback kalau belum ada -->
              <img
                v-else
                src="/images/logo-tanoto.png"
                alt="Tanoto Foundation"
                class="footer-logo"
              />
            </div>


            <!-- MENU -->
            <div class="col-md-4 mb-4 mb-md-0 pt-5">
              <ul class="footer-menu list-unstyled">
                <li><a class="text-white" href="#home">Home</a></li>
                <li><a class="text-white" href="#kalkulator">Kalkulator Mandiri</a></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="container-fluid footer-inner-social w-100 p-0">
          <div class="row align-items-center footer-content w-100 m-0">
            <div class="col-md-12 w-100">
              <div class="footer-social d-flex gap-2 justify-content-md-end w-100">

                <a
                  v-for="item in footerSocials"
                  :key="item.id"
                  :href="item.url"
                  target="_blank"
                  rel="noopener"
                >
                  <img
                    :src="socialIcons[item.type]"
                    :alt="item.type"
                  />
                </a>

              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- COPYRIGHT BAR -->
      <div class="footer-copyright">
        Copyright @ Tanoto Foundation 2025
      </div>
    </footer>


  </div>

  <!-- Scroll To Top -->
<button
  v-show="showScrollTop"
  class="scroll-to-top"
  @click="scrollToTop"
>
  <i class="fa-solid fa-arrow-up"></i>
</button>

</template>

<style scoped>
/* =========================
   SCROLL TO TOP BUTTON
========================== */
.scroll-to-top {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: var(--bs-primary);
  color: #fff;
  border: none;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  cursor: pointer;
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s ease;
}

.scroll-to-top:hover {
  transform: translateY(-4px);
  background: #157347; /* hijau lebih gelap */
}

.scroll-to-top i {
  font-size: 1rem;
}

/* =========================
   GLOBAL FONT SETTING
========================= */
.homepage {
  font-family: 'Inter', serif;
}

.nav-home-icon {
  width: 26px;
  height: 26px;
  object-fit: contain;
  transform: translateY(-3px);
}
.hero-section {
  background-color: #edf4f1;
  border-bottom: 1px solid rgba(0,0,0,0.04);
}
.hero-badge {
  display: inline-flex;
  align-items: center;
  padding: 6px 14px;
  border-radius: 999px;
  font-size: 1rem;
  font-weight: 400;
  white-space: nowrap;
}
.hero-title {
  color: #006341;
  font-weight: 600; /* lebih ringan dari fw-bold */
}
.hero-desc-primary {
  font-size: 1.15rem;
  color: #4c564f;
  line-height: 1.6;
}

.hero-desc-secondary {
  font-size: 0.95rem;
  color: #4c564f;
  line-height: 1.5;
}
/* =========================
   BUTTON STYLES
========================= */

.btn-ajukan {
  background-color: #006341;
  border: 1px solid #006341;
  color: #ffffff;
  font-size: 0.80rem;
  padding: 0.45rem 1rem;
}

.btn-ajukan:hover {
  background-color: #ffffff;
  border-color: #006341;
  color: #006341;
}

/* Dashboard button */
.btn-dashboard {
  background-color: #ffffff;
  border: 1px solid #006341;
  color: #006341;
  font-size: 0.80rem;
  padding: 0.45rem 1rem;
}

.btn-dashboard:hover {
  background-color: #006341;
  color: #ffffff;
}

/* Icon inside button */
.btn-icon {
  width: 16px;
  height: 16px;
  object-fit: contain;
}

/* =========================
   HERO SLIDER (550x322)
========================= */
.hero-slider {
  aspect-ratio: 550 / 322;
  position: relative;
}

.hero-slider img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* =========================
   CUSTOM ARROW
========================= */
.custom-control {
  width: auto;
  opacity: 1;
}

.custom-arrow {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.50);
  color: #4c564f;
  font-size: 22px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 10px rgba(0,0,0,0.12);
  transition: all 0.2s ease;
}

/* Hover effect */
.custom-arrow:hover {
  background: #ffffff;
  transform: scale(1.05);
}

/* Posisi kiri & kanan */
.carousel-control-prev {
  left: 12px;
}

.carousel-control-next {
  right: 12px;
}

/* Hilangkan default icon bootstrap */
.carousel-control-prev-icon,
.carousel-control-next-icon {
  display: none;
}

/* =========================
   FOOTER RATIO 1150x310
========================= */
.footer {
  color: #ffffff!important;
}

/* ratio wrapper */
.footer-ratio {
  padding-bottom: 20px;
  padding-left: 60px;
  padding-right: 60px;
  position: relative;
  width: 100%;
  aspect-ratio: 1150 / 180;   /* FIX RATIO */
  background: url('/images/footer-bg.png') no-repeat center;
  background-size: cover;
  display: flex;
  flex-direction: column;
}

/* isi footer di tengah */
.footer-inner {
  flex: 1;
  display: flex;
  margin: 0;
  max-height:150px;
  align-items: center; /* center vertical */
}

.footer-inner-social {
  flex: 1;
  display: flex;
  margin: 0;
  max-height:80px;
  margin-bottom: 10px;
  align-items: center; /* center vertical */
}

/* konten */
.footer-content {
  width: 100%;
}

/* menu */
.footer-menu a::before {
  content: '|';
  margin-right: 10px;
  opacity: 0.7;
  color: #FFFFFF;
}

/* social icons */
.footer-social img {
  width: 36px;
  height: 36px;
  object-fit: contain;
}

/* copyright bar */
.footer-copyright {
  background: #b3a369;
  color: #ffffff;
  text-align: center;
  font-size: 0.85rem;
  padding: 0.6rem 0;
}
.footer-program {
  font-family: 'Inter', sans-serif;
  font-size: 0.70rem;
  letter-spacing: 0.12em;   /* khas label */
  opacity: 0.9;
}

/* Logo Tanoto */
.footer-logo {
  max-width: 175px;         /* pas untuk rasio 1150x310 */
  width: 100%;
  height: auto;
}
@media (max-width: 768px) {
  .footer-logo {
    max-width: 150px;
  }

  .footer-program {
    font-size: 0.7rem;
  }
}

.footer-social {
  margin-top: auto;
  margin-bottom: 0px;
  align-items: center;
}

.footer-menu li:not(:last-child) {
  margin-bottom: 0.75rem;
}

.footer-menu a {
  text-decoration: none;
  color: #ffffff;
  transition: all 0.2s ease;
}

.footer-menu a:hover {
  transform: translateX(4px);
}

.heatmap-box {
  width: 100%;
}

.bg-heatmap{
  background-color: #d2ebe1;
}

.kalkulator-section {
  background: #f1f7f4;
}

.kalkulator-badge {
  display: inline-block;
  background: #cfe8da;
  color: #1f5f46;
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 1rem;
  font-weight: 400;
}

.kalkulator-title {
  color: #1f5f46;
  font-size: 2.3rem;
  font-weight: 600;
}

.kalkulator-subtitle {
  color: #4c564f;
  font-size: 0.95rem;
}

/* CARD */
.kalkulator-card {
  background: #1f5f46;
  border-radius: 20px;
  padding: 28px;
  color: #ffffff;
  background-image: url('/images/kalkulator-bg.png');
  background-size: cover;
}

.kalkulator-card-title {
  text-align: center;
  font-size: 1.3rem;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 0.25rem;
}

.kalkulator-label {
  text-align: center;
  font-size: 1rem;
  font-weight: 400;
  color: rgba(255,255,255,0.9);
  margin-bottom: 0.75rem;
}

.kalkulator-card-desc {
  text-align: center;
  font-size: 0.85rem;
  color: rgba(255,255,255,0.85);
  margin-bottom: 1.5rem;
}


/* GENDER CARD */
/* ===== KOTAK LUAR (DEFAULT) ===== */
.gender-card {
  background: #ffffff;
  border-radius: 10px;
  padding: 12px;
  text-align: center;
  cursor: pointer;
  border: 2px solid #e5e7eb;
  transition: all 0.25s ease;
  position: relative;
}

/* ===== LABEL ===== */
.gender-card span {
  display: block;
  margin-top: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
}

/* ===== KOTAK DALAM (ICON AREA) ===== */
.gender-inner {
  border-radius: 10px;
  padding: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.gender-inner.male {
  background: #7fae95;
}

.gender-inner.female {
  background: #bfc4c6;
}

.gender-inner img {
  width: 42px;
  height: 42px;
}

/* ===== ACTIVE / TERPILIH ===== */
.gender-card.active {
  border-color: #2f6f63;
  background: #f0f8f5;
  box-shadow: 0 8px 20px rgba(0,0,0,0.12);
  transform: translateY(-2px);
}

/* label berubah */
.gender-card.active span {
  color: #1f5f46;
}

/* icon lebih kontras */
.gender-card.active .gender-inner {
  box-shadow: inset 0 0 0 2px rgba(255,255,255,0.6);
}

/* ===== CHECK ICON (opsional tapi cakep) ===== */
.gender-card.active::after {
  content: "✓";
  position: absolute;
  top: 8px;
  right: 8px;
  background: #2f6f63;
  color: #ffffff;
  width: 22px;
  height: 22px;
  font-size: 13px;
  font-weight: 700;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}



/* INPUT */
.kalkulator-input {
  border-radius: 10px;
  font-size: 0.9rem;
}

/* BUTTON */
.kalkulator-btn {
  background: #cfe8da;
  color: #1f5f46;
  font-weight: 600;
  border-radius: 10px;
  padding: 10px;
  border: none;
}

.kalkulator-btn:hover {
  background: #70b890;
}

/* RESULT */
.kalkulator-result {
  background: rgba(255,255,255,0.15);
  padding: 12px;
  border-radius: 12px;
  font-size: 0.85rem;
}

/* =========================
   HEATMAP SECTION
========================= */
.heatmap-section {
  background: #64a48e;
}

.heatmap-title {
  font-size: 2.4rem;
}

.heatmap-subtitle {
  font-size: 1rem;
}

/* badge pill */
.heatmap-badge {
  display: inline-flex;
  align-items: center;
  background: #2f6f63;
  color: #ffffff;
  padding: 6px 16px;
  border-radius: 999px;
  font-size: 1rem;
  font-weight: 500;
}

/* container luar peta */
.bg-heatmap {
  background: #d2ebe1;
  border-radius: 22px;
}

/* box peta */
.heatmap-box {
  background: #dff1e8;
  border-radius: 16px;
}

/* SVG map */
.heatmap-box svg {
  width: 100%;
  height: auto;
  display: block;
}


</style>


<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import IndonesiaSvgMap from '@/components/IndonesiaSvgMap.vue'
import KMSChecker from '@/components/KMSChecker.vue'

/* =========================
   ROUTER
========================== */
const router = useRouter()

function goDashboard() {
  router.push('/admin/login')
}

/* =========================
   DATE (BULAN SAAT INI)
========================== */
const currentMonthYear = new Date().toLocaleDateString('id-ID', {
  month: 'long',
  year: 'numeric',
})

/* =========================
   API CONFIG
========================== */
// PORT backend kamu
const API_PORT = 8001

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8001"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

/* =========================
   SLIDER TEXT STATE
========================== */
const slider = ref({
  main_title: '',
  title: '',
  description: '',
  subdescription: '',
})

/* =========================
   SLIDER IMAGE STATE
========================== */
const sliderImages = ref([])

/* =========================
   FOOTER STATE
========================== */
const footerLogo = ref(null)
const footerSocials = ref([])

/* =========================
   SOCIAL ICON MAP
========================== */
const socialIcons = {
  call: '/icons/phone.png',
  email: '/icons/email.png',
  web: '/icons/web.png',
  twitter: '/icons/twitter.png',
  instagram: '/icons/instagram.png',
  facebook: '/icons/facebook.png',
  linkedin: '/icons/linkedin.png',
}

/* =========================
   LOAD SLIDER TEXT (PUBLIC)
========================== */
async function loadSliderSetting() {
  try {
    const res = await axios.get(`${baseURL}/api/public/slider-setting`, {
      headers: { Accept: 'application/json' },
    })

    if (res.data?.data) {
      slider.value = {
        main_title: res.data.data.main_title ?? '',
        title: res.data.data.title ?? '',
        description: res.data.data.description ?? '',
        subdescription: res.data.data.subdescription ?? '',
      }
    }
  } catch (error) {
    console.error('Gagal load slider setting:', error)
  }
}

/* =========================
   LOAD SLIDER IMAGES (PUBLIC)
========================== */
async function loadSliderImages() {
  try {
    const res = await axios.get(`${baseURL}/api/public/slider-images`, {
      headers: { Accept: 'application/json' },
    })

    sliderImages.value = res.data?.data ?? []
  } catch (error) {
    console.error('Gagal load slider images:', error)
  }
}

/* =========================
   LOAD FOOTER LOGO (PUBLIC)
========================== */
async function loadFooterLogo() {
  try {
    const res = await axios.get(`${baseURL}/api/public/footer`, {
      headers: { Accept: 'application/json' },
    })

    footerLogo.value = res.data?.data?.logo_url || null
  } catch (error) {
    console.error('Gagal load footer logo:', error)
  }
}

/* =========================
   LOAD FOOTER SOCIAL (PUBLIC)
========================== */
async function loadFooterSocial() {
  try {
    const res = await axios.get(`${baseURL}/api/public/footer-social`, {
      headers: { Accept: 'application/json' },
    })

    footerSocials.value = (res.data?.data || []).filter(
      item => item.is_active === 1
    )
  } catch (error) {
    console.error('Gagal load footer social:', error)
  }
}

/* =========================
   SCROLL TO TOP
========================== */
const showScrollTop = ref(false)

function handleScroll() {
  showScrollTop.value = window.scrollY > 300
}

function scrollToTop() {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  })
}

/* =========================
   ON MOUNT
========================== */
onMounted(() => {
  loadSliderSetting()
  loadSliderImages()
  loadFooterLogo()
  loadFooterSocial()
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>
