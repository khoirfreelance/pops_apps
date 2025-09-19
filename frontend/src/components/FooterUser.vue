<template>
  <footer class="footer-gradient text-white pt-5 pb-3">
    <!-- Top Multi-layer Wave -->
    <div class="footer-wave">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 1440 150"
        preserveAspectRatio="none"
      >
        <defs>
          <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#0e7a54; stop-opacity:1" />
            <stop offset="100%" style="stop-color:#2fa174; stop-opacity:1" />
          </linearGradient>
        </defs>

        <!-- Layer 1 (paling bawah, warna penuh) -->
        <path
          fill="url(#waveGradient)"
          d="M0,80
             C360,0 1080,160 1440,80
             L1440,0 L0,0 Z"
        ></path>

        <!-- Layer 2 (transparan) -->
        <path
          fill="#ffffff"
          fill-opacity="1"
          d="M0,50
             C480,20 960,180 1440,100
             L1440,0 L0,0 Z"
        ></path>

        <!-- Layer 3 (lebih transparan lagi) -->
        <path
          fill="#ffffff"
          fill-opacity="0.4"
          d="M0,150
             C360,40 1080,200 1440,120
             L1440,0 L0,0 Z"
        ></path>
      </svg>
    </div>

    <!-- Isi footer -->
    <div class="container">
      <div class="row align-items-center text-white">

        <!-- Logo -->
        <div class="col-md-4 text-center text-md-centert mb-4 mb-md-0">
          <p class="small mb-3 text-uppercase letter-space">Program dari</p>
          <img
            src="/src/assets/tf_reserved_primary.png"
            alt="Logo"
            class="img-fluid"
            style="max-width: 180px;"
          />
        </div>

        <!-- Menu & Kontak -->
        <div class="col-md-8">
           <!-- Row menu + contact button -->
          <div class="row align-items-start mb-4">

            <!-- Menu -->
            <div class="col-md-9">
              <div class="row">
                <div class="col-6 col-md-6">
                  <ul class="list-unstyled">
                    <li><a href="#section0" class="footer-link my-2">Home</a></li>
                    <li><a href="#section1" class="footer-link my-2">Tentang Pops</a></li>
                  </ul>
                </div>
                <div class="col-6 col-md-6">
                  <ul class="list-unstyled">
                    <li><a href="#section2" class="footer-link my-2">Status Gizi</a></li>
                    <li><a href="#section3" class="footer-link my-2">Kalkulator Mandiri</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Contact Us button -->
            <div class="col-md-3 text-md-end text-center mt-2 mt-md-0">
              <a href="#contact" class="btn btn-secondary text-light fw-bold px-4 py-2 mt-4">
                CONTACT US
              </a>
            </div>

          </div>

          <!-- Separator -->
          <hr class="border-light opacity-25 mb-3" />

          <!-- Social Media -->
          <div class="d-flex justify-content-md-end justify-content-center gap-3 fs-6">
            <a href="#" class="footer-icon"><i class="bi bi-phone"></i></a>
            <a href="#" class="footer-icon"><i class="bi bi-envelope"></i></a>
            <a href="#" class="footer-icon"><i class="bi bi-globe"></i></a>
            <a href="#" class="footer-icon"><i class="bi bi-twitter"></i></a>
            <a href="#" class="footer-icon"><i class="bi bi-instagram"></i></a>
            <a href="#" class="footer-icon"><i class="bi bi-facebook"></i></a>
            <a href="#" class="footer-icon"><i class="bi bi-linkedin"></i></a>
          </div>

        </div>

      </div>

      <hr class="w-100 border-light opacity-25 mt-4" />
      <CopyRight />
    </div>

  </footer>
</template>

<script>
import CopyRight from './CopyRight.vue';
export default {
  components: { CopyRight }
}
</script>

<style scoped>
.footer-gradient {
  background: linear-gradient(90deg, #0e7a54, #2fa174);
  position: relative;
  padding-top: 6rem !important;
}

/* Wave */
.footer-wave {
  position: absolute;
  top: -20px;
  left: 0;
  width: 100%;
  overflow: hidden;
  line-height: 0;
}
.footer-wave svg {
  display: block;
  width: 100%;
  height: 120px;
}

/* Link fresh look */
.footer-link {
    color: #fff;
    text-decoration: none;
    display: block;
    padding: 4px 0;
    border-left: 2px solid #28a745; /* garis hijau kecil seperti contoh */
    padding-left: 8px;
  }
  .footer-link:hover {
    opacity: 0.7;
  }
  .footer-icon {
    color: white;
    text-decoration: none;
  }
  .footer-icon:hover {
    opacity: 0.7;
  }

/* Animasi halus */
.animate-fade-in {
  animation: fadeIn 1.2s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.letter-space {
  letter-spacing: 2px;
}
</style>
