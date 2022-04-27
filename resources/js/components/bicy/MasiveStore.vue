<template>

<div class="wrapper">

  <div>
      <ValidationObserver ref="observer" v-slot="{ handleSubmit }">
        <form ref="form" @submit.prevent="handleSubmit(dataSubmit)">
          <div class="form-group col" data-content="Archivo CSV">
            <label for="csv">Archivo CSV</label>
            <ValidationProvider
                rules="mimes:text/csv,application/vnd,csv"
                name="archivo csv"
                v-slot="{ errors }"
            >
                <b-form-file
                  browse-text="Buscar"
                  id="csv"
                  v-model="form.csv"
                  type="file"
                  class="form-control-user form-control mb-4"
                  :class="errors[0] ? 'is-invalid' : ''"
                ></b-form-file>
                <span class="form-text text-danger">{{ errors[0] }}</span>
              </ValidationProvider>
          </div>
          <div>
            <button class="btn btn-primary btn-block my-3" type="submit">
              Guardar
            </button>
          </div>
      </form>
      </ValidationObserver>
  </div>

  <div v-if="storeErrors.length">
    <h3>Errores en el envío</h3>
    <ul>
    <li v-for="(error, key) of storeErrors" :key="key">{{error}}</li>
    </ul>
  </div>


</div>

</template>


<script>
  import toastr from "toastr";
  export default {
    
    data() {
      return {
        form : {
          csv : null
        },
        storeErrors :[]
      }
    },
    methods :{
      dataSubmit(){
        this.storeErrors = [];
        var data = new FormData(); data.append('csv',this.form.csv);
        this.$api
          .post("web/data/bicyMassive", data).then( res => {
          console.log(res.data);
          if(res.status == 200){
            if(res.data.response.errors.length){
              this.storeErrors = res.data.response.errors;
              console.log(res.data.response.errors);
              toastr.error('Error al guardar.')
              return;
            }
            if(res.data.response.errors?.storeErrors?.length){
              this.storeErrors = res.data.response.errors?.storeErrors;
              console.log(res.data.response.errors?.storeErrors);
              toastr.error('Error al guardar.')
              return;
            }
            toastr.success('Éxito !')
          }
        });
      }
    }
  }

</script>