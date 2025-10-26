import Vue from "vue";
import App from "./App.vue";
import axios from "axios";

// Set Axios defaults
axios.defaults.baseURL = "";  // Remove /api prefix since it's handled by proxy
axios.defaults.withCredentials = true;

Vue.config.productionTip = false;

new Vue({
    render: (h) => h(App),
}).$mount("#app");