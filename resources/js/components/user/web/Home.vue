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
        <b-tab title="Usuarios" active>
          <div v-if="tabComponent == 0">
            <UserComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import UserComponent from "./User";
import PermitComponent from "./Permits";

export default {
  components: {
    UserComponent,
    PermitComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
