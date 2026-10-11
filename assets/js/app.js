// Show/hide password toggle
document.querySelectorAll("[data-toggle-password]").forEach(function (btn) {
  btn.addEventListener("click", function () {
    var input = document.querySelector(btn.getAttribute("data-toggle-password"));
    var show = input.type === "password";
    input.type = show ? "text" : "password";
    btn.querySelector("i").className = show ? "bi bi-eye-slash" : "bi bi-eye";
  });
});
