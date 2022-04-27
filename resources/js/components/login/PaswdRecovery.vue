<template>
    <div class="wrapper">
        <div> Te enviaremos un SMS con un código de confirmación, ingresa el correo electrónico que usas para iniciar sesión. </div>
        <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
         
          <div>
            <div class="form-group form-group col-6 offset-3 mt-5" data-content="Email">
              <label for="email">Correo electrónico</label>
              <ValidationProvider
                name="email"
                rules="required|email"
                v-slot="{ errors }"
              >
              <div class="row">
                <div class="col-10"><input id="email" v-model="form.email" class=" form-control-user form-control" :class="errors[0] ? 'is-invalid' : ''" /></div>
                <div class="btn btn-info btn-sm text-light fa fa-envelope col-2" v-show="!errors.length && form.email.length" @click="sendMessage()"></div>
              </div>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>

          <div class="row col-8 offset-2" v-if="displayPasswdInputs">
            <div class="form-group form-group mx-auto" data-content="confirmation">
              <label for="confirmation">Código de verificación</label>
              <ValidationProvider
                name="código de verificación"
                rules="required|min:2|max:100"
                v-slot="{ errors }"
              >
                <input id="confirmation" v-model="form.confirmation" class="form-control-user form-control" :class="errors[0] ? 'is-invalid' : ''" />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>

            <div class="form-group form-group mx-auto" data-content="passwd">
              <label for="password">Nueva Contraseña</label>
              <ValidationProvider
                name="contraseña"
                rules="required|min:2|max:100"
                v-slot="{ errors }"
              >
                <input id="password" type="password" v-model="form.password" class="form-control-user form-control" :class="errors[0] ? 'is-invalid' : ''" />
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
            </div>
          </div>
        <div>
            <button class="btn btn-primary btn-block my-3" type="submit">
              Guardar
            </button>
          </div>
        </form>
      </ValidationObserver>

    </div>
</template>

<script>

import toastr from "toastr";
export default {
  data(){
    return {
      message: 'Recuperar Contraseña',
      form : {
        email : '',
        password : '',
        confirmation : '',
      },
      displayPasswdInputs: false
    }
  },
  methods:{
    dataSubmit(){
      const form = {
        email : this.form.email,
        code : this.form.confirmation,
        password : this.form.password,
      }
      this.$api.put(`web/restorePassword?email=${this.form.email}`, form).then((res) => {

        if(res.status == 200){
          toastr.success('Contraseña modificada exitosamente');
          this.$emit('close',true);
        }else if(res.status == 400){
          toastr.error('La validación de la información ha fallado, por favor, valida los campos.');
        }else if(res.status == 404){
          toastr.error('El usuario no ha sido encontrado.');
        }else{
          toastr.error('Se ha presentado un inconveniente al modificar el registro, por favor, valida la información.');
        }

      });


    },
    sendMessage(){
      
      this.$api.get(`web/restorePasswordCode?email=${this.form.email}`).then((res) => {

        if(res.status == 200){

          toastr.info(`Código enviado exitosamente.`); 
          this.displayPasswdInputs = true;

        }else if(res.status == 404){
          toastr.error(`El usuario especificado no ha sido encontrado`); 
        }else{
          toastr.error(`Se ha presentado un problema al enviar el código, por favor valida la información.`); 
        }

      });
      
    }
  },
  created(){ }
}
</script>