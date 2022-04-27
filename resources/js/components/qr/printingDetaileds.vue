<template>
  <div>
    <div class="row">
      <span
        class="container col-3"
        style=""
        v-for="(qr, index) in qrs"
        :key="index"
      >
        <div
          class="container border border-dark"
          style="background-color: #fff; width: fit-content"
        >
          <div class="" style="display: inline-block">
            <div class="text-center font-weight-bold" style="font-size: 1em">
              {{ qr.code }}
            </div>
            <img
              :src="
                'https://chart.googleapis.com/chart?cht=qr&chs=140x140&chl=' +
                JSON.stringify(qr) +
                '&chld=1|2'
              "
            />
            <img :src="'../storage/files/vectoring.png'" />

            <div
              style="
                display: inline-block;
                font-size: 0.9em;
                position: absolute;
                left: 182px;
                top: 35px;
                width: 127px;
                height: 113px;
              "
            >
              <div class="overlayedText">
                {{ qr.biker_document_type }} {{ qr.biker_document }}
              </div>
              <div class="overlayedText">{{ qr.brand }} {{ qr.color }}</div>
              <div class="overlayedText">Marco {{ qr.serial }}</div>
              <div class="overlayedText">{{ qr.bicy_type }}</div>
              <div class="overlayedText">{{ qr.bicy_tires }}</div>
            </div>
          </div>

          <!-- <div class="" style="display: inline-block">
            <div class="text-center font-weight-bold" style="font-size: 1em">
              {{ qr.code }}
            </div>
            <img :src="'../storage/files/vectoring.png'">
          </div> -->
          <!-- <div
            class="font-weight-bold"
            style="
              display: inline-block;
              font-size: 0.9em;
              position: absolute;
              left: 249px;
              top: 20px;
              background-color: rgba(255, 255, 255, 0.8);
              width: 134px;
              height: 110px;
            "
          > -->
          <!-- <span>
              <div class="overlayedText">
                {{ qr.biker_document_type }} {{ qr.biker_document }}
              </div>
              <div class="overlayedText">{{ qr.brand }} {{ qr.color }}</div>
              <div class="overlayedText">Marco {{ qr.serial }}</div>
              <div class="overlayedText">{{ qr.bicy_type }}</div>
              <div class="overlayedText">{{ qr.bicy_tires }}</div>
            </span> -->
          <!-- </div> -->
        </div>
      </span>
    </div>
    <!-- <div class="row mt-2">
      <div class="container col-1" style="max-width: none !important">
        <img :src="'../storage/files/vectoring.png'" />
      </div>
    </div> -->
  </div>
</template>
<style>
.overlayedText {
  white-space: nowrap;
  overflow-x: hidden;
}
</style>
<script>
import toastr from "toastr";
export default {
  data() {
    return {
      no: [],
      qrs: [],
      data: this.$route.query.data,
    };
  },
  methods: {
    getData() {
      const ids = typeof this.data == "array" ? this.data.join(",") : this.data;
      this.$api.get("web/data/Dstickers/" + ids).then((res) => {
        if (res.status == 200) {
          this.qrs = res.data.response.data;
          if (!this.qrs.length) {
            toastr.info(
              "No se ha cargado ningún sticker. Estás seguro de que los registros seleccionados están activos ?"
            );
          } else {
            this.$api.put("web/data/Dstickers/" + ids).then((res) => {
              if (res.status != 200) {
                toastr.error(
                  "No se ha conseguido validar como impresos los stickers seleccionados"
                );
              }
            });
          }
        } else {
          toastr.error(
            "No se ha conseguido cargar los stickers seleccionados."
          );
        }
      });
    },
  },
  created() {
    setTimeout(() => {
      document.querySelectorAll("#accordionSidebar,.navbar").forEach((el) => {
        el.classList.add("d-none");
      });
    }, 1);
    this.getData();
  },
};
</script>