const modal = document.getElementById("project-modal");
const form = document.getElementById("project-form");

const openButtons = document.querySelectorAll("[data-modal-open]");
const closeButtons = document.querySelectorAll("[data-modal-close]");

const toggleModal = (isOpen) => {
  if (!modal) return;
  modal.classList.toggle("open", isOpen);
  modal.setAttribute("aria-hidden", String(!isOpen));
};

openButtons.forEach((button) => {
  button.addEventListener("click", () => toggleModal(true));
});

closeButtons.forEach((button) => {
  button.addEventListener("click", () => toggleModal(false));
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    toggleModal(false);
  }
});

if (form) {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const status = form.querySelector(".form-status");
    const submitButton = form.querySelector("button[type='submit']");

    if (status) {
      status.textContent = "Sending...";
    }
    if (submitButton) {
      submitButton.disabled = true;
    }

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: new FormData(form),
      });
      const result = await response.json();

      if (result.success) {
        if (status) status.textContent = "Thanks! We will follow up shortly.";
        form.reset();
        setTimeout(() => toggleModal(false), 1200);
      } else {
        if (status) status.textContent = result.message || "Unable to send message.";
      }
    } catch (error) {
      if (status) status.textContent = "Connection error. Please try again.";
    } finally {
      if (submitButton) submitButton.disabled = false;
    }
  });
}
