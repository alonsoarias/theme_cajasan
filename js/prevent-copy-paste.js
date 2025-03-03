/*! Prevenir copiar y pegar | Prevent copy and paste - por @parias */

document.addEventListener('contextmenu', evento => evento.preventDefault());

// Prevenir Ctrl+S, Ctrl+W, Ctrl+C, y Ctrl+X
document.addEventListener('keydown', function (e) {
    if (!e.ctrlKey) return;

    switch (e.key) {
        case 's': // Bloquear Ctrl+S
        case 'w': // Bloquear Ctrl+W (puede no funcionar en Chrome/Firefox)
        case 'c': // Bloquear Ctrl+C
        case 'x': // Bloquear Ctrl+X
            e.preventDefault();
            e.stopPropagation();
            break;
    }
});
