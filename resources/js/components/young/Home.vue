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
        <b-tab title="Autorizaciones" active>
          <div v-if="tabComponent == 0">
            <YoungComponent />
          </div>
        </b-tab>
        <b-tab title="Acudientes">
          <div v-if="tabComponent == 1">
            <ParentComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import ParentComponent from "./Parent";
import YoungComponent from "./Young";

export default {
  components: {
    ParentComponent,
    YoungComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
