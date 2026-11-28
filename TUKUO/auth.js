document.querySelectorAll("[data-toggle-password]").forEach((button) => {
  button.addEventListener("click", () => {
    const input = document.getElementById(button.dataset.togglePassword);
    if (!input) return;
    input.type = input.type === "password" ? "text" : "password";
    button.textContent = input.type === "password" ? "◉" : "◎";
    button.setAttribute("aria-label", input.type === "password" ? "Tampilkan password" : "Sembunyikan password");
  });
});
