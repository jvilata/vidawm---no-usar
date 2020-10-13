/*
*  LIBRERIA DE FUNCIONES JS GLOBALES
*/
/*
* Abrir Ventana con parametros Post
*/
import { axiosInstance } from 'boot/axios.js'
function newPostWindow (url, nomP1, valP1, nomP2, valP2, nomP3, valP3) {
  var host = axiosInstance.defaults.baseURL // 'https://vidawm.com/privado/php/'
  var form = document.createElement('form')
  form.setAttribute('method', 'post')
  form.setAttribute('action', host + url)
  form.setAttribute('target', 'view')

  var hiddenField = document.createElement('input')
  hiddenField.setAttribute('type', 'hidden')
  hiddenField.setAttribute('name', nomP1)
  hiddenField.setAttribute('value', valP1)
  form.appendChild(hiddenField)

  hiddenField = document.createElement('input')
  hiddenField.setAttribute('type', 'hidden')
  hiddenField.setAttribute('name', nomP2)
  hiddenField.setAttribute('value', valP2)
  form.appendChild(hiddenField)

  hiddenField = document.createElement('input')
  hiddenField.setAttribute('type', 'hidden')
  hiddenField.setAttribute('name', nomP3)
  hiddenField.setAttribute('value', valP3)
  form.appendChild(hiddenField)

  document.body.appendChild(form)
  window.open('', 'view')
  form.submit()
}

export { newPostWindow }
