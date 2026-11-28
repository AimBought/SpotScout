'use strict';

(function () {
    const explore = window.spotScoutExplore;
    const searchInput = document.querySelector('#search-spots');
    const categoryInputs = Array.from(document.querySelectorAll('.search-filter input[name="categories[]"]'));
    const form = document.querySelector('#spot-search-form');
    const filter = document.querySelector('.search-filter');
    const filterPanel = document.querySelector('#filterPanel');
    const siteHeader = document.querySelector('.site-header');
    const minUpvotesInput = document.querySelector('#minUpvotesInput');
    const sortSelect = document.querySelector('#spotSortSelect');
    const radiusSelect = document.querySelector('#radiusSelect');
    const pickLocationButton = document.querySelector('#pickLocationBtn');
    const locationPickStatus = document.querySelector('#locationPickStatus');
    const clearFiltersButton = document.querySelector('#clearFiltersBtn');
    const clearLocationButton = document.querySelector('#clearLocationBtn');
    const activeFilterCount = document.querySelector('#filterActiveCount');

    if (!explore || !searchInput) return;

    const cards = new Map(Array.from(document.querySelectorAll('.spots-grid [data-spot-id]'))
        .map(card => [String(card.dataset.spotId), card]));
    const spotsGrid = document.querySelector('.spots-grid');
    const originalCardOrder = new Map(Array.from(cards.keys()).map((id, index) => [id, index]));
    const noResultsMessage = document.querySelector('#spot-search-empty');
    let selectedCenter = null;
    let radiusCircle = null;
    let centerMarker = null;
    let isPickingLocation = false;

    function updateActiveFilterCount() {
        const activeCategories = categoryInputs.filter(input => input.checked).length;
        const activeCount = activeCategories
            + (searchInput.value.trim() !== '' ? 1 : 0)
            + (minUpvotesInput?.value.trim() !== '' ? 1 : 0)
            + (sortSelect && sortSelect.value !== 'default' ? 1 : 0)
            + (selectedCenter !== null ? 1 : 0);

        if (activeFilterCount) {
            activeFilterCount.textContent = String(activeCount);
            activeFilterCount.hidden = activeCount === 0;
        }
        if (clearLocationButton) clearLocationButton.hidden = selectedCenter === null;
    }

    function filterSpots() {
        const query = searchInput.value.trim().toLocaleLowerCase();
        const selectedCategories = categoryInputs.filter(input => input.checked).map(input => input.value);
        const minUpvotesValue = minUpvotesInput?.value.trim() ?? '';
        const minUpvotes = minUpvotesValue === '' ? null : Number.parseInt(minUpvotesValue, 10);
        const radiusKm = Number.parseFloat(radiusSelect?.value ?? '10');
        const radiusMeters = radiusKm * 1000;
        const visibleCoordinates = [];
        let visibleCount = 0;

        explore.spots.forEach(spot => {
            const card = cards.get(String(spot.id));
            if (!card) return;

            const categories = (card.dataset.category || '')
                .split(',')
                .map(category => category.trim().toLocaleLowerCase())
                .filter(Boolean);
            const normalizedSpotCategories = categories.length > 0
                ? categories
                : (Array.isArray(spot.categories) ? spot.categories.map(category => String(category).trim().toLocaleLowerCase()) : []);
            const title = card.dataset.title || spot.title || '';
            const matchesQuery = query === ''
                || title.toLocaleLowerCase().includes(query)
                || normalizedSpotCategories.some(category => category.includes(query));
            const matchesCategories = selectedCategories.length === 0
                || selectedCategories.some(category => normalizedSpotCategories.includes(category.trim().toLocaleLowerCase()));
            const upvotes = Number.parseInt(card.dataset.upvotes || '0', 10);
            const matchesUpvotes = minUpvotes === null || (Number.isFinite(minUpvotes) && upvotes >= minUpvotes);
            let matchesRadius = true;
            if (selectedCenter !== null) {
                const latitude = Number.parseFloat(card.dataset.lat || '');
                const longitude = Number.parseFloat(card.dataset.lng || '');
                matchesRadius = Number.isFinite(latitude)
                    && Number.isFinite(longitude)
                    && explore.map.distance(selectedCenter, [latitude, longitude]) <= radiusMeters;
            }

            const visible = matchesQuery && matchesCategories && matchesUpvotes && matchesRadius;
            card.hidden = !visible;
            if (visible) visibleCount++;

            const marker = explore.markers.get(String(spot.id));
            if (!marker) return;
            if (visible) {
                marker.addTo(explore.map);
                visibleCoordinates.push(marker.getLatLng());
            } else {
                explore.map.removeLayer(marker);
            }
        });

        if (selectedCenter !== null && radiusCircle !== null) {
            explore.map.fitBounds(radiusCircle.getBounds(), {padding: [24, 24], maxZoom: 15});
        } else if (visibleCoordinates.length > 0) {
            explore.map.fitBounds(visibleCoordinates, {padding: [24, 24], maxZoom: 15});
        } else {
            explore.map.setView([52.1, 19.4], 6);
        }

        if (noResultsMessage) {
            const hasAnyFilter = query !== '' || selectedCategories.length > 0
                || minUpvotes !== null || selectedCenter !== null;
            noResultsMessage.hidden = visibleCount > 0 || !hasAnyFilter;
        }

        updateActiveFilterCount();

        if (spotsGrid) {
            const sortMode = sortSelect?.value ?? 'default';
            const sortedCards = Array.from(cards.entries()).sort(([idA, cardA], [idB, cardB]) => {
                if (sortMode === 'default') return originalCardOrder.get(idA) - originalCardOrder.get(idB);
                const votesA = Number.parseInt(cardA.dataset.upvotes || '0', 10);
                const votesB = Number.parseInt(cardB.dataset.upvotes || '0', 10);
                const voteOrder = sortMode === 'upvotes-desc' ? votesB - votesA : votesA - votesB;
                return voteOrder || originalCardOrder.get(idA) - originalCardOrder.get(idB);
            });
            sortedCards.forEach(([, card]) => spotsGrid.appendChild(card));
        }

        const url = new URL(window.location.href);
        if (searchInput.value.trim()) url.searchParams.set('q', searchInput.value.trim());
        else url.searchParams.delete('q');
        url.searchParams.delete('categories[]');
        selectedCategories.forEach(category => url.searchParams.append('categories[]', category));
        window.history.replaceState({}, '', url);
    }

    function stopPickingLocation() {
        isPickingLocation = false;
        explore.map.getContainer().style.cursor = '';
        if (pickLocationButton) pickLocationButton.setAttribute('aria-pressed', 'false');
    }

    function positionFilterPanel() {
        if (!filter?.open || !filterPanel) return;

        const trigger = filter.querySelector('.search-filter-button');
        if (!trigger) return;

        const triggerBounds = trigger.getBoundingClientRect();
        const panelWidth = filterPanel.getBoundingClientRect().width;
        const horizontalPadding = 8;
        const left = Math.max(
            horizontalPadding,
            Math.min(triggerBounds.right - panelWidth, window.innerWidth - panelWidth - horizontalPadding)
        );

        filterPanel.style.left = `${left}px`;
        filterPanel.style.top = `${triggerBounds.bottom + 8}px`;
    }

    filter?.addEventListener('toggle', () => {
        siteHeader?.classList.toggle('has-open-search-filter', filter.open);
        positionFilterPanel();
    });
    window.addEventListener('resize', positionFilterPanel);
    window.addEventListener('scroll', positionFilterPanel, {passive: true});
    form?.addEventListener('submit', event => {
        event.preventDefault();
        filterSpots();
        if (filter) filter.open = false;
    });

    categoryInputs.forEach(input => input.addEventListener('change', filterSpots));
    searchInput.addEventListener('input', updateActiveFilterCount);
    minUpvotesInput?.addEventListener('input', filterSpots);
    sortSelect?.addEventListener('change', filterSpots);
    radiusSelect?.addEventListener('change', () => {
        const radiusMeters = Number.parseFloat(radiusSelect.value) * 1000;
        if (radiusCircle) radiusCircle.setRadius(radiusMeters);
        filterSpots();
    });

    pickLocationButton?.addEventListener('click', () => {
        isPickingLocation = true;
        explore.map.getContainer().style.cursor = 'crosshair';
        pickLocationButton.setAttribute('aria-pressed', 'true');
        if (locationPickStatus) locationPickStatus.textContent = 'Kliknij mapę, aby wybrać środek promienia';
    });

    explore.map.on('click', event => {
        if (!isPickingLocation) return;

        selectedCenter = event.latlng;
        const radiusMeters = Number.parseFloat(radiusSelect?.value ?? '10') * 1000;
        if (radiusCircle) explore.map.removeLayer(radiusCircle);
        if (centerMarker) explore.map.removeLayer(centerMarker);
        radiusCircle = L.circle(selectedCenter, {radius: radiusMeters}).addTo(explore.map);
        centerMarker = L.marker(selectedCenter).addTo(explore.map);
        if (locationPickStatus) {
            locationPickStatus.textContent = `Wybrano punkt (${selectedCenter.lat.toFixed(4)}, ${selectedCenter.lng.toFixed(4)})`;
        }
        updateActiveFilterCount();
        stopPickingLocation();
        filterSpots();
    });

    clearLocationButton?.addEventListener('click', () => {
        if (radiusCircle) explore.map.removeLayer(radiusCircle);
        if (centerMarker) explore.map.removeLayer(centerMarker);
        radiusCircle = null;
        centerMarker = null;
        selectedCenter = null;
        if (locationPickStatus) locationPickStatus.textContent = 'Punkt nie został wybrany';
        filterSpots();
    });

    clearFiltersButton?.addEventListener('click', () => {
        searchInput.value = '';
        categoryInputs.forEach(input => { input.checked = false; });
        if (minUpvotesInput) minUpvotesInput.value = '';
        if (sortSelect) sortSelect.value = 'default';
        if (radiusSelect) radiusSelect.value = '10';
        stopPickingLocation();
        if (radiusCircle) explore.map.removeLayer(radiusCircle);
        if (centerMarker) explore.map.removeLayer(centerMarker);
        radiusCircle = null;
        centerMarker = null;
        selectedCenter = null;
        if (locationPickStatus) locationPickStatus.textContent = 'Punkt nie został wybrany';
        filterSpots();
    });

    filterSpots();
})();
