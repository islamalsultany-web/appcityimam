function updateClock() {
    var now = new Date();
    var time = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    var date = now.toLocaleDateString('ar-IQ', { weekday: 'long', day: 'numeric', month: 'long' });
    document.getElementById('time').textContent = time;
    document.getElementById('date').textContent = date;
}

function renderCalendar() {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth();
    var today = now.getDate();
    var first = new Date(year, month, 1).getDay();
    var daysInMonth = new Date(year, month + 1, 0).getDate();
    var grid = document.getElementById('calendarGrid');
    var monthLabel = document.getElementById('monthLabel');

    monthLabel.textContent = now.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
    grid.innerHTML = '';

    var i;
    for (i = 0; i < first; i++) {
        var empty = document.createElement('span');
        empty.className = 'empty';
        grid.appendChild(empty);
    }

    for (i = 1; i <= daysInMonth; i++) {
        var day = document.createElement('span');
        day.textContent = i;
        if (i === today) {
            day.classList.add('today');
        }
        grid.appendChild(day);
    }
}

async function updateWeather() {
    var tempNow = document.getElementById('tempNow');
    var tempRange = document.getElementById('tempRange');
    var weatherState = document.getElementById('weatherState');
    try {
        var url = 'https://api.open-meteo.com/v1/forecast?latitude=32.6149&longitude=44.0249&current=temperature_2m&daily=temperature_2m_max,temperature_2m_min&timezone=Asia%2FBaghdad';
        var response = await fetch(url);
        if (!response.ok) {
            throw new Error('Weather request failed');
        }
        var data = await response.json();
        var nowTemp = Math.round(data.current.temperature_2m);
        var max = Math.round(data.daily.temperature_2m_max[0]);
        var min = Math.round(data.daily.temperature_2m_min[0]);
        tempNow.textContent = nowTemp + 'C';
        tempRange.textContent = min + 'C - ' + max + 'C';
        weatherState.textContent = 'تم تحديث البيانات';
    } catch (error) {
        weatherState.textContent = 'تعذر تحديث الطقس';
    }
}

