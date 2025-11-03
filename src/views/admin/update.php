<?php
/**
 * @var string $title
 * @var \Sae\Models\DataObject\User $user
 */

// On récupère l'utilisateur courant pour comparer
$currentUser = (new \Sae\Models\Repository\UserRepository())->selectCurrent();
$isCurrentUser = $user->getId() === $currentUser->getId();
$isAdmin = $user->isAdmin();
$canChangeRole = !$isAdmin || $isCurrentUser;
?>

<div class="admin-container">
    <h1>Modifier l'utilisateur</h1>

    <div class="admin-links">
        <a href="/admin/users" class="btn">Retour à la liste</a>
        <a href="/admin/details/<?= $user->getId() ?>" class="btn btn-secondary">Voir les détails</a>
    </div>

    <div class="admin-panel">
        <form action="/admin/update" method="post" class="update-form">
            <input type="hidden" name="userId" value="<?= $user->getId() ?>">

            <div class="form-group">
                <label for="pseudo">Pseudo:</label>
                <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($user->getPseudo()) ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom:</label>
                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user->getName()) ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user->getLastName()) ?>" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" class="form-control" required>
            </div>

            <?php if(!$isAdmin): ?>
                <div class="form-group">
                    <label for="role">Rôle:</label>
                    <select id="role" name="role" class="form-control" <?= $isCurrentUser ? 'disabled' : '' ?>>
                        <option value="0" <?= $user->getRole() === 0 ? 'selected' : '' ?>>Utilisateur standard</option>
                        <option value="1" <?= $user->getRole() === 1 ? 'selected' : '' ?>>Administrateur</option>
                    </select>
                    <?php if($isCurrentUser): ?>
                        <small class="form-text">Vous ne pouvez pas modifier votre propre rôle.</small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>