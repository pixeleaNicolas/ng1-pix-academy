document.addEventListener('DOMContentLoaded', () => {
    // --- Éléments du DOM ---
    const filtersContainer = document.getElementById('ng1-tag-filters-container');
    const videoContainer = document.getElementById('ng1-video-container');
    const modal = document.getElementById('ng1-modal');
    const modalCloseBtn = document.querySelector('.ng1-modal-close');
    const modalTitle = document.getElementById('ng1-modal-title');
    const modalVideo = document.getElementById('ng1-modal-video');
    const modalDescription = document.getElementById('ng1-modal-description');

    if (!videoContainer || !modal || !filtersContainer) return;

    // --- Variables d'état ---
    let allFetchedVideos = []; // Stocke toutes les vidéos pour un accès rapide
    const { api_url, visible_videos, diffuseur_slug } = ng1_pix_academy_data;

    // --- Vérification initiale ---
    if (!api_url) {
        videoContainer.innerHTML = `<div class="ng1-notice">Veuillez configurer l'URL de l'API dans les <a href="admin.php?page=ng1_pix_academy_settings">réglages</a>.</div>`;
        filtersContainer.style.display = 'none';
        return;
    }

    // --- Fonctions ---

    /**
     * Crée le HTML pour une carte vidéo, en incluant les slugs des tags dans un data-attribute pour le filtrage.
     * @param {object} video - L'objet de données de la vidéo.
     */
    const createVideoCard = (video) => {
        const tagsHTML = video.tags.map(tag => `<span class="ng1-tag" data-tags="${tag.slug}">${tag.name}</span>`).join('');
        const tagSlugs = video.tags.map(tag => tag.slug).join(' ');
        //const thumbnailUrl = video.thumbnail?.thumbnail?.url || '';
        let thumbnailUrl = video.thumbnail?.thumbnail?.url || '';
        if (thumbnailUrl) {
            thumbnailUrl = thumbnailUrl.replace(/-150x150(\.\w+)$/, '$1');
        }
        return `
            <div class="ng1-video-card" data-video-id="${video.id}" data-tags="${tagSlugs}">
                ${thumbnailUrl ? `<img src="${thumbnailUrl}" alt="${video.title.rendered}">` : ''}
                <div class="ng1-video-card-content">
                    <h3>${video.title.rendered}</h3>
                    <div class="ng1-video-card-tags">${tagsHTML}</div>
                </div>
            </div>
        `;
    };

    /**
     * Affiche les vidéos dans le conteneur principal.
     * @param {Array} videos - Une liste d'objets vidéo.
     */
    const displayVideos = (videos) => {
        videoContainer.innerHTML = '';
        const allowedVideos = (Array.isArray(visible_videos) && visible_videos.length > 0)
            ? videos.filter(video => visible_videos.includes(String(video.id)))
            : videos;

        if (allowedVideos.length === 0) {
            videoContainer.innerHTML = `<div class="ng1-notice">Aucune vidéo à afficher. Vérifiez vos réglages de visibilité dans Pix Academie → Réglages API.</div>`;
            return;
        }

        allowedVideos.forEach(video => {
            videoContainer.insertAdjacentHTML('beforeend', createVideoCard(video));
        });
    };

    /**
     * Gère le filtrage côté client en masquant/affichant les cartes vidéo.
     * @param {string} tagSlug - Le slug du tag à filtrer, ou 'all' pour tout afficher.
     */
    const filterVideos = (tagSlug) => {
        const allCards = document.querySelectorAll('.ng1-video-card');
        allCards.forEach(card => {
            const cardTags = card.dataset.tags || '';
            if (tagSlug === 'all' || cardTags.includes(tagSlug)) {
                card.classList.remove('ng1-card-hidden');
            } else {
                card.classList.add('ng1-card-hidden');
            }
        });
    };

    /**
     * Récupère les tags depuis l'API et crée les boutons de filtre.
     */
    const initializeFilters = async () => {
        try {
            const base = `${api_url}wp-json/ng1-video-sharing-api/v1`;
            const url = diffuseur_slug
                ? `${base}/video-tags/by-user/${encodeURIComponent(diffuseur_slug)}`
                : `${base}/video-tags`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur réseau lors de la récupération des tags.');

            const tags = await response.json();
            filtersContainer.innerHTML = ''; // Vide le message "Chargement..."

            // Crée le bouton "Toutes les vidéos"
            const allButton = document.createElement('button');
            allButton.className = 'ng1-tag-filter-btn active'; // Actif par défaut
            allButton.textContent = 'Toutes les vidéos';
            allButton.dataset.tagSlug = 'all';
            filtersContainer.appendChild(allButton);

            // Crée un bouton pour chaque tag
            tags.forEach(tag => {
                const tagButton = document.createElement('button');
                tagButton.className = 'ng1-tag-filter-btn';
                tagButton.textContent = tag.name;
                tagButton.dataset.tagSlug = tag.slug;
                filtersContainer.appendChild(tagButton);
            });

            // Ajoute un écouteur d'événement sur le conteneur des filtres
            filtersContainer.addEventListener('click', (e) => {
                if (e.target.matches('.ng1-tag-filter-btn')) {
                    filtersContainer.querySelector('.active').classList.remove('active');
                    e.target.classList.add('active');
                    filterVideos(e.target.dataset.tagSlug);
                }
            });

        } catch (error) {
            filtersContainer.innerHTML = `<p>Impossible de charger les filtres.</p>`;
            console.error('Erreur chargement des tags:', error);
        }
    };
    
    /**
     * Récupère TOUTES les vidéos une seule fois au chargement de la page.
     */
    const fetchAllVideos = async () => {
        videoContainer.innerHTML = '<p class="ng1-loading">Chargement des vidéos...</p>';
        try {
            const base = `${api_url}wp-json/ng1-video-sharing-api/v1`;
            const url = diffuseur_slug
                ? `${base}/videos/by-user/${encodeURIComponent(diffuseur_slug)}?per_page=100`
                : `${base}/videos?per_page=100`;
            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur réseau lors de la récupération des vidéos.');
            
            const videos = await response.json();
            allFetchedVideos = videos;
            displayVideos(videos);
        } catch (error) {
            console.error('Erreur:', error);
            videoContainer.innerHTML = `<div class="ng1-notice notice-error">${error.message}</div>`;
        }
    };

    /**
     * Crée une URL "embed" à partir de différents formats de liens (YouTube, Vimeo).
     * @param {string} url - L'URL originale de la vidéo.
     */
    const createEmbedUrl = (url) => {
        let embedUrl = "";
        try {
            const videoUrl = new URL(url);
            if (videoUrl.hostname.includes("youtube.com") || videoUrl.hostname.includes("youtu.be")) {
                const videoId = videoUrl.hostname.includes("youtu.be") ? videoUrl.pathname.slice(1) : videoUrl.searchParams.get("v");
                if (videoId) embedUrl = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
            } else if (videoUrl.hostname.includes("vimeo.com")) {
                const videoId = videoUrl.pathname.split("/").pop();
                if (videoId) embedUrl = `https://player.vimeo.com/video/${videoId}?autoplay=1`;
            }
        } catch (e) {
            console.error("URL de vidéo invalide:", url);
        }
        return embedUrl;
    };

    /**
     * Ouvre et remplit la popup avec les données d'une vidéo.
     * @param {string} videoId - L'ID de la vidéo à afficher.
     */
    const openModal = (videoId) => {
        const video = allFetchedVideos.find(v => v.id == videoId);
        if (!video) return;

        const embedUrl = createEmbedUrl(video.video_details.url);
        modalTitle.innerHTML = video.title.rendered;
        modalDescription.innerHTML = video.content.rendered;
        
        if (embedUrl) {
            modalVideo.innerHTML = `<iframe src="${embedUrl}" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>`;
        } else {
            modalVideo.innerHTML = `<p>Vidéo non disponible en format "embed". <a href="${video.video_details.url}" target="_blank">Voir sur le site d'origine</a>.</p>`;
        }
        modal.style.display = 'flex';
    };

    /**
     * Ferme la popup et arrête la lecture de la vidéo.
     */
    const closeModal = () => {
        modal.style.display = 'none';
        modalTitle.innerHTML = '';
        modalDescription.innerHTML = '';
        modalVideo.innerHTML = ''; // Vide l'iframe pour stopper la vidéo
    };

    // --- Écouteurs d'événements ---

    // Ouvre la popup au clic sur une carte vidéo
    videoContainer.addEventListener('click', (e) => {
        const card = e.target.closest('.ng1-video-card');
        if (card) {
            openModal(card.dataset.videoId);
        }
    });

    // Ferme la popup
    modalCloseBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) { // Clic sur le fond gris uniquement
            closeModal();
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape" && modal.style.display === 'flex') {
            closeModal();
        }
    });

    // --- Initialisation de l'application ---
    initializeFilters();
    fetchAllVideos();
});