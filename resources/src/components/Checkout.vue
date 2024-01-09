<template>
  <form
    id="checkout-payment-form"
    method="POST"
    action=""
  >
    <div class="one-liner">
      <div class="card-frame"></div>
    </div>
    <p class="error-message"></p>
    <p class="success-payment-message"></p>
  </form>
</template>

<script>
import '../checkout.js';

export default {
  props: {
    CHECKOUT_PUBLIC_KEY: {
      type: String,
      required: true
    }
  },
  methods: {
    payment() {
      return Frames.submitCard();
    }
  },
  mounted() {
    var form = document.getElementById("checkout-payment-form");
    var errorStack = [];

    Frames.init(this.CHECKOUT_PUBLIC_KEY);

    Frames.addEventHandler(
      Frames.Events.FRAME_VALIDATION_CHANGED,
      onValidationChanged
    );
    function onValidationChanged(event) {
      var errorMessageElement = document.querySelector(".error-message");
      var hasError = !event.isValid && !event.isEmpty;

      if (hasError) {
        errorStack.push(event.element);
      } else {
        errorStack = errorStack.filter(function (element) {
          return element !== event.element;
        });
      }

      var errorMessage = errorStack.length
        ? getErrorMessage(errorStack[errorStack.length - 1])
        : "";
      errorMessageElement.textContent = errorMessage;
    }

    function getErrorMessage(element) {
      var errors = {
        "card-number": "Please enter a valid card number",
        "expiry-date": "Please enter a valid expiry date",
        cvv: "Please enter a valid cvv code",
      };

      return errors[element];
    }

    Frames.addEventHandler(
      Frames.Events.CARD_TOKENIZATION_FAILED,
      onCardTokenizationFailed
    );
    function onCardTokenizationFailed(error) {
      console.log("CARD_TOKENIZATION_FAILED: %o", error);
      Frames.enableSubmitForm();
    }

    form.addEventListener("submit", function (event) {
      event.preventDefault();
      Frames.submitCard();
    });
  }
}
</script>

<style scoped>
#checkout-payment-form {
  /* max-width: 31.5rem; */
  margin: 0 auto;
}

iframe {
  width: 100%;
}

.one-liner {
  display: flex;
  flex-direction: column;
}

#pay-button {
  border: none;
  border-radius: 3px;
  color: #fff;
  font-weight: 500;
  height: 40px;
  width: 100%;
  background-color: #13395e;
  box-shadow: 0 1px 3px 0 rgba(19, 57, 94, 0.4);
}

#pay-button:active {
  background-color: #0b2a49;
  box-shadow: 0 1px 3px 0 rgba(19, 57, 94, 0.4);
}

#pay-button:hover {
  background-color: #15406b;
  box-shadow: 0 2px 5px 0 rgba(19, 57, 94, 0.4);
}

#pay-button:disabled {
  background-color: #697887;
  box-shadow: none;
}

#pay-button:not(:disabled) {
  cursor: pointer;
}

.card-frame {
  border: solid 1px #13395e;
  border-radius: 3px;
  width: 100%;
  max-width: 100%;
  margin-bottom: 8px;
  height: 40px;
  box-shadow: 0 1px 3px 0 rgba(19, 57, 94, 0.2);
}

.card-frame.frame--rendered {
  opacity: 1; /* Prevents iFrame rendering issue */

  /* Reminder: consider removal of 'rendered' */
  /* event passing to Merchant page */
}

.card-frame.frame--rendered.frame--focus {
  border: solid 1px #13395e;
  box-shadow: 0 2px 5px 0 rgba(19, 57, 94, 0.15);
}

.card-frame.frame--rendered.frame--invalid {
  border: solid 1px #d96830;
  box-shadow: 0 2px 5px 0 rgba(217, 104, 48, 0.15);
}

.error-message {
  color: #c9501c;
  font-size: 0.9rem;
  margin: 8px 0 0 1px;
  font-weight: 300;
}

.success-payment-message {
  color: #13395e;
  line-height: 1.4;
}
.token {
  color: #b35e14;
  font-size: 0.9rem;
  font-family: monospace;
}

@media screen and (min-width: 31rem) {
  .one-liner {
    flex-direction: row;
  }

  .card-frame {
    /* width: 318px; */
    margin-bottom: 0;
  }

  #pay-button {
    width: 175px;
    margin-left: 8px;
  }
}
</style>
