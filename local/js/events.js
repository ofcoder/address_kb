BX.ready(function(){
    BX.addCustomEvent("onInitialized", function(params,initialized){
        if (initialized.includes('task-form')) {
            BX.adjust(
                document.querySelector("#bx-component-scope-bitrix_tasks_widget_timeestimate_11 > label > input"),
                {props: {checked: true}}
            );
            BX.adjust(
                document.querySelector("#bx-component-scope-bitrix_tasks_widget_timeestimate_11 > input"),
                {props: {value: 'Y'}}
            );
        }
    });

    BX.addCustomEvent("onAjaxSuccessFinish", function(data){
        if (data.url === '/bitrix/services/main/ajax.php?context=PROJECT_TAG&action=ui.entityselector.load') {
            document.querySelector('div[data-bx-confidentiality-type="secret"]').click();
        }
    });

    BX.addCustomEvent("onImUpdateSumCounters", function(data){
        let iframe = document.getElementsByTagName('iframe');
        if (!!iframe[1]?.contentWindow.document.querySelector('.ui-mail-left-directory-menu')) {
            let nodeList = iframe[1]?.contentWindow.document.querySelectorAll('#left-panel > div > ul > div > .mail-menu-directory-item-container');
            let itemsArray = [];
            let parent = nodeList[0].parentNode;
            nodeList.forEach(element =>  itemsArray.push(parent.removeChild(element)));

            itemsArray.sort(function(nodeA, nodeB) {
                let a = nodeA.title;
                let b = nodeB.title;
                if (a < b) {
                    return -1;
                }
                if (a > b) {
                    return 1;
                }
                // если имена равны
                return 0;
            }).forEach(function(node) {
                parent.appendChild(node)
            });
        }
    });

    function DoubleScroll(element) {
        let scrollbar = document.createElement('div');
        scrollbar.appendChild(document.createElement('div'));
        scrollbar.style.overflow = 'auto';
        scrollbar.style.overflowY = 'hidden';
        scrollbar.style.width = element.offsetWidth + 'px';
        scrollbar.style.height = '20px';
        scrollbar.firstChild.style.width = element.scrollWidth + 'px';
        scrollbar.firstChild.style.paddingTop = '1px';
        scrollbar.firstChild.appendChild(document.createTextNode('\xA0'));
        scrollbar.onscroll = function () {
            element.scrollLeft = scrollbar.scrollLeft;
        };
        element.onscroll = function () {
            scrollbar.scrollLeft = element.scrollLeft;
        };
        element.parentNode.insertBefore(scrollbar, element);
    }

    if (window.location.href.includes('/company/vis_structure.php')) {
        DoubleScroll(document.querySelector('.workarea-content-paddings'));
    }
});