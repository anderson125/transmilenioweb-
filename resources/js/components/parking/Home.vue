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
        <b-tab title="Bici Estaciónes" active>
          <div v-if="tabComponent == 0">
            <ParkingComponent />
          </div>
        </b-tab>
        <b-tab title="Tipos">
          <div v-if="tabComponent == 1">
            <TypeComponent />
          </div>
        </b-tab>
        <b-tab title="Troncales">
          <div v-if="tabComponent == 2">
            <StationComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import TypeComponent from "./Type";
import StationComponent from "./Station";
import ParkingComponent from "./Parking";

export default {
  components: {
    StationComponent,
    TypeComponent,
    ParkingComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
