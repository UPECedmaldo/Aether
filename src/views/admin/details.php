<?php
/**
 * @var string $title
 * @var \Sae\Models\DataObject\User $user
 */
?>

<div class="admin-container">
    <h1>Détails de l'utilisateur</h1>

    <div class="admin-links">
        <a href="/admin/users" class="btn">Retour à la liste</a>
        <a href="/admin/update/<?= $user->getId() ?>" class="btn btn-primary">Modifier</a>
    </div>

    <div class="admin-panel user-details">
        <div class="user-header">
            <h2><?= htmlspecialchars($user->getPseudo()) ?></h2>
            <span class="role-badge <?= $user->isAdmin() ? 'role-admin' : 'role-user' ?>">
                <?= $user->isAdmin() ? 'Administrateur' : 'Utilisateur' ?>
            </span>
        </div>

        <div class="user-info">
            <div class="info-group">
                <h3>Informations personnelles</h3>
                <div class="info-row">
                    <span class="info-label">ID:</span>
                    <span class="info-value"><?= $user->getId() ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nom:</span>
                    <span class="info-value"><?= htmlspecialchars($user->getLastName()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Prénom:</span>
                    <span class="info-value"><?= htmlspecialchars($user->getName()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?= htmlspecialchars($user->getEmail()) ?></span>
                </div>
            </div>

            <div class="info-group">
                <h3>Détails du compte</h3>
                <div class="info-row">
                    <span class="info-label">Pseudo:</span>
                    <span class="info-value"><?= htmlspecialchars($user->getPseudo()) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date de création:</span>
                    <span class="info-value"><?= $user->getCreation() ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dernière connexion:</span>
                    <span class="info-value"><?= $user->getLastLogin() ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Rôle:</span>
                    <span class="info-value"><?= $user->isAdmin() ? 'Administrateur' : 'Utilisateur standard' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>