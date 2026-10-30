console.log('SpotScout script loaded.');

const initializeSpotSearch = () => {
    const searchInput = document.querySelector('#search-spots');
    const spotCards = document.querySelectorAll('.spots-grid .spot-card');

    if (searchInput && spotCards.length > 0) {
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLocaleLowerCase();

            spotCards.forEach((card) => {
                const cardText = card.textContent.toLocaleLowerCase();
                card.hidden = !cardText.includes(query);
            });
        });
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSpotSearch, { once: true });
} else {
    initializeSpotSearch();
}
