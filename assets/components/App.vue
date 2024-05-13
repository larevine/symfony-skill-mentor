<template>
  <table class="table table-hover">
    <thead>
    <tr><th>Идентификатор</th><th>Email</th><th>Полное имя</th></tr>
    </thead>
    <tbody>
    <tr v-for="user in users">
      <td v-for="key in columns">
        {{ user[key] }}
      </td>
    </tr>
    </tbody>
  </table>
</template>

<script>
export default {
  data() {
    return {
      users: [],
      columns: ['id', 'email', 'fullname']
    };
  },
  mounted() {
    let data = document.querySelector("div[data-users]");
    let userList = JSON.parse(data.dataset.users);
    console.log(userList);

    this.users.push.apply(this.users, userList);
  }
};
</script>