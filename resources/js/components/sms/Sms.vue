<template>
  <div>
    <form ref="form" @submit.stop.prevent="dataSubmit">
      <b-form-group label="Telefono" label-for="phone-input">
        <b-form-input
          ref="phoneInput"
          id="phone-input"
          v-model="phone"
          required
        ></b-form-input>
      </b-form-group>
      <b-form-group label="Texto" label-for="name-input">
        <b-form-input
          ref="nameInput"
          id="name-input"
          v-model="name"
          required
        ></b-form-input>
      </b-form-group>
    </form>
  </div>
</template>

<script>
import toastr from "toastr";
import Swal from "sweetalert2";
export default {
  data() {
    return {
      phone: null,
      name: null,
    };
  },
  methods: {
    dataSubmit() {
      axios
        .post("/api/data/sms", {
          name: this.$refs.nameInput.value,
          phone: this.$refs.phoneInput.value,
        })
        .then((res) => {
          if (res.data.status == 1) {
            this.getData();
            toastr.success(res.data.response);
          } else {
            this.getData();
            toastr.error(res.data.response);
          }
        })
        .catch((e) => {
          toastr.error("Error al enviar, comunicate con soporte tecnico");
          console.error(e);
        });
    },
  },
};
</script>
