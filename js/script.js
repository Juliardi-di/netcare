window.addEventListener("load", () => {
    const loading = document.getElementById("loading");
    const content = document.getElementById("content");

    if (loading && content) {
        setTimeout(() => {
            loading.classList.add("hidden");
            content.style.display = "block";
        }, 1000);
    }
});

const menuToggle = document.querySelector(".menu-toggle");
const sidebar = document.querySelector(".sidebar");

if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("open");
    });
const menuToggle = document.querySelector(".menu-toggle");
const sidebar = document.querySelector(".sidebar");

if (menuToggle && sidebar) {
    menuToggle.addEventListener("click", () => {
        sidebar.classList.toggle("open");
    });
}
function previewFoto(event) {
    const output = document.getElementById('fotoProfil');
    output.src = URL.createObjectURL(event.target.files[0]);
}

}

