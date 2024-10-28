function showMenu(){
    let popup = document.querySelector('.exit-popup');
    popup.classList.toggle('dn');
}

function showMobileMenu() {
    let menu = document.querySelector('.head-center');
    let userInfo = document.querySelector('.head-right');
    menu.classList.toggle('df');
    userInfo.classList.toggle('df');
}

function showInstructions() {
    BX.ready(function (){
        let popup = BX.PopupWindowManager.create("instructions-popup", null, {
            zIndex: 100, // z-index
            closeIcon: {right: "20px", top: "20px", width: "16px", height: "16px"},
            closeByEsc: true, // закрытие окна по esc
            darkMode: false, // окно будет светлым или темным
            autoHide: true, // закрытие при клике вне окна
            draggable: false, // можно двигать или нет
            resizable: false, // можно ресайзить
            min_height: 600, // минимальная высота окна
            min_width: 320, // минимальная ширина окна
            offsetLeft: 0,
            offsetTop: 0,
            className: 'instructions-popup',
            content: BX('instructions'),
            overlay: {
                // объект со стилями фона
                backgroundColor: 'black',
                opacity: 55
            }
        });
        popup.show();
    });
}

function showHistory() {
    BX.ready(function (){
        let popup = BX.PopupWindowManager.create("history-popup", null, {
            zIndex: 100, // z-index
            closeIcon: {right: "20px", top: "20px", width: "16px", height: "16px"},
            closeByEsc: true, // закрытие окна по esc
            darkMode: false, // окно будет светлым или темным
            autoHide: true, // закрытие при клике вне окна
            draggable: false, // можно двигать или нет
            resizable: false, // можно ресайзить
            min_width: 320, // минимальная ширина окна
            min_height: 600,
            offsetLeft: 0,
            offsetTop: 0,
            className: 'history-popup',
            content: BX('history'),
            overlay: {
                // объект со стилями фона
                backgroundColor: 'black',
                opacity: 55
            }
        });
        popup.show();
    });
}