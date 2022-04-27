<template>
  <!-- Approach -->
  <div>
    <b-card no-body class="shadow mb-4">
      <b-tabs
        v-model="tabComponent"
        card
        nav-class="font-weight-bold"
        active-nav-item-class="font-weight-bold text-danger"
        content-class="mt-3"
      >
        <b-tab title="Ingresos y salidas Mensual diario por CP" active>
          <div v-if="tabComponent == 0">
            <Rp1Component />
          </div>
        </b-tab>
        <b-tab title="Ingresos mensuales generales por usuario">
          <div v-if="tabComponent == 1">
            <Rp2Component />
          </div>
        </b-tab>
        <b-tab title="Ingresos mensuales detallados por usuario" >
          <div v-if="tabComponent == 2">
            <Rp3Component />
          </div>
        </b-tab>
        <b-tab title="Reporte ingresos y salidas diario por CP por hora">
          <div v-if="tabComponent == 3">
            <Rp4Component />
          </div>
        </b-tab>
        <b-tab title="Reporte de bicicletas abandono">
          <div v-if="tabComponent == 4">
            <Rp5Component />
          </div>
        </b-tab>
        <b-tab title="WEB MAP SERVICE">
          <div v-if="tabComponent == 5">
            <Rp6Component />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import Rp1Component from "./Rp1";
import Rp2Component from "./Rp2";
import Rp3Component from "./Rp3";
import Rp4Component from "./Rp4";
import Rp5Component from "./Rp5";
import Rp6Component from "./Rp6";
export default {
  components: {
    Rp1Component,
    Rp2Component,
    Rp3Component,
    Rp4Component,
    Rp5Component,
    Rp6Component,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