function setupSearch() {
    var input = document.getElementById('appSearch');
    var clearButton = document.getElementById('clearSearch');
    var noResults = document.getElementById('noResults');
    var cards = Array.from(document.querySelectorAll('.app-card'));
    var titleCache = cards.map(function (card) {
        var titleElement = card.querySelector('h3');
        var title = card.getAttribute('data-title') || '';
        titleElement.textContent = title;
        return { card: card, titleElement: titleElement, title: title };
    });

    function escapeRegex(text) {
        return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlightText(text, query) {
        if (!query) {
            return text;
        }
        var safeQuery = escapeRegex(query);
        var regex = new RegExp('(' + safeQuery + ')', 'g');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function applyFilter() {
        var query = input.value.trim();
        var visibleCount = 0;

        clearButton.classList.toggle('hidden', query.length === 0);

        titleCache.forEach(function (item) {
            var visible = item.title.indexOf(query) !== -1;
            item.card.classList.toggle('hidden', !visible);
            item.titleElement.innerHTML = highlightText(item.title, query);
            if (visible) {
                visibleCount += 1;
            }
        });

        noResults.classList.toggle('show', visibleCount === 0);
    }

    input.addEventListener('input', function () {
        applyFilter();
    });

    clearButton.addEventListener('click', function () {
        input.value = '';
        applyFilter();
        input.focus();
    });

    applyFilter();
}

function setupThemeToggle() {
    var toggle = document.getElementById('themeToggle');
    var storageKey = 'cityimam-theme';
    var savedTheme = localStorage.getItem(storageKey);

    if (savedTheme === 'dark') {
        document.body.classList.add('dark');
    }

    function refreshLabel() {
        var isDark = document.body.classList.contains('dark');
        toggle.textContent = isDark ? 'الوضع الفاتح' : 'الوضع الداكن';
    }

    toggle.addEventListener('click', function () {
        document.body.classList.toggle('dark');
        localStorage.setItem(storageKey, document.body.classList.contains('dark') ? 'dark' : 'light');
        refreshLabel();
        showToast(document.body.classList.contains('dark') ? 'تم تفعيل الوضع الداكن' : 'تم تفعيل الوضع الفاتح');
    });

    refreshLabel();
}

function showToast(message) {
    var wrap = document.getElementById('toastWrap');
    var toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    wrap.appendChild(toast);
    setTimeout(function () {
        toast.remove();
    }, 2200);
}

function setupCardToasts() {
    var cards = document.querySelectorAll('.app-card');
    cards.forEach(function (card) {
        card.addEventListener('click', function () {
            var title = card.getAttribute('data-title') || 'البرنامج';
            showToast('جاري فتح ' + title);
        });
    });
}

function setupCardOrdering() {
    var main = document.querySelector('.main');
    var storageKey = 'cityimam-card-order';
    var defaultOrder = ['hr', 'mony', 'asset'];
    var draggedCard = null;

    function getCards() {
        return Array.from(main.querySelectorAll('.app-card'));
    }

    function saveOrder() {
        var order = getCards().map(function (card) {
            return card.getAttribute('data-id');
        });
        localStorage.setItem(storageKey, JSON.stringify(order));
    }

    function restoreOrder() {
        var saved = localStorage.getItem(storageKey);
        if (!saved) {
            return;
        }

        try {
            var ids = JSON.parse(saved);
            ids.forEach(function (id) {
                var card = main.querySelector('.app-card[data-id="' + id + '"]');
                if (card) {
                    main.insertBefore(card, document.getElementById('noResults'));
                }
            });
        } catch (error) {
            localStorage.removeItem(storageKey);
        }
    }

    function applyOrder(ids) {
        ids.forEach(function (id) {
            var card = main.querySelector('.app-card[data-id="' + id + '"]');
            if (card) {
                main.insertBefore(card, document.getElementById('noResults'));
            }
        });
    }

    function insertBeforeTarget(target, event) {
        if (!draggedCard || !target || draggedCard === target) {
            return;
        }

        var targetRect = target.getBoundingClientRect();
        var targetMiddle = targetRect.left + (targetRect.width / 2);
        var before = event.clientX < targetMiddle;

        if (before) {
            main.insertBefore(draggedCard, target);
        } else {
            main.insertBefore(draggedCard, target.nextSibling);
        }
    }

    getCards().forEach(function (card) {
        card.addEventListener('dragstart', function (event) {
            draggedCard = card;
            card.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.getAttribute('data-id'));
        });

        card.addEventListener('dragend', function () {
            card.classList.remove('dragging');
            draggedCard = null;
            saveOrder();
            showToast('تم حفظ ترتيب البرامج');
        });

        card.addEventListener('dragover', function (event) {
            event.preventDefault();
            insertBeforeTarget(card, event);
        });

        card.addEventListener('drop', function (event) {
            event.preventDefault();
            insertBeforeTarget(card, event);
        });
    });

    restoreOrder();

    var resetButton = document.getElementById('resetOrder');
    resetButton.addEventListener('click', function () {
        localStorage.removeItem(storageKey);
        applyOrder(defaultOrder);
        showToast('تمت إعادة الترتيب الافتراضي');
    });
}

function setupSidebarToggle() {
    var layout = document.querySelector('.layout');
    var toggle = document.getElementById('sidebarToggle');
    var storageKey = 'cityimam-sidebar-hidden';
    var saved = localStorage.getItem(storageKey);

    function applyState(collapsed) {
        layout.classList.toggle('sidebar-collapsed', collapsed);
        toggle.textContent = collapsed ? 'إظهار العمود' : 'إخفاء العمود';
    }

    if (saved === null) {
        applyState(window.innerWidth <= 560);
    } else {
        applyState(saved === 'true');
    }

    toggle.addEventListener('click', function () {
        var collapsed = !layout.classList.contains('sidebar-collapsed');
        applyState(collapsed);
        localStorage.setItem(storageKey, String(collapsed));
        showToast(collapsed ? 'تم إخفاء العمود الجانبي' : 'تم إظهار العمود الجانبي');
    });
}

updateClock();
renderCalendar();
updateWeather();
setupSearch();
setupThemeToggle();
setupCardToasts();
setupCardOrdering();
setupSidebarToggle();
setInterval(updateClock, 1000);
setInterval(updateWeather, 300000);
