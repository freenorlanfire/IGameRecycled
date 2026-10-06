<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function ir_portal_base_path()
{
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '';
    $dir = str_replace('\\', '/', dirname($scriptName));

    if ($dir === '/' || $dir === '.') {
        return '';
    }

    if (substr($dir, -6) === '/games') {
        $dir = substr($dir, 0, -6);
    }

    return rtrim($dir, '/');
}

function ir_url($path = '')
{
    $base = ir_portal_base_path();
    $path = ltrim((string) $path, '/');

    if ($path === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $path;
}

function ir_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function ir_game_catalog()
{
    return array(
        array(
            'title' => 'Neon Archer',
            'slug' => 'archery',
            'file' => 'archery.php',
            'thumb' => '../img/thumbnail-archery.svg',
            'category' => 'Shooter',
            'description' => 'Aim with precision and hit moving targets in a neon shooting challenge.',
            'is_featured' => true,
            'is_new' => false,
            'tags' => array('aim', 'arcade', 'precision')
        ),
        array(
            'title' => 'Neon Duck Hunt',
            'slug' => 'duck-hunt',
            'file' => 'duck-hunt.php',
            'thumb' => '../img/thumbnail-duck-hunt.svg',
            'category' => 'Shooter',
            'description' => 'Track fast targets, react quickly, and improve your accuracy streak.',
            'is_featured' => true,
            'is_new' => false,
            'tags' => array('reaction', 'targets', 'arcade')
        ),
        array(
            'title' => 'Neon Flappy',
            'slug' => 'neon-flappy',
            'file' => 'neon-flappy.php',
            'thumb' => '../img/thumbnail-neon-flappy.svg',
            'category' => 'Endless',
            'description' => 'Fly between obstacles and survive as long as possible in classic endless mode.',
            'is_featured' => true,
            'is_new' => false,
            'tags' => array('endless', 'tap', 'reflex')
        ),
        array(
            'title' => 'Neon Flappy V2',
            'slug' => 'neon-flappy-v2',
            'file' => 'neon-flappy-v2.php',
            'thumb' => '../img/thumbnail-flappy-v2.svg',
            'category' => 'Endless',
            'description' => 'Take on a harder variation with faster pacing and tighter lanes.',
            'is_featured' => false,
            'is_new' => true,
            'tags' => array('endless', 'hard mode', 'skill')
        ),
        array(
            'title' => 'Nitro Highway',
            'slug' => 'nitro-highway',
            'file' => 'nitro-highway.php',
            'thumb' => '../img/thumbnail-nitro-highway.svg',
            'category' => 'Racing',
            'description' => 'Drive through neon traffic and keep your combo alive at high speed.',
            'is_featured' => true,
            'is_new' => false,
            'tags' => array('cars', 'speed', 'arcade')
        ),
        array(
            'title' => 'Nitro Highway 2',
            'slug' => 'nitro-highway-2',
            'file' => 'nitro-highway-2.php',
            'thumb' => '../img/thumbnail-nitro-highway-2.svg',
            'category' => 'Racing',
            'description' => 'Sequel with denser traffic patterns and upgraded visual intensity.',
            'is_featured' => false,
            'is_new' => true,
            'tags' => array('cars', 'dodging', 'high score')
        ),
        array(
            'title' => 'Neon Snake',
            'slug' => 'snake',
            'file' => 'snake.php',
            'thumb' => '../img/thumbnail-snake.svg',
            'category' => 'Classic',
            'description' => 'Grow your neon snake, avoid collisions, and chase a new top score.',
            'is_featured' => false,
            'is_new' => false,
            'tags' => array('classic', 'grid', 'strategy')
        ),
        array(
            'title' => 'Neon Spaceman',
            'slug' => 'spaceman',
            'file' => 'spaceman.php',
            'thumb' => '../img/thumbnail-spaceman.svg',
            'category' => 'Action',
            'description' => 'Jump through space-themed hazards and survive the escalating challenge.',
            'is_featured' => false,
            'is_new' => true,
            'tags' => array('platform', 'timing', 'space')
        ),
    );
}

function ir_categories()
{
    $categories = array();

    foreach (ir_game_catalog() as $game) {
        $categories[] = $game['category'];
    }

    $categories = array_values(array_unique($categories));
    sort($categories);

    return $categories;
}
