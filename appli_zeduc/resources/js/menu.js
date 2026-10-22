document.querySelectorAll('.menu-cartes article').forEach((carte) => {
    const bouton = carte.querySelector('button')
    const nom = carte.querySelector('h2').textContent.trim()

    bouton.addEventListener('click', () => {
        window.location.href = `/pages/plat-du-jour.html?plat=${encodeURIComponent(nom)}`
    })
})