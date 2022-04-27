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
      
        <b-tab title="Roles" active>
          <div v-if="tabComponent == 0">
            <RolesComponent />
          </div>
        </b-tab>
        <b-tab title="Permisos">
          <div v-if="tabComponent == 1">
            <PermissionsComponent />
          </div>
        </b-tab>
      </b-tabs>
    </b-card>
  </div>
</template>
<script>
import RolesComponent from "./Roles";
import PermissionsComponent from "./Permissions";

export default {
  components: {
    RolesComponent,
    PermissionsComponent,
  },
  data() {
    return {
      tabComponent: null,
    };
  },
};
</script>
