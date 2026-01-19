<template>
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
      <CopyRight />
    </footer>
</template>

<script>
import CopyRight from './CopyRight.vue'
import axios from 'axios'
/* =========================
   API CONFIG
========================== */
// PORT backend kamu
const API_PORT = 8000

// Bangun base URL dari window.location
const { protocol, hostname } = window.location
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`

export default {
  components: { CopyRight },
  data() {
    return {
      domain: window.location.origin,
      footerLogo: null,
      footerSocials: [],
      socialIcons: {
        call: '/icons/phone.png',
        email: '/icons/email.png',
        web: '/icons/web.png',
        twitter: '/icons/twitter.png',
        instagram: '/icons/instagram.png',
        facebook: '/icons/facebook.png',
        linkedin: '/icons/linkedin.png',
      }
    }
  },
  methods:{
    async loadFooterLogo() {
      try {
        const res = await axios.get(`${baseURL}/api/public/footer`, {
          headers: { Accept: 'application/json' },
        })

        this.footerLogo = res.data?.data?.logo_url || null
      } catch (error) {
        console.error('Gagal load footer logo:', error)
      }
    },
    async loadFooterSocial() {
      try {
        const res = await axios.get(`${baseURL}/api/public/footer-social`, {
          headers: { Accept: 'application/json' },
        })

        this.footerSocials = (res.data?.data || []).filter(
          item => item.is_active === 1
        )
      } catch (error) {
        console.error('Gagal load footer social:', error)
      }
    }
  },
  mounted(){
    this.loadFooterLogo()
    this.loadFooterSocial()
  }
}
</script>

<style scoped>
/* =========================
   FOOTER RATIO 1150x310
========================= */
.footer {
  color: #ffffff!important;
}

/* ratio wrapper */
.footer-ratio {
  /*padding-bottom: 20px;*/
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
</style>
