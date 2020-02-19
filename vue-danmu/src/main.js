import Vue from 'vue'
import App from './App.vue'

Vue.config.productionTip = false
import {vueBaberrage} from 'vue-baberrage';
Vue.use(vueBaberrage);
new Vue({
  render: h => h(App),
}).$mount('#app')
