<template>
  <v-container class="fill-height background" fluid>
    <!-- Formulário de login -->
    <v-card class="mx-auto"
            :loading="loading"
            shaped
            style="width: 24rem; max-width: 100%"
    >
      <v-card-title>
        <v-img src="@/assets/logo-color.svg" max-height="8rem" contain></v-img>
      </v-card-title>
      <v-card-text>
        <v-form @submit.prevent="submit">
          <v-text-field
            label="Usuário"
            prepend-inner-icon="mdi-account"
            outlined
            :disabled="loading"
            placeholder="usuario"
            required
            autofocus
            v-model="user"
            :rules="userRules"
            maxlength="16"
          ></v-text-field>
          <v-text-field
            label="Senha"
            prepend-inner-icon="mdi-lock"
            outlined
            :disabled="loading"
            placeholder="senha"
            required
            v-model="password"
            :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
            :type="showPassword ? 'text' : 'password'"
            @click:append="showPassword = !showPassword"
            :rules="passwordRules"
            maxlength="16"
          ></v-text-field>
          <v-btn rounded large block :loading="loading" type="submit" color="primary">ENTRAR</v-btn>
          <v-btn text color="primary" class="mt-2 mx-auto" block small @click="resetPassword">Esqueci minha senha</v-btn>
        </v-form>
      </v-card-text>
    </v-card>
  </v-container>
</template>

<script>
  export default {
    name: "Login",
    data: () => ({
      loading: false,
      user: '',
      password: '',
      showPassword: false,
      userRules: [
        v => !!v || 'O nome de usuário'
      ],
      passwordRules: [
        v => !!v || 'Digite sua senha',
        v => (v && v.length >= 4) || 'Precisa ter pelo menos 4 caracteres',
        v => (v && v.length <= 16) || 'O limite é 16 caracteres',
        v => (!/\s/g.test(v)) || 'Espaços não são permitidos'
      ]
    }),
    methods: {
      submit() {
        this.loading = true;
        this.$store.dispatch('login', {login: this.user, senha: this.password})
          .then(() => this.$router.push('/'))
          .finally(() => this.loading = false)
      },
      resetPassword() {}
    }
  }
</script>

<style scoped>
  .background {
    background-image: url('https://picsum.photos/id/178/1920/1080');
    background-size: cover;
  }
  .background::before {
    content: "";
    display: block;
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background-color: rgba(250,250,255,.8);
  }
</style>