// const addressElement = elements.create("address", {
//     mode: "shipping",
//     autocomplete: {
//       mode: "google_maps_api",
//       apiKey: "{YOUR_GOOGLE_MAPS_API_KEY}",
//     },
//     defaultValues: {
//         name: 'Jane Doe',
//         address: {
//           line1: '354 Oyster Point Blvd',
//           line2: '',
//           city: 'South San Francisco',
//           state: 'CA',
//           postal_code: '94080',
//           country: 'US',
//         },
//       },
//   });
  

// This is your test publishable API key.
const stripe = Stripe("pk_test_51Nk8hzJgE50PTTAVMS2a5gHVECeogAM6Lce8sZn1qWAGpuYdK3AGEIqrjXtte6DgSSfHGo2btlF73i755bXdAfaR00gOEdGgU8");

// The items the customer wants to buy
const items = [{ id: "xl-tshirt" }];

const options = {
    // Fully customizable with appearance API.
    appearance: { /* ... */ }
  };

let elements;

initialize();
checkStatus();

document
  .querySelector("#payment-form")
  .addEventListener("submit", handleSubmit);

let emailAddress = 'djimra@mossosouk.com';
// Fetches a payment intent and captures the client secret
async function initialize() {
    const { clientSecret } = await fetch("/payment/get_client_secret", { 
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ items }),
    }).then((r) => r.json());
    console.log("Client secret", clientSecret);
    elements = stripe.elements({ clientSecret });

    const linkAuthenticationElement = elements.create("linkAuthentication");
    linkAuthenticationElement.mount("#link-authentication-element");

    const addressElementOptions = {
        mode: 'shipping',
        fields: {
            phone: 'always'
        },
        validation: {
            phone: {
                required: 'always',
            }
        }
    };
    // const addressElement = elements.create('address', addressElementOptions);
    // addressElement.mount("#address-element");

    const paymentElementOptions = {
    layout: "tabs",
    };

    const paymentElement = elements.create("payment", paymentElementOptions);
    paymentElement.mount("#payment-element");
    // handleNextStep();
}

const handleNextStep = async () => {
    const addressElement = elements.getElement('address');

    const {complete, value} = await addressElement.getValue();

    console.log("Value of address element before complete", value)
    if (complete) {
        // Allow user to proceed to the next step
        // Optionally, use value to store the address details
        console.log("Value of address element", value)
    }
};

async function handleSubmit(e) {
  e.preventDefault();
  setLoading(true);

  const { error } = await stripe.confirmPayment({
    elements,
    confirmParams: {
      // Make sure to change this to your payment completion page
    //   return_url: "https://127.0.0.1:8000/payment/new",
      return_url: "https://127.0.0.1:8000/payment/payment_success",
      receipt_email: emailAddress, // capture billing address instead
    },
  });

  // This point will only be reached if there is an immediate error when
  // confirming the payment. Otherwise, your customer will be redirected to
  // your `return_url`. For some payment methods like iDEAL, your customer will
  // be redirected to an intermediate site first to authorize the payment, then
  // redirected to the `return_url`.
  if (error.type === "card_error" || error.type === "validation_error") {
    showMessage(error.message);
  } else {
    showMessage(error.type, error.message);
  }

  setLoading(false);
}

// Fetches the payment intent status after payment submission
async function checkStatus() {
  const clientSecret = new URLSearchParams(window.location.search).get(
    "payment_intent_client_secret"
  );

  if (!clientSecret) {
    return;
  }

  const data = await stripe.retrievePaymentIntent(clientSecret);
  const { paymentIntent } = data;
  console.log(data);

  switch (paymentIntent.status) {
    case "succeeded":
      showMessage("Payment succeeded!");
      break;
    case "processing":
      showMessage("Your payment is processing.");
      break;
    case "requires_payment_method":
      showMessage("Your payment was not successful, please try again.");
      break;
    default:
      showMessage("Something went wrong.");
      break;
  }
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = document.querySelector("#payment-message");

  messageContainer.classList.remove("hidden");
  messageContainer.textContent = messageText;

  setTimeout(function () {
    messageContainer.classList.add("hidden");
    messageContainer.textContent = "";
  }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
  if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector("#submit").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("#submit").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
}