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
        <b-tab title="Bicicletas" active>
          <div v-if="tabComponent == 0">
            <BicyComponent />
          </div>
        </b-tab>
        <b-tab title="Tipo de Bicicletas">
          <div v-if="tabComponent == 1">
            <TypeComponent />
          </div>
        </b-tab>
        <b-tab title="Estado de Bicicletas">
          <div v-if="tabComponent == 2">
            <StatusComponent />
          </div>
        </b-tab>
        <b-tab title="Almacén masivo">
          <div v-if="tabComponent == 3">
            <MasiveStoreComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import TypeComponent from "./Type";
import StatusComponent from "./Status";
import BicyComponent from "./Bicy";
import MasiveStoreComponent from "./MasiveStore";

export default {
  components: {
    MasiveStoreComponent,
    TypeComponent,
    StatusComponent,
    BicyComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
