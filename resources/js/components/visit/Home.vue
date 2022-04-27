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
        <b-tab title="Visitas" active>
          <div v-if="tabComponent == 0">
            <VisitComponent />
          </div>
        </b-tab>
        <b-tab title="Estado de visita">
          <div v-if="tabComponent == 1">
            <StatusComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import VisitComponent from "./Visit";
import StatusComponent from "./Status";

export default {
  components: {
    StatusComponent,
    VisitComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
