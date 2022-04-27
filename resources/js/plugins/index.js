import toastr from "toastr";
export default {
  install(Vue, options) {
    Vue.prototype.$bold = (status, title, message, options) => {
      if (status == 200) {
        return toastr.success(message, title, options);
      } else if (status == 500) {
        return toastr.error(message, title, options);
      } else if (status == 404) {
        return toastr.warning(message, title, options);
      }
    };
  }
};