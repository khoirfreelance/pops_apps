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
            <input
              type="password"
              class="form-control rounded-pill"
              id="password"
              v-model="password"
              required
            />
          </div>
          <button type="submit" class="btn w-100 text-white btn-brand-gradient rounded-pill">
            Login
          </button>
        </form>
        <div class="mt-3 text-center">
          <a href="/admin/forgot" class="text-decoration-none text-brand fw-semibold">
            Lupa Password?
          </a>
        </div>
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

export default {
  name: 'LoginView',
  components: { FooterUser },
  data() {
    return {
      email: '',
      password: '',
      loading: false,
    }
  },
  setup() {
    const router = useRouter()
    return { router }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/login', {
          email: this.email,
          password: this.password,
        })

        // ambil data dari API
        const { status, message, user, token } = response.data

        //console.log('Token di config.vue:', token) // debug di console

        if (status) {
          // simpan token + info user ke localStorage
          localStorage.setItem('token', token)
          localStorage.setItem('isLoggedIn', 'true')
          localStorage.setItem('userEmail', user.email)
          //localStorage.setItem('userID', user.id)
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
          console.log('berhasil masuk');

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
</style>
