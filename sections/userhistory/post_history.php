<?php

if (!empty($LoggedUser['DisableForums'])) {
    error(403);
}
$user = (new Gazelle\Manager\User)->findById(empty($_GET['userid']) ? $LoggedUser['ID'] : (int)$_GET['userid']);
if (!$user) {
    error(404);
}

$ownProfile = ($user->id() === $LoggedUser['ID']);
$showUnread = ($ownProfile && (!isset($_GET['showunread']) || !!$_GET['showunread']));
$showGrouped = ($ownProfile && (!isset($_GET['group']) || !!$_GET['group']));

if ($showGrouped) {
    $title = 'Grouped '.($showUnread ? 'unread ' : '')."post history";
} elseif ($showUnread) {
    $title = "Unread post history";
} else {
    $title = "Post history";
}

$forumSearch = (new Gazelle\ForumSearch(new Gazelle\User($LoggedUser['ID'])))
    ->setPosterId($user->id())
    ->setShowGrouped($ownProfile && $showGrouped)
    ->setShowUnread($ownProfile && $showUnread);

$paginator = new Gazelle\Util\Paginator($LoggedUser['PostsPerPage'] ?? POSTS_PER_PAGE, (int)($_GET['page'] ?? 1));
$paginator->setTotal($forumSearch->postHistoryTotal());

View::show_header($user->username() . " &rsaquo; $title", 'subscriptions,comments,bbcode');

echo G::$Twig->render('user/post-history.twig', [
    'is_fmod'       => check_perms('site_moderate_forums'),
    'own_profile'   => $ownProfile,
    'paginator'     => $paginator,
    'posts'         => $forumSearch->postHistoryPage($paginator->limit(), $paginator->offset()),
    'show_avatar'   => Users::has_avatars_enabled(),
    'show_grouped'  => $showGrouped,
    'show_unread'   => $showUnread,
    'subscriptions' => (new \Gazelle\Manager\Subscription($user->id()))->subscriptions(),
    'title'         => $title,
    'url_stem'      => 'userhistory.php?action=posts&amp;userid=' . $user->id() . '&amp;',
    'user'          => $user,
]);

View::show_footer();
