<template>
  <div id="app">
    <h1>Place Your Bet</h1>
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
  </div>
</template>

<script>
import axios from "axios";

export default {
  data() {
    return {
      events: [],
      bet: {
        event_id: null,
        outcome: null,
        amount: 1,
      },
      balance: 100, // Example balance, replace with actual user balance
      error: null,
    };
  },
  computed: {
    selectedEvent() {
      return this.events.find((e) => e.id === this.bet.event_id) || null;
    },
    availableOutcomes() {
      return this.selectedEvent && Array.isArray(this.selectedEvent.outcomes)
        ? this.selectedEvent.outcomes
        : [];
    },
  },
  watch: {
    // When event changes, ensure outcome is valid
    selectedEvent: {
      immediate: true,
      handler(ev) {
        if (!ev) {
          this.bet.outcome = null;
          return;
        }
        if (!this.availableOutcomes.includes(this.bet.outcome)) {
          this.bet.outcome = this.availableOutcomes[0] || null;
        }
      },
    },
  },
  created() {
    this.fetchEvents();
  },
  methods: {
    async fetchEvents() {
      try {
        const response = await axios.get("/api/events");
        this.events = response.data;
        // Set default selection if not set
        if (!this.bet.event_id && this.events.length) {
          this.bet.event_id = this.events[0].id;
        }
      } catch (err) {
        console.error(err);
        this.error = "Failed to load events.";
      }
    },
    async submitBet() {
      if (this.bet.amount <= 0 || this.bet.amount > this.balance) {
        this.error = "Invalid amount. Ensure it is greater than 0 and within your balance.";
        return;
      }
      if (!this.bet.event_id || !this.bet.outcome) {
        this.error = "Please select an event and outcome.";
        return;
      }

      try {
        await axios.post("/api/bets", this.bet);
        alert("Bet placed successfully!");
        this.error = null;
      } catch (err) {
        if (err.response && err.response.data && err.response.data.error) {
          this.error = err.response.data.error;
        } else {
          this.error = "An unexpected error occurred.";
        }
      }
    },
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
</style>
