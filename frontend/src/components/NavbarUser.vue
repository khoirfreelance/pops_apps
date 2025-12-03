<template>
  <nav
    class="navbar navbar-expand-lg fixed-top transition-all"
    :class="isScrolled ? 'scrolled-navbar' : 'bg-primary navbar-dark'"
  >
    <div class="container-fluid">
      <!-- Toggler -->
      <button
        class="navbar-toggler custom-toggler"
        type="button"
        @click="toggleMenu"
      >

        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menu -->
      <div
        class="collapse navbar-collapse"
        :class="{ show: isCollapse }"
        id="navbarSupportedContent"
      >

        <ul class="navbar-nav gap-3">
          <li class="nav-item">
            <a href="#section0" class="nav-link" @click="isCollapse=false">Home</a>
          </li>
          <li class="nav-item">
            <a href="#section1" class="nav-link" @click="isCollapse=false">Tentang POPS</a>
          </li>
          <li class="nav-item">
            <a href="#section3" class="nav-link" @click="isCollapse=false">Kalkulator</a>
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
// eslint-disable-next-line no-unused-vars
import { Collapse } from 'bootstrap';

export default {
  data() {
    return {
      isScrolled: false,
      showBackToTop: false,
      isCollapse: false
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
    toggleMenu() {
      this.isCollapse = !this.isCollapse   // FIXED 👍
    }
  },
}
</script>

<style scoped>
/* nav {
  z-index: 1050;
  border-bottom: 5px solid var(--bs-secondary);
} */
/* Transition */
.transition-all {
  transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

/* Navbar scroll style */
.scrolled-navbar {
  background-color: var(--bs-primary) !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

/* Link color fix */
.scrolled-navbar .nav-link {
  color: #fff !important;
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
