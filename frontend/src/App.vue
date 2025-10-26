<template>
  <div id="app">
    <h1>Place Your Bet</h1>

    <!-- 🔐 Login form -->
    <div v-if="!token">
      <h2>Login</h2>
      <form @submit.prevent="login">
        <input v-model="email" placeholder="Email" required />
        <input v-model="password" type="password" placeholder="Password" required />
        <button type="submit">Login</button>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
      <hr />
    </div>

    <!-- 🎲 Betting form -->
    <div v-else>
      <form @submit.prevent="submitBet">
        <div>
          <label for="event">Event:</label>
          <select v-model="bet.event_id" id="event" required>
            <option v-for="event in events" :key="event.id" :value="event.id">
              {{ event.title }}
            </option>
          </select>
        </div>

        <div>
          <label for="outcome">Outcome:</label>
          <select v-model="bet.outcome" id="outcome" required>
            <option v-for="o in availableOutcomes" :key="o" :value="o">
              {{ o }}
            </option>
          </select>
        </div>

        <div>
          <label for="amount">Amount:</label>
          <input
              type="number"
              id="amount"
              v-model.number="bet.amount"
              min="1"
              :max="balance"
              required
          />
        </div>

        <button type="submit">Place Bet</button>
        <p v-if="error" class="error">{{ error }}</p>
      </form>
      <button @click="logout" class="logout-button">Logout</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      events: [],
      bet: { event_id: null, outcome: null, amount: 1 },
      balance: 100,
      error: null,
      token: localStorage.getItem("token") || null,
      userId: localStorage.getItem("userId") || null,
      email: "alice@example.com",
      password: "password", // Default values for easier testing
    };
  },

  computed: {
    selectedEvent() {
      return this.events.find(function(e) {
        return e.id === this.bet.event_id;
      }.bind(this)) || null;
    },
    availableOutcomes() {
      var se = this.selectedEvent;
      return se && se.outcomes ? se.outcomes : [];
    },
  },

  created() {
    if (this.token) {
      this.fetchUserData();
      this.fetchEvents();
    }
  },

  methods: {
    async login() {
      try {
        var res = await axios.post("http://localhost:8080/api/login", {
          email: this.email,
          password: this.password,
        });
        this.token = res.data.token;
        this.userId = (res.data.user && res.data.user.id) || 7;
        this.balance = (res.data.user && res.data.user.balance);
        localStorage.setItem("token", this.token);
        localStorage.setItem("userId", this.userId);
        this.error = null;
        this.fetchEvents();
      } catch (err) {
        console.error(err);
        if (err.response && err.response.data.message === "Too many login attempts. Please try again in 1 minute.") {
          this.error = "Too many login attempts. Please try again in 1 minute.";
        } else {
          this.error = "Login failed";
        }
      }
    },

    async fetchUserData() {
      try {
        const response = await axios.get("http://localhost:8080/api/user", {
          headers: { Authorization: "Bearer " + this.token },
        });
        this.balance = response.data.balance;
      } catch (err) {
        console.error("Failed to fetch user data", err);
        if (err.response && err.response.status === 401) {
          this.token = null;
          this.userId = null;
          this.balance = 0;
          localStorage.removeItem("token");
          localStorage.removeItem("userId");
          alert("Session expired. Please log in again.");
          return;
        }
        this.error =
          (err.response && err.response.data && err.response.data.error) ||
          "An unexpected error occurred.";
      }
    },

    async fetchEvents() {
      try {
        var res = await axios.get("http://localhost:8080/api/events", {
          headers: { Authorization: "Bearer " + this.token },
        });
        this.events = res.data;
        if (!this.bet.event_id && this.events.length) {
          this.bet.event_id = this.events[0].id;
        }
      } catch (err) {
        console.error(err);
        this.error = "Failed to load events.";
      }
    },

    async submitBet() {
      if (!this.bet.event_id || !this.bet.outcome) {
        this.error = "Please select an event and outcome.";
        return;
      }

      try {
        const idempotencyKey = `idempotency-${Date.now().toString(16)}-${Math.random().toString(16).substring(2, 8)}`;
        await axios.post("http://localhost:8080/api/bets", this.bet, {
          headers: {
            Authorization: "Bearer " + this.token,
            "Content-Type": "application/json",
            "Idempotency-Key": idempotencyKey,
          },
        });
        alert("Bet placed successfully!");
        this.fetchUserData(); // Refresh balance after betting
        this.error = null;
      } catch (err) {
        console.error(err);
        if (err.response) {
          const serverMessage = err.response.data.message;
          if (serverMessage === "Insufficient funds") {
            this.error = "You do not have enough funds to place this bet.";
          } else if (serverMessage === "Too many requests") {
            this.error = "You are making requests too frequently. Please try again later.";
          } else {
            this.error = serverMessage || "An unexpected error occurred.";
          }
        } else {
          this.error = "Failed to connect to the server. Please try again later.";
        }
      }
    },

    async logout() {
      try {
        await axios.post("http://localhost:8080/api/logout", {}, {
          headers: { Authorization: "Bearer " + this.token },
        });
      } catch (err) {
        console.error("Logout API call failed", err);
      } finally {
        this.token = null;
        this.userId = null;
        this.balance = 0;
        localStorage.removeItem("token");
        localStorage.removeItem("userId");
        alert("You have been logged out.");
      }
    }
  },
};
</script>

<style scoped>
/* 🎨 Общие стили */
body {
  font-family: "Inter", "Segoe UI", sans-serif;
  background-color: #f8fafc;
  color: #1e293b;
  margin: 0;
  padding: 0;
}

#app {
  max-width: 600px;
  margin: 2rem auto;
  padding: 2rem;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

h1 {
  text-align: center;
  color: #0f172a;
  margin-bottom: 1.5rem;
  font-size: 1.8rem;
}

h2 {
  color: #1e293b;
  text-align: center;
  margin-bottom: 1rem;
}

/* 📋 Формы */
form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

form div {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

/* 🧾 Поля ввода */
input,
select {
  padding: 0.6em 0.75em;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

input:focus,
select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
  outline: none;
}

/* 🔘 Кнопки */
button {
  padding: 0.7em 1em;
  font-size: 1rem;
  font-weight: 500;
  background-color: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background-color 0.2s, transform 0.1s;
}

button:hover {
  background-color: #1d4ed8;
}

button:active {
  transform: scale(0.98);
}

/* 🚪 Кнопка Logout */
.logout-button {
  background-color: #ef4444;
  margin-top: 1.5rem;
}

.logout-button:hover {
  background-color: #dc2626;
}

/* ⚠️ Ошибки */
.error {
  color: #dc2626;
  font-size: 0.9rem;
  text-align: center;
}

/* 🧩 Разделитель */
hr {
  margin: 2rem 0;
  border: 0;
  border-top: 1px solid #e2e8f0;
}

/* 📱 Адаптивность */
@media (max-width: 480px) {
  #app {
    margin: 1rem;
    padding: 1.5rem;
  }

  h1 {
    font-size: 1.5rem;
  }

  button {
    font-size: 0.95rem;
  }
}
</style>
