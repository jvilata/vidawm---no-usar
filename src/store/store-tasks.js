import Vue from 'vue'
import { uid } from 'quasar'
const state = {
  showModalD: {
    showDialog: false,
    isAdd: false,
    payload: {
      id: '',
      task: {}
    }
  },
  search: '',
  tasks: {
    ID1: {
      name: 'task1',
      dueDate: '2019/01/01',
      dueTime: '10:00:00',
      selected: false
    },
    ID2: {
      name: 'task2',
      dueDate: '2019/02/01',
      dueTime: '11:00:00',
      selected: false
    },
    ID3: {
      name: 'task3',
      dueDate: '2019/03/01',
      dueTime: '12:00:00',
      selected: false
    }
  }
}

const mutations = {
  updateTask (state, payload) {
    Object.assign(state.tasks[payload.id], payload.task)
  },
  deleteTask (state, id) {
    Vue.delete(state.tasks, id)
  },
  addTask (state, payload) {
    Vue.set(state.tasks, payload.id, payload.task)
  },

  changeModalD (state, showModalD) {
    Object.assign(state.showModalD, showModalD)
  },
  changeSearch (state, value) {
    state.search = value
  }
}

const actions = {
  updateTask ({ commit }, payload) {
    commit('updateTask', payload)
  },
  deleteTask ({ commit }, id) {
    commit('deleteTask', id)
  },
  addTask ({ commit }, task) {
    const taskuid = uid()
    commit('addTask', {
      id: taskuid,
      task: task
    })
  },
  changeModalD ({ commit }, showModalD) {
    commit('changeModalD', showModalD)
  },
  changeSearch ({ commit }, value) {
    commit('changeSearch', value)
  }
}

const getters = {
  tasks: (state) => {
    return state.tasks
  },
  tasksFiltered: (state) => {
    const tasksFiltered = {}
    if (state.search) {
      Object.keys(state.tasks).forEach(function (key) {
        const task = state.tasks[key]
        if (task.name.includes(state.search)) {
          tasksFiltered[key] = task
        }
      })
      return tasksFiltered
    } else {
      return state.tasks
    }
  },
  showModalD: (state) => {
    return state.showModalD
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters
}
