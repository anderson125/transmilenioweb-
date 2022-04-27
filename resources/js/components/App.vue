<template>
  <div>
    <div v-if="$route.name == 'login'">
      <LoginComponent />
    </div>
    <div v-else>
      <LayoutComponent />
    </div>
  </div>
</template>
<script>
import LoginComponent from "./login/Home";
import LayoutComponent from "./layout/Home";
        
export default {
  components: { LoginComponent, LayoutComponent},
};
</script>
