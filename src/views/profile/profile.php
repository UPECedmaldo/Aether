<?php
use Sae\Models\DataObject\Profile;
use Sae\Models\Repository\UserRepository;

// On suppose que cette variable est déjà passée à la vue
/** @var Profile $profile */
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-username">
            <span><img src="<?= $profile->getAvatar() ?>" alt="Photo de profil"></span>
        </div>
        <h1 class="profile-username">@<?= htmlspecialchars($profile->getUserName()) ?></h1>
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-count"><?= $profile->getFollowerCount() ?></span>
                <span class="stat-label">Abonné(s)</span>
            </div>
            <div class="stat-item">
                <span class="stat-count"><?= $profile->getLibraryCount() ?></span>
                <span class="stat-label">Bibliothèque(s)</span>
            </div>
        </div>
        <form action="/profile/follow" method="post" class="follow-form">
            <input type="hidden" name="targetId" value="<?= $profile->getId() ?>">
            <button type="submit" class="follow-button">S'abonner</button>
        </form>
    </div>

    <div class="profile-content">
        <div class="content-tabs">
            <button class="tab-button active" data-tab="measures">Bibliothèques</button>
            <button class="tab-button" data-tab="friends">Amis</button>
        </div>

        <div class="tab-content" id="measures-content">
            <div class="measures-grid">
                <?php
                if (!empty($libraries)) {
                    foreach ($libraries as $library) {
                        ?>
                        <div class="library-card" style="background-color: <?= htmlspecialchars($library->getColor()) ?>">
                            <h3 class="library-name"><?= htmlspecialchars($library->getName()) ?></h3>
                            <a href="/library/consult?library=<?= $library->getId() ?>" class="library-link-button">M'y rendre</a>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <p>Aucune bibliothèque publique</p>
                    </div>
                <?php } ?>
            </div>
        </div>

        <div class="tab-content hidden" id="friends-content">
            <div class="friends-list">
                <?php if (isset($followers) && !empty($followers)): ?>

                    <?php
                        $repository = new UserRepository();
                    ?>

                    <?php foreach ($followers as $follower): ?>
                        <a href="/profile/<?= $follower->getId() ?>" class="follower-card">
                            <div class="follower-avatar">
                                <img src="<?= $repository->profilePictureUrl($follower->getId()) ?>" alt="Avatar de <?= htmlspecialchars($follower->getPseudo()) ?>">
                            </div>
                            <div class="follower-info">
                                <h3 class="follower-name">@<?= htmlspecialchars($follower->getPseudo()) ?></h3>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Aucun abonné pour le moment</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
    .profile-container {
        max-width: 100%;
        margin: 85px auto 0; /* Ajout d'une marge supérieure correspondant à la hauteur de la navbar */
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        color: #121212;
        padding-top: 20px; /* Espace supplémentaire en haut */
    }

    /* Le reste du CSS reste inchangé */
    .profile-header {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e6e6e6;
    }

    .profile-username {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 16px 0;
    }

    .profile-stats {
        display: flex;
        gap: 40px;
        margin-bottom: 20px;
    }

    .stat-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .stat-count {
        font-size: 18px;
        font-weight: 700;
    }

    .stat-label {
        font-size: 14px;
        color: #767676;
    }

    .follow-button {
        background-color: #fe2c55;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 24px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .follow-button:hover {
        background-color: #ea284f;
    }

    .profile-content {
        padding: 16px;
    }

    .content-tabs {
        display: flex;
        justify-content: center;
        border-bottom: 1px solid #e6e6e6;
        margin-bottom: 20px;
    }

    .tab-button {
        background: none;
        border: none;
        padding: 12px 16px;
        font-size: 16px;
        font-weight: 600;
        color: #767676;
        cursor: pointer;
        position: relative;
    }

    .tab-button.active {
        color: #121212;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #fe2c55;
    }

    .tab-content {
        padding: 16px 0;
    }

    .hidden {
        display: none;
    }

    .measures-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 8px;
    }

    .friends-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .empty-state {
        text-align: center;
        padding: 40px 0;
        color: #767676;
    }

    @media (max-width: 768px) {
        .profile-stats {
            gap: 20px;
        }
    }

    .follow-form {
        margin: 0;
        padding: 0;
    }

    .measures-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        padding: 10px;
    }

    .library-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 20px;
        border-radius: 12px;
        min-height: 160px;
        color: white;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .library-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.2);
        z-index: 0;
    }

    .library-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    }

    .library-name {
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 15px 0;
        word-break: break-word;
        position: relative;
        z-index: 1;
    }

    .library-link-button {
        display: inline-block;
        background-color: rgba(255, 255, 255, 0.3);
        color: white;
        padding: 8px 14px;
        border-radius: 6px;
        text-decoration: none;
        text-align: center;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.2s;
        backdrop-filter: blur(2px);
        position: relative;
        z-index: 1;
        border: 1px solid rgba(255, 255, 255, 0.4);
    }

    .library-link-button:hover {
        background-color: rgba(255, 255, 255, 0.45);
        transform: scale(1.05);
    }

    @media (max-width: 768px) {
        .measures-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .measures-grid {
            grid-template-columns: 1fr;
        }
    }

    .follower-card {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-radius: 8px;
        background-color: #f8f8f8;
        margin-bottom: 10px;
        text-decoration: none;
        color: inherit;
        transition: background-color 0.2s, transform 0.2s;
    }

    .follower-card:hover {
        background-color: #f0f0f0;
        transform: translateY(-2px);
    }

    .follower-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #fe2c55;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
        font-weight: bold;
        margin-right: 16px;
    }

    .follower-info {
        flex: 1;
    }

    .follower-name {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .profile-username img {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 16px;
    }

    .profile-avatar {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        background-color: #147aff; /* Remplacé #fe2c55 par #147aff */
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 40px;
        font-weight: bold;
        margin-bottom: 16px;
    }

    .tab-button.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #147aff; /* Remplacé #fe2c55 par #147aff */
    }

    .follower-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #147aff; /* Remplacé #fe2c55 par #147aff */
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
        font-weight: bold;
        margin-right: 16px;
        overflow: hidden;
    }

    .follower-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .follow-button {
        background-color: #147aff; /* Remplacé #fe2c55 par #147aff */
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 24px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .follow-button:hover {
        background-color: #0056b3; /* Remplacé #ea284f par une teinte plus foncée du bleu */
    }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.getAttribute('data-tab');

                // Mettre à jour les boutons
                tabButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Mettre à jour le contenu
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                document.getElementById(`${tabName}-content`).classList.remove('hidden');
            });
        });
    });
</script>