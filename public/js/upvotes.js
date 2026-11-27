document.addEventListener('submit', async event => {
    const form = event.target.closest('.spot-upvote-form');
    if (!form) return;

    event.preventDefault();
    const button = form.querySelector('.spot-upvote-button');
    if (!button || button.disabled) return;

    button.disabled = true;
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: {Accept: 'application/json'},
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.error || 'Nie udało się zapisać głosu.');

        button.classList.toggle('is-upvoted', result.upvoted);
        button.setAttribute('aria-pressed', result.upvoted ? 'true' : 'false');
        const actionLabel = result.upvoted ? 'Usuń swój upvote' : 'Upvote spot';
        button.title = actionLabel;
        const spotTitle = button.dataset.spotTitle || '';
        button.setAttribute('aria-label', `${actionLabel}: ${spotTitle}`);

        const container = form.closest('.spot-card-vote') || form.closest('.spot-hero-actions')?.parentElement;
        container?.querySelectorAll('[data-upvote-count]').forEach(count => {
            count.textContent = String(result.count);
            count.setAttribute('aria-label', `Liczba upvote’ów: ${result.count}`);
        });
    } catch (error) {
        window.alert(error instanceof Error ? error.message : 'Nie udało się zapisać głosu.');
    } finally {
        button.disabled = false;
    }
});
