"use strict";

function setState(input, messageElement, valid, message) {
  input.classList.toggle("field-error", !valid);
  input.classList.toggle("field-valid", valid);
  if (messageElement) {
    messageElement.textContent = valid ? "" : message;
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const registrationForm = document.querySelector("[data-registration-form]");
  if (registrationForm) {
    registrationForm.addEventListener("submit", (event) => {
      let valid = true;
      const required = ["ime", "prezime", "username"];

      required.forEach((id) => {
        const input = document.getElementById(id);
        const message = document.getElementById(`poruka-${id}`);
        const ok = input.value.trim().length >= 2;
        setState(input, message, ok, "Polje mora sadržavati najmanje 2 znaka.");
        valid = valid && ok;
      });

      const pass = document.getElementById("pass");
      const passRep = document.getElementById("passRep");
      const passOk = pass.value.length >= 8;
      const matchOk = pass.value === passRep.value && passRep.value.length >= 8;
      setState(pass, document.getElementById("poruka-pass"), passOk, "Lozinka mora imati najmanje 8 znakova.");
      setState(passRep, document.getElementById("poruka-passRep"), matchOk, "Lozinke se ne podudaraju.");
      valid = valid && passOk && matchOk;

      if (!valid) event.preventDefault();
    });
  }

  const newsForm = document.querySelector("[data-news-form]");
  if (newsForm) {
    newsForm.addEventListener("submit", (event) => {
      const title = newsForm.querySelector("[name='title']");
      const about = newsForm.querySelector("[name='about']");
      const content = newsForm.querySelector("[name='content']");
      let valid = true;

      [[title, 5], [about, 10], [content, 20]].forEach(([field, min]) => {
        const ok = field.value.trim().length >= min;
        field.classList.toggle("field-error", !ok);
        field.classList.toggle("field-valid", ok);
        valid = valid && ok;
      });

      if (!valid) {
        event.preventDefault();
        const status = newsForm.querySelector("[data-form-status]");
        if (status) status.textContent = "Provjerite označena polja.";
      }
    });
  }
});
