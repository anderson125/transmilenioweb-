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
        <b-tab title="Inicio" active>
          <div v-if="tabComponent == 0">
            <XxxComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import XxxComponent from "./Xxx";

export default {
  components: {
    XxxComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
