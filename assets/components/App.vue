<template>
  <div class="vue-table-wrapper">
    <div class="table-filters mb-3">
      <div class="input-group">
        <input type="text" 
               class="form-control" 
               v-model="searchQuery" 
               placeholder="Search by name or email...">
        <button class="btn btn-outline-secondary" 
                type="button" 
                @click="resetFilters">
          Clear
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th v-for="(label, key) in columns" 
                :key="key" 
                @click="sortBy(key)"
                :class="{ sortable: true, active: sortKey === key }">
              {{ label }}
              <span v-if="sortKey === key" class="sort-indicator">
                {{ sortOrders[key] === 1 ? '▲' : '▼' }}
              </span>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in filteredAndSortedData" :key="item.id">
            <td>{{ item.fullName }}</td>
            <td>{{ item.email }}</td>
            <td>
              <div class="d-flex flex-wrap gap-1">
                <span v-for="group in item.teachingGroups" 
                      :key="group.id" 
                      class="badge bg-primary">
                  {{ group.name }}
                </span>
              </div>
            </td>
            <td>
              <div class="d-flex flex-wrap gap-1">
                <span v-for="skill in item.skills" 
                      :key="skill.id" 
                      class="badge bg-info">
                  {{ skill.skill.name }} (Level {{ skill.level }})
                </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      searchQuery: '',
      sortKey: '',
      sortOrders: {
        fullName: 1,
        email: 1,
        teachingGroups: 1,
        skills: 1
      },
      columns: {
        fullName: 'Name',
        email: 'Email',
        teachingGroups: 'Groups',
        skills: 'Skills'
      },
      data: []
    }
  },
  computed: {
    filteredAndSortedData() {
      let filtered = this.data
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => {
          return item.fullName.toLowerCase().includes(query) ||
                 item.email.toLowerCase().includes(query)
        })
      }
      
      if (this.sortKey) {
        filtered = filtered.slice().sort((a, b) => {
          a = a[this.sortKey]
          b = b[this.sortKey]
          if (this.sortKey === 'teachingGroups' || this.sortKey === 'skills') {
            return (a.length === b.length ? 0 : a.length > b.length ? 1 : -1) * this.sortOrders[this.sortKey]
          }
          return (a === b ? 0 : a > b ? 1 : -1) * this.sortOrders[this.sortKey]
        })
      }
      
      return filtered
    }
  },
  methods: {
    sortBy(key) {
      this.sortKey = key
      this.sortOrders[key] = this.sortOrders[key] * -1
    },
    resetFilters() {
      this.searchQuery = ''
      this.sortKey = ''
      Object.keys(this.sortOrders).forEach(key => {
        this.sortOrders[key] = 1
      })
    }
  },
  mounted() {
    const dataElement = document.querySelector('[data-teachers]')
    if (dataElement) {
      try {
        this.data = JSON.parse(dataElement.dataset.teachers)
      } catch (e) {
        console.error('Error parsing teacher data:', e)
      }
    }
  }
}
</script>

<style lang="scss">
.vue-table-wrapper {
  padding: 1rem;
}

.table-filters {
  max-width: 400px;
}

th.sortable {
  cursor: pointer;
  position: relative;
  padding-right: 1.5rem;
  
  &:hover {
    background-color: rgba(0, 0, 0, 0.05);
  }
  
  &.active {
    color: #0d6efd;
  }
}

.sort-indicator {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
}

.badge {
  margin: 0.125rem;
}
</style>