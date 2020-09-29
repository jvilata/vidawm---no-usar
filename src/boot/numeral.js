import Vue from 'vue'
import numeral from 'numeral'
import 'numeral/locales'

/**
 * @param {VueConstructor} Vue
 * @param {string?} locale
 */
export default function VueNumerals ({ Vue }, { locale = 'es-es' } = {}) {
  numeral.locale(locale)
  numeral.defaultFormat('0,0.00')
  Vue.prototype.$numeral = numeral
  Vue.filter('numeralFormat', (value, format = '0,0.00') => numeral(value).format(format))
}

if (typeof window !== 'undefined' && window.Vue) {
  // eslint-disable-next-line no-undef
  Vue.use(VueNumerals)
}
const numeralInstance = numeral
export { numeralInstance }
