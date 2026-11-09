'use strict';

(function () {
    const explore = window.spotScoutExplore;
    const searchInput = document.querySelector('#search-spots');
    const categoryInputs = Array.from(document.querySelectorAll('.search-filter input[name="categories[]"]'));
    const form = document.querySelector('#spot-search-form');
    const filter = document.querySelector('.search-filter');
    const siteHeader = document.querySelector('.site-header');

    if (!explore || !searchInput) return;

    const cards = new Map(Array.from(document.querySelectorAll('.spots-grid [data-spot-id]'))
        .map(card => [String(card.dataset.spotId), card]));
    const noResultsMessage = document.querySelector('#spot-search-empty');

    function updateExploreResults() {
        const query = searchInput.value.trim().toLocaleLowerCase();
        const selectedCategories = categoryInputs.filter(input => input.checked).map(input => input.value);
        const visibleCoordinates = [];
        let visibleCount = 0;

        explore.spots.forEach(spot => {
            const categories = Array.isArray(spot.categories) ? spot.categories : [];
            const matchesQuery = query === ''
                || spot.title.toLocaleLowerCase().includes(query)
                || categories.some(category => category.toLocaleLowerCase().includes(query));
            const matchesCategories = selectedCategories.length === 0
                || selectedCategories.some(category => categories.includes(category));
            const visible = matchesQuery && matchesCategories;
            if (visible) visibleCount++;
            const card = cards.get(String(spot.id));
            if (card) card.hidden = !visible;

            const marker = explore.markers.get(String(spot.id));
            if (!marker) return;
            if (visible) {
                marker.addTo(explore.map);
                visibleCoordinates.push(marker.getLatLng());
            } else {
                explore.map.removeLayer(marker);
            }
        });

        if (visibleCoordinates.length > 0) {
            explore.map.fitBounds(visibleCoordinates, { padding: [24, 24], maxZoom: 15 });
        } else {
            explore.map.setView([52.1, 19.4], 6);
        }

        if (noResultsMessage) {
            noResultsMessage.hidden = visibleCount > 0 || (query === '' && selectedCategories.length === 0);
        }

        const url = new URL(window.location.href);
        if (searchInput.value.trim()) url.searchParams.set('q', searchInput.value.trim());
        else url.searchParams.delete('q');
        url.searchParams.delete('categories[]');
        selectedCategories.forEach(category => url.searchParams.append('categories[]', category));
        window.history.replaceState({}, '', url);
    }

    filter?.addEventListener('toggle', () => {
        siteHeader?.classList.toggle('has-open-search-filter', filter.open);
    });
    form?.addEventListener('submit', event => {
        event.preventDefault();
        updateExploreResults();
        if (filter) filter.open = false;
    });

    updateExploreResults();
})();
