<template>
  <div class="">
    <div class="container">
      <!-- Outer Row -->
      <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12 col-md-9">
          <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
              <!-- Nested Row within Card Body -->
              <div class="row">
                <div class="col-lg-6 d-none d-lg-block">
                  <img :src="'logo.png'" alt="" />
                </div>
                <div class="col-lg-6 my-auto">
                  <div class="p-5">
                    <div class="text-center">
                      <h1 class="h4 text-gray-900 mb-5">Bienvenido</h1>
                    </div>
                    <ValidationObserver
                      ref="observer"
                      v-slot="{ handleSubmit }"
                    >
                      <form ref="form" @submit.prevent="handleSubmit(login)">
                        <div class="form-group">
                          <ValidationProvider
                            name="correo"
                            rules="required|min:6|max:60"
                            v-slot="{ errors }"
                          >
                            <input
                              v-model="form.email"
                              type="email"
                              class="form-control-user form-control"
                              :class="errors[0] ? 'is-invalid' : ''"
                            />
                            <span class="form-text text-danger">{{
                              errors[0]
                            }}</span>
                          </ValidationProvider>
                        </div>
                        <div class="form-group">
                          <ValidationProvider
                            name="contraseña"
                            rules="required|min:8|max:20"
                            v-slot="{ errors }"
                          >
                            <input
                              v-model="form.password"
                              type="password"
                              class="form-control form-control-user"
                              :class="errors[0] ? 'is-invalid' : ''"
                            />
                            <span class="form-text text-danger">{{
                              errors[0]
                            }}</span>
                          </ValidationProvider>
                          <a href="#" @click="displayRecovPaswModal"> ¿ Olvidaste tu contraseña ?  </a>
                        </div>
                        <button
                          type="submit"
                          class="btn btn-primary btn-user btn-block"
                        >
                          Ingresar
                        </button>
                      </form>
                    </ValidationObserver>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <b-modal hide-footer id="passwdRecov-model" ref="modal" size="xl" title="Recuperar Contraseña" 
         ok="handleOk" >

        <PaswdRecoveryComponent @close="closeModal"/>

      </b-modal>

    </div>
  </div>
</template>
<script>
import toastr from "toastr";
import PaswdRecoveryComponent from "./PaswdRecovery.vue";
export default {
  components:{
    PaswdRecoveryComponent
  },
  data() {
    return {
      form: {
        email: "",
        password: "",
      },
    };
  },
  methods: {
    login() {
      this.$store.dispatch("login", this.form).then((res) => {
        if (res == 200) {
          toastr.clear();
          toastr.success("Sesion Iniciada");
          this.$router.push("/inventory");
        }
      });
    },
    displayRecovPaswModal(){
      this.$bvModal.show('passwdRecov-model')
    },
    closeModal(){
      this.$bvModal.hide('passwdRecov-model')
    }
  },
  mounted() {
    document.body.className = "bg-gradient-primary";
  },
};
</script>

