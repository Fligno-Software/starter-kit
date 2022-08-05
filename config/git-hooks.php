<?php

/**
 * For complete list of Git Hooks, please refer to the official documentation.
 *
 * @link https://git-scm.com/docs/githooks
 * @link https://git-scm.com/book/en/v2/Customizing-Git-Git-Hooks#_git_hooks
 */

return [
    'pre-commit' => [
        'echo Committing codes as $(git config user.name)',
        'echo Running Laravel Pint...',
        './vendor/bin/pint',
        'echo Adding changes by Laravel Pint to staging area...',
        'git add .',
    ],
    'pre-push' => [
        'echo Pushing codes as $(git config user.name)',
        'git fetch',
    ],
];
