<template>
  <nav
    class="navbar navbar-expand-lg fixed-top transition-all"
    :class="isScrolled ? 'scrolled-navbar' : 'bg-primary navbar-dark'"
  >
    <div class="container-fluid">
      <!-- Brand -->
      <a class="navbar-brand d-flex align-items-center mx-4" href="#">
        <img src="@/assets/tf_box_outlined.png" alt="POPS Logo" height="50" class="me-2" />
      </a>

      <!-- Toggler -->
      <button
        class="navbar-toggler custom-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menu -->
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav gap-3"> <!-- kasih jarak antar menu -->
          <li class="nav-item">
            <a href="#section1" class="nav-link">Tentang POPS</a>
          </li>
          <li class="nav-item">
            <a href="#section3" class="nav-link">Kalkulator Mandiri</a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Back to Top -->
    <button
      v-show="showBackToTop"
      class="btn btn-primary back-to-top shadow"
      @click="scrollToTop"
    >
      ↑
    </button>
  </nav>
</template>

<script>
export default {
  data() {
    return {
      isScrolled: false,
      showBackToTop: false,
    }
  },
  mounted() {
    window.addEventListener("scroll", this.handleScroll)
  },
  beforeUnmount() {
    window.removeEventListener("scroll", this.handleScroll)
  },
  methods: {
    handleScroll() {
      const scrollTop = window.scrollY
      this.isScrolled = scrollTop > 50
      this.showBackToTop = scrollTop > 300
    },
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: "smooth" })
    },
  },
}
</script>

<style scoped>
/* Transition */
.transition-all {
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Navbar scroll style */
.scrolled-navbar {
  background-color: #fff !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

/* Link color fix */
.scrolled-navbar .nav-link {
  color: #000 !important;
}
.navbar-dark .nav-link {
  color: #fff !important;
}

/* Hover effect halus */
.nav-link {
  position: relative;
  transition: color 0.3s ease;
}
.nav-link::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: 2px;
  width: 0%;
  height: 2px;
  background-color: currentColor;
  transition: width 0.3s ease;
}
.nav-link:hover::after {
  width: 100%;
}

/* Custom toggler (biar putih) */
.custom-toggler {
  border: none;
}
.custom-toggler .navbar-toggler-icon {
  background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255,255,255,1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E");
}

/* Back to Top */
.back-to-top {
  position: fixed;
  bottom: 30px;
  right: 20px;
  border-radius: 50%;
  width: 45px;
  height: 45px;
  font-size: 18px;
  line-height: 1;
  z-index: 1050; /* selalu di depan */
}
</style>
