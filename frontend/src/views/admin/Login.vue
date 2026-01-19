<template>
  <div class="d-flex flex-column min-vh-100 bg-brand-gradient">
    <!-- Form di tengah -->
    <div class="flex-grow-1 d-flex justify-content-center align-items-center m-5">
      <div
        class="card p-4 shadow-lg rounded-4"
        style="min-width: 320px; max-width: 400px; width: 100%"
      >
        <h4 class="mb-4 text-center fw-bold text-brand">Login</h4>
        <form @submit.prevent="handleLogin">
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input
              type="email"
              class="form-control rounded-pill"
              id="email"
              v-model="email"
              required
            />
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>

            <div class="password-wrapper">
              <input
                :type="showPassword ? 'text' : 'password'"
                id="password"
                v-model="password"
                class="form-control rounded-pill password-input"
                required
              />

              <button
                type="button"
                class="password-toggle"
                @click="togglePassword"
                tabindex="-1"
              >
                <i
                  class="fa-solid"
                  :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
                ></i>
              </button>
            </div>
          </div>


          <button type="submit" class="btn w-100 text-white btn-brand-gradient rounded-pill">
            <i class="bi bi-box-arrow-in-right"></i> Login
          </button>
        </form>

        <!-- <div class="mt-3 text-center">
          <a href="/admin/forgot" class="text-decoration-none text-brand fw-semibold">
            Lupa Password?
          </a>
        </div> -->
      </div>
    </div>

    <!-- Footer di bawah -->
    <FooterUser />
  </div>
</template>

<script>
import Swal from 'sweetalert2'
import FooterUser from '@/components/FooterUser.vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

// PORT backend kamu
const API_PORT = 8000;

// Bangun base URL dari window.location
const { protocol, hostname } = window.location;
// contoh hasil: "http://192.168.0.5:8000"
const baseURL = `${protocol}//${hostname}:${API_PORT}`;

export default {
  name: 'LoginView',
  components: { FooterUser },
  data() {
    return {
      email: '',
      password: '',
      showPassword: false,
      loading: false,
    }
  },
  setup() {
    const router = useRouter()
    return { router }
  },
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword
    },
    async handleLogin() {
      this.loading = true
      try {
        const response = await axios.post(`${baseURL}/api/login`, {
          email: this.email,
          password: this.password,
        })

        // ambil data dari API
        //console.log('user role: ',response.data.user.role);

        const { status, message, user, token } = response.data

        if (status) {
          // simpan token + info user ke localStorage
          localStorage.setItem('token', token)
          localStorage.setItem('isLoggedIn', 'true')
          localStorage.setItem('userEmail', user.email)
          localStorage.setItem('userName', user.name)
          localStorage.setItem('role', user.role)
          localStorage.setItem('userWilayah', user.id_wilayah)
          localStorage.setItem('userPosyandu', user.id_posyandu)
          localStorage.setItem('authToken', token)

          Swal.fire({
            title: 'Login Berhasil!',
            text: message,
            icon: 'success',
            timer: 1500,
            showConfirmButton: false,
            showClass: { popup: 'animate__animated animate__fadeInDown' },
            hideClass: { popup: 'animate__animated animate__fadeOutUp' },
          })
          //console.log('berhasil masuk');

          this.router.push('/admin')
        } else {
          Swal.fire({
            title: 'Login Gagal!',
            text: message,
            icon: 'error',
            confirmButtonText: 'Coba Lagi',
          })
        }
      } catch (error) {
        Swal.fire({
          title: 'Error',
          text: error.response?.data?.message || 'Terjadi kesalahan pada server',
          icon: 'error',
          confirmButtonText: 'OK',
        })
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<style scoped>
.text-brand {
  color: #006341;
}
.btn-brand-gradient {
  background: linear-gradient(90deg, #006341, #b3a369);
  border: none;
  transition: opacity 0.2s ease-in-out;
}
.btn-brand-gradient:hover {
  opacity: 0.9;
}
.password-wrapper {
  position: relative;
}

.password-input {
  padding-right: 3rem; /* ruang icon */
}

.password-toggle {
  position: absolute;
  top: 50%;
  right: 1rem;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  padding: 0;
  line-height: 1;
  color: #333;
  cursor: pointer;
}

.password-toggle i {
  font-size: 1.1rem;
}

.password-toggle:focus {
  outline: none;
}

</style>
