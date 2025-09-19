<template>
  <div class="d-flex flex-column min-vh-100 bg-brand-gradient">
    <!-- Form di tengah -->
    <div class="m-5 flex-grow-1 d-flex justify-content-center align-items-center">
      <div
        class="card p-4 shadow-lg rounded-4"
        style="min-width: 320px; max-width: 400px; width: 100%"
      >
        <h4 class="mb-4 text-center fw-bold text-brand">Login</h4>
        <form @submit.prevent="handleForgot">
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" v-model="email" required />
          </div>
          <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-primary">Reset</button>
            <a href="/admin/login" class="btn btn-danger">Cancel</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Footer di bawah -->
    <FooterUser />
  </div>
</template>

<script>
import FooterUser from '@/components/FooterUser.vue'

export default {
  name: 'ForgotView',
  components: {
    FooterUser,
  },
  data() {
    return {
      email: '',
    }
  },
  methods: {
    handleForgot() {
      console.log('who forgot:', this.email)
      alert('Forgot')
    },
  },
}
</script>

<style scoped>
/* Warna teks brand */
.text-brand {
  color: #006341;
}

/* Tombol gradient */
.btn-brand-gradient {
  background: linear-gradient(90deg, #006341, #b3a369);
  border: none;
  transition: opacity 0.2s ease-in-out;
}
.btn-brand-gradient:hover {
  opacity: 0.9;
}
</style>
