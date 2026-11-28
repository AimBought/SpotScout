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
    const pagination = document.querySelector('#explore-pagination');
    const pageSizeSelect = document.querySelector('#spots-page-size');
    const paginationStatus = document.querySelector('#explore-pagination-status');
    const pageControls = document.querySelector('#explore-page-controls');

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
    let currentPage = 1;

    function updatePagination(matchingCards) {
        const pageSize = Number.parseInt(pageSizeSelect?.value ?? '10', 10) || 10;
        const pageCount = Math.max(1, Math.ceil(matchingCards.length / pageSize));
        currentPage = Math.min(currentPage, pageCount);
        const firstIndex = (currentPage - 1) * pageSize;
        const lastIndex = Math.min(firstIndex + pageSize, matchingCards.length);

        cards.forEach(card => { card.hidden = true; });
        matchingCards.slice(firstIndex, lastIndex).forEach(card => { card.hidden = false; });

        if (pagination) pagination.hidden = matchingCards.length <= 10;
        if (paginationStatus) {
            paginationStatus.textContent = matchingCards.length === 0
                ? 'Brak spotów do wyświetlenia'
                : `Wyświetlono ${firstIndex + 1}–${lastIndex} z ${matchingCards.length} spotów`;
        }
        if (!pageControls) return;

        pageControls.replaceChildren();
        const addPageButton = (label, page, {disabled = false, current = false, ariaLabel = label} = {}) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.disabled = disabled;
            button.setAttribute('aria-label', ariaLabel);
            if (current) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', () => {
                currentPage = page;
                updatePagination(matchingCards);
            });
            pageControls.appendChild(button);
        };

        addPageButton('Poprzednia', Math.max(1, currentPage - 1), {
            disabled: currentPage === 1,
            ariaLabel: 'Poprzednia strona spotów',
        });
        const firstPage = Math.max(1, Math.min(currentPage - 2, pageCount - 4));
        const lastPage = Math.min(pageCount, firstPage + 4);
        for (let page = firstPage; page <= lastPage; page++) {
            addPageButton(String(page), page, {current: page === currentPage, ariaLabel: `Strona ${page}`});
        }
        addPageButton('Następna', Math.min(pageCount, currentPage + 1), {
            disabled: currentPage === pageCount,
            ariaLabel: 'Następna strona spotów',
        });
    }

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
        currentPage = 1;
        const query = searchInput.value.trim().toLocaleLowerCase();
        const selectedCategories = categoryInputs.filter(input => input.checked).map(input => input.value);
        const minUpvotesValue = minUpvotesInput?.value.trim() ?? '';
        const minUpvotes = minUpvotesValue === '' ? null : Number.parseInt(minUpvotesValue, 10);
        const radiusKm = Number.parseFloat(radiusSelect?.value ?? '10');
        const radiusMeters = radiusKm * 1000;
        const visibleCoordinates = [];
        const matchingCards = [];

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
            card.dataset.filterMatch = visible ? 'true' : 'false';
            if (visible) matchingCards.push(card);

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
            noResultsMessage.hidden = matchingCards.length > 0 || !hasAnyFilter;
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
            const sortRank = new Map(sortedCards.map(([, card], index) => [card, index]));
            matchingCards.sort((cardA, cardB) => sortRank.get(cardA) - sortRank.get(cardB));
        }
        updatePagination(matchingCards);

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
    searchInput.addEventListener('input', filterSpots);
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

    pageSizeSelect?.addEventListener('change', () => {
        currentPage = 1;
        filterSpots();
    });
    document.addEventListener('spot-upvote-updated', filterSpots);

    filterSpots();
})();
