let tempFoto = "https://via.placeholder.com/200x200?text=Foto+Admin";

function validateFile(file) {
    const validTypes = ["image/jpeg", "image/png"];
    const maxSize = 2 * 1024 * 1024;

    if (!validTypes.includes(file.type)) {
        alert("❌ Hanya file JPG atau PNG yang diperbolehkan.");
        return false;
    }
    if (file.size > maxSize) {
        alert("❌ Ukuran file maksimal 2 MB.");
        return false;
    }
    return true;
}

function previewEditImage(event) {
    const file = event.target.files[0];
    if (!file || !validateFile(file)) {
        event.target.value = "";
        return;
    }
    var reader = new FileReader();
    reader.onload = function () {
        document.getElementById('editPreview').src = reader.result;
        tempFoto = reader.result;
    }
    reader.readAsDataURL(file);
}

function showEditForm() {
    document.getElementById("profileResult").classList.add("d-none");
    document.getElementById("profileFormCard").classList.remove("d-none");
}

function cancelEdit() {
    document.getElementById("profileFormCard").classList.add("d-none");
    document.getElementById("profileResult").classList.remove("d-none");
}

function togglePasswordForm() {
    document.getElementById("passwordForm").classList.toggle("d-none");
}

document.getElementById("profileForm").addEventListener("submit", function (e) {
    e.preventDefault();

    document.getElementById("rNama").textContent = document.getElementById("nama").value;
    document.getElementById("rEmail").textContent = document.getElementById("email").value;
    document.getElementById("rTelepon").textContent = document.getElementById("telepon").value;
    document.getElementById("rAlamat").textContent = document.getElementById("alamat").value;
    document.getElementById("sidebarPhoto").src = tempFoto;
    document.getElementById("resultPhoto").src = tempFoto;
    document.getElementById("editPreview").src = tempFoto;

    document.getElementById("profileFormCard").classList.add("d-none");
    document.getElementById("profileResult").classList.remove("d-none");
})