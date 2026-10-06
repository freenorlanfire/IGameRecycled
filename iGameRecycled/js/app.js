(function () {
    var searchInput = document.querySelector('[data-game-search]');
    var filterButtons = document.querySelectorAll('[data-filter]');
    var cards = document.querySelectorAll('[data-catalog-grid] .game-card');
    var emptyState = document.querySelector('[data-empty-state]');

    if (!cards.length) {
        return;
    }

    var activeCategory = 'all';
    var query = '';

    function setFromQueryString() {
        var params = new URLSearchParams(window.location.search || '');
        var category = params.get('category');
        if (category) {
            activeCategory = category.toLowerCase();
            filterButtons.forEach(function (button) {
                button.classList.toggle('active', button.getAttribute('data-filter') === activeCategory);
            });
        }
    }

    function applyFilters() {
        var visibleCount = 0;

        cards.forEach(function (card) {
            var name = card.getAttribute('data-name') || '';
            var tags = card.getAttribute('data-tags') || '';
            var category = card.getAttribute('data-category') || '';

            var matchCategory = activeCategory === 'all' || category === activeCategory;
            var matchQuery = !query || name.indexOf(query) !== -1 || tags.indexOf(query) !== -1;
            var visible = matchCategory && matchQuery;

            card.hidden = !visible;
            if (visible) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.hidden = visibleCount !== 0;
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            query = (searchInput.value || '').toLowerCase().trim();
            applyFilters();
        });
    }

    filterButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeCategory = (button.getAttribute('data-filter') || 'all').toLowerCase();
            filterButtons.forEach(function (node) {
                node.classList.toggle('active', node === button);
            });
            applyFilters();
        });
    });

    setFromQueryString();
    applyFilters();
})();
