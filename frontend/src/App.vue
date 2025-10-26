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
import CryptoJS from "crypto-js";

export default {
  data() {
    return {
      events: [],
      bet: { event_id: null, outcome: null, amount: 1 },
      balance: 100,
      error: null,
      token: localStorage.getItem("token") || null,
      userId: localStorage.getItem("userId") || null,
      apiKey: "5FiGKOcgjjMJ6mAGKJZEHzZFROZuBNUsOVAmN5BRuwYuf3pK8RW5IoxButYNXYnb",
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
    if (this.token) this.fetchEvents();
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
        localStorage.setItem("token", this.token);
        localStorage.setItem("userId", this.userId);
        this.error = null;
        this.fetchEvents();
      } catch (err) {
        console.error(err);
        this.error = "Login failed";
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

      var payload = JSON.stringify(this.bet);
      var timestamp = Math.floor(Date.now() / 1000);
      var idempotency = "idemp-" + Math.random().toString(36).substring(2, 12);
      var signature = CryptoJS.HmacSHA256(payload + timestamp, this.apiKey).toString(
          CryptoJS.enc.Hex
      );

      try {
        await axios.post("http://localhost:8080/api/bets", this.bet, {
          headers: {
            Authorization: "Bearer " + this.token,
            "Content-Type": "application/json",
            "X-User-Id": this.userId,
            "X-Timestamp": timestamp,
            "X-Signature": signature,
            "Idempotency-Key": idempotency,
          },
        });
        alert("Bet placed successfully!");
        this.error = null;
      } catch (err) {
        console.error(err);
        if (err.response && err.response.status === 401) {
          this.token = null;
          this.userId = null;
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
        localStorage.removeItem("token");
        localStorage.removeItem("userId");
        alert("You have been logged out.");
      }
    }
  },
};
</script>

<style>
.error {
  color: red;
}
form {
  max-width: 400px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
}
form div {
  margin-bottom: 1em;
}
button {
  padding: 0.5em;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
button:hover {
  background-color: #0056b3;
}
.logout-button {
  margin-top: 1em;
  padding: 0.5em;
  background-color: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.logout-button:hover {
  background-color: #c82333;
}
</style>
