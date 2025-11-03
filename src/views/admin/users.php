<?php
/**
 * @var string $title
 * @var array $users
 */
?>

<div class="admin-container">
    <h1>Gestion des utilisateurs</h1>

    <div class="admin-links">
        <a href="/admin" class="btn">Retour au tableau de bord</a>
    </div>

    <div class="admin-panel">
        <table class="admin-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Pseudo</th>
                <th>Email</th>
                <th>Date d'inscription</th>
                <th>Dernière connexion</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $currentUser = (new \Sae\Models\Repository\UserRepository())->selectCurrent();
            foreach ($users as $user):
                $isCurrentUser = $user->getId() === $currentUser->getId();
                $isAdmin = $user->isAdmin();
                $canModify = !$isCurrentUser && !$isAdmin;
                ?>
                <tr<?= $isAdmin ? ' class="admin-row"' : '' ?>>
                    <td><?= $user->getId() ?></td>
                    <td><?= htmlspecialchars($user->getLastName()) ?></td>
                    <td><?= htmlspecialchars($user->getName()) ?></td>
                    <td><?= htmlspecialchars($user->getPseudo()) ?></td>
                    <td><?= htmlspecialchars($user->getEmail()) ?></td>
                    <td><?= $user->getCreation() ?></td>
                    <td><?= $user->getLastLogin() ?></td>
                    <td>
                        <?php if ($canModify): ?>
                            <?php if ($user->isAdmin()): ?>
                                <form action="/admin/updateUser" method="post">
                                    <input type="hidden" name="userId" value="<?= $user->getId() ?>">
                                    <input type="hidden" name="action" value="changeRole">
                                    <input type="hidden" name="role" value="0">
                                    <button type="submit" class="btn-warning">Rétrograder</button>
                                </form>
                            <?php else: ?>
                                <span>Utilisateur</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?= $user->isAdmin() ? '<span class="admin-badge">Administrateur</span>' : 'Utilisateur' ?>
                            <?php if ($isCurrentUser): ?>
                                <small>(vous)</small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td class="action-buttons">
                        <a href="/admin/details/<?= $user->getId() ?>" class="btn btn-info">Détails</a>

                        <?php if (!$isAdmin || $isCurrentUser): ?>
                            <a href="/admin/update/<?= $user->getId() ?>" class="btn btn-primary">Modifier</a>
                        <?php endif; ?>

                        <?php if ($canModify): ?>
                            <form action="/admin/deleteUser" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                <input type="hidden" name="userId" value="<?= $user->getId() ?>">
                                <button type="submit" class="btn-danger">Supprimer</button>
                            </form>
                        <?php else: ?>
                            <button class="btn-disabled" disabled title="<?= $isCurrentUser ? 'Vous ne pouvez pas supprimer votre compte' : 'Vous ne pouvez pas supprimer un administrateur' ?>">Supprimer</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>