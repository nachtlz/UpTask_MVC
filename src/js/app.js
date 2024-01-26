const mobileMenuBtn = document.querySelector("#mobile-menu");
const sidebar = document.querySelector(".sidebar");

if(mobileMenuBtn) {
    mobileMenuBtn.addEventListener("click", function() {
        sidebar.classList.toggle("mostrar");
    })
}

//Eliminar clase de mostrar en un tamaño mayor al de tablet
window.addEventListener("resize", function() {
    const anchoPantalla = document.body.clientWidth;
    if(anchoPantalla >= 768) {
        sidebar.classList.remove("mostrar");
    }
})