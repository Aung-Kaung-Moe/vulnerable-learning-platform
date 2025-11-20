<?php
require_once __DIR__ . '/auth.php';

/**
 * HTML escape helper
 */
function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_user_name(): string
{
    return $_SESSION['user'] ?? 'Guest';
}

function current_user_email(): string
{
    return $_SESSION['email'] ?? '';
}

/**
 * Fake data used by the different views.
 * You can change / extend these freely.
 */

function get_courses(): array
{
    return [
        [
            'id'          => 1,
            'title'       => 'Intro to Web Exploitation',
            'tag'         => 'Web',
            'difficulty'  => 'Beginner',
            'description' => 'Basic HTTP, cookies, and simple web vulns.',
        ],
        [
            'id'          => 2,
            'title'       => 'SQL Injection 101',
            'tag'         => 'Web',
            'difficulty'  => 'Intermediate',
            'description' => 'Classic SQLi flows, error-based and blind.',
        ],
        [
            'id'          => 3,
            'title'       => 'Intro to Reverse Engineering',
            'tag'         => 'Reversing',
            'difficulty'  => 'Intermediate',
            'description' => 'ELF/PE basics, strings, xrefs, stack.',
        ],
    ];
}

function get_tracks(): array
{
    return [
        [
            'id'       => 1,
            'title'    => 'Web Bug Hunter Path',
            'tag'      => 'Web',
            'level'    => 'Intermediate',
            'duration' => '6–10 hours',
            'labs'     => [1, 2, 3],
        ],
        [
            'id'       => 2,
            'title'    => 'Binary Exploitation Starter',
            'tag'      => 'Pwn',
            'level'    => 'Intermediate',
            'duration' => '8–12 hours',
            'labs'     => [4, 5],
        ],
    ];
}

function get_labs(): array
{
    return [
        [
            'id'          => 1,
            'title'       => 'Reflected XSS Basics',
            'category'    => 'Web',
            'difficulty'  => 'Beginner',
            'description' => 'Find and exploit a simple reflected XSS.',
        ],
        [
            'id'          => 2,
            'title'       => 'Login Bypass via SQLi',
            'category'    => 'Web',
            'difficulty'  => 'Intermediate',
            'description' => 'Classic `OR 1=1` style injection on login.',
        ],
        [
            'id'          => 3,
            'title'       => 'IDOR on Profile Endpoint',
            'category'    => 'Web',
            'difficulty'  => 'Intermediate',
            'description' => 'Guess victim IDs and steal their data.',
        ],
        [
            'id'          => 4,
            'title'       => 'Stack Buffer Overflow (32-bit)',
            'category'    => 'Pwn',
            'difficulty'  => 'Intermediate',
            'description' => 'Smash the stack and hijack control flow.',
        ],
        [
            'id'          => 5,
            'title'       => 'Format String Bug',
            'category'    => 'Pwn',
            'difficulty'  => 'Hard',
            'description' => 'Abuse `%n` to overwrite memory.',
        ],
    ];
}

function get_track_labs(int $trackId): array
{
    $tracks = get_tracks();
    $labs   = get_labs();

    foreach ($tracks as $t) {
        if ($t['id'] == $trackId) {
            $result = [];
            foreach ($t['labs'] as $labId) {
                foreach ($labs as $lab) {
                    if ($lab['id'] == $labId) {
                        $result[] = $lab;
                    }
                }
            }
            return $result;
        }
    }

    return [];
}
