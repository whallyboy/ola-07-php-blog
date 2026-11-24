<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only run from the command line.\n");
}

require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';

$authorId = (int) $pdo->query('SELECT id FROM admins ORDER BY id LIMIT 1')->fetchColumn();
if ($authorId < 1) {
    fwrite(STDERR, "No administrator exists. Run create-admin.php first.\n");
    exit(1);
}

$categories = [
    'Technology' => [
        'Practical AI Tools for Everyday Work',
        'Choosing a Laptop That Fits Your Needs',
        'A Simple Guide to Safer Online Accounts',
    ],
    'Tutorials' => [
        'Build a Consistent Morning Writing Habit',
        'A Beginner-Friendly Guide to Website Backups',
        'Organize Digital Files Without Overthinking It',
    ],
    'Productivity' => [
        'Plan Your Week in Thirty Focused Minutes',
        'Make Room for Deep Work in a Busy Schedule',
        'A Small-Tasks System That Keeps Projects Moving',
    ],
    'Lifestyle' => [
        'Create a Calmer and More Useful Home Workspace',
        'Simple Ways to Spend More Time Outdoors',
        'Make Reading a Relaxing Part of Your Day',
    ],
];

$articles = [
    [
        'Practical AI Tools for Everyday Work',
        'A useful AI workflow starts with a clear, repeatable task. Use a tool to outline a meeting summary, compare ideas, or draft a checklist, then review the result before relying on it.',
        'A grounded approach to using AI for routine tasks while keeping human review at the center.',
        "Start with a task you already understand and define what a good result looks like.\n\nGive the tool relevant context, ask for a specific format, and check important facts yourself. Avoid entering private customer or account information into services that are not approved for that data.\n\nTreat the output as a first draft. Your judgment and subject knowledge are what make it useful.",
    ],
    [
        'Choosing a Laptop That Fits Your Needs',
        'The best laptop is the one that handles your actual workload comfortably. Think about screen size, battery life, memory, keyboard quality, and repair support before comparing model names.',
        'A practical checklist for choosing a laptop based on work habits, budget, and longevity.',
        "Write down the applications you use and whether you travel with your computer. This quickly narrows the useful options.\n\nFor everyday browsing and office work, prioritize a comfortable screen, enough memory for multitasking, and a reliable solid-state drive. Creative and technical workloads may need more processing power.\n\nCompare warranty and repair options too. A dependable machine that is easy to maintain can be a better long-term value.",
    ],
    [
        'A Simple Guide to Safer Online Accounts',
        'Unique passwords and multi-factor authentication reduce the chance that one exposed password becomes a problem everywhere. A password manager makes both habits easier to maintain.',
        'Straightforward account-security habits that protect personal and work logins.',
        "Use a long, unique password for every important account. A reputable password manager can create and remember them without relying on small variations of one familiar password.\n\nTurn on multi-factor authentication, especially for email, banking, and administrator accounts. Keep recovery methods up to date and store backup codes somewhere private.\n\nPause before following unexpected sign-in links. Open the service directly when a message asks you to verify an account.",
    ],
    [
        'Build a Consistent Morning Writing Habit',
        'A short writing session can become sustainable when it has a fixed cue and a small finish line. Prepare your notes the night before and begin with a modest time limit.',
        'A gentle routine for writing regularly without waiting for perfect inspiration.',
        "Choose a repeatable time and place, then make the first step easy: open the document and write one rough sentence.\n\nSet a timer for fifteen minutes. During that session, focus on getting ideas down rather than correcting every line. A clear stopping point makes it easier to return tomorrow.\n\nAt the end, leave yourself a note about the next paragraph or question to explore.",
    ],
    [
        'A Beginner-Friendly Guide to Website Backups',
        'A backup is only useful if it can be restored. Keep independent copies of both the database and uploaded files, automate the schedule, and test a restore in a safe environment.',
        'A dependable backup routine for small websites and personal projects.',
        "Identify everything needed to rebuild the site: source files, uploaded media, database content, and configuration kept outside the repository.\n\nAutomate regular copies and keep at least one copy separate from the web server. Restrict access to backup files because they can contain sensitive information.\n\nEvery so often, restore a copy into a test environment. Confirm the site works before relying on the backup during an outage.",
    ],
    [
        'Organize Digital Files Without Overthinking It',
        'A small number of consistent folders and descriptive filenames can make years of files easier to navigate. Build a structure around how you retrieve work, not around every possible future case.',
        'A lightweight file system that stays useful without becoming another project.',
        "Start with a few broad folders such as active work, reference, and archive. Add subfolders only when you notice repeated confusion.\n\nUse filenames that include a subject and useful date or version. Keep one current copy of active documents and move finished work into an archive rather than duplicating it.\n\nSchedule a brief monthly tidy-up. A simple system maintained occasionally beats a complicated structure abandoned after a week.",
    ],
    [
        'Plan Your Week in Thirty Focused Minutes',
        'A weekly review helps turn a crowded list into a realistic plan. Look at deadlines, choose a few meaningful outcomes, and leave open space for unexpected work.',
        'A concise weekly planning routine that balances priorities with flexibility.',
        "Begin by reviewing appointments, commitments, and unfinished tasks. Capture anything you might otherwise keep trying to remember.\n\nChoose three outcomes that would make the week feel productive. Break each into a clear next action and place the most important work into open calendar blocks.\n\nKeep some unscheduled time. A plan with room to adapt is more useful than a calendar filled to the edges.",
    ],
    [
        'Make Room for Deep Work in a Busy Schedule',
        'Focused work is easier when it has a protected start and a clear boundary. Short blocks, fewer notifications, and a prepared next action can make concentration more attainable.',
        'Practical ways to protect concentration even when your calendar is already full.',
        "Pick one task that benefits from uninterrupted attention and define a small, visible result. Reserve a realistic block rather than waiting for an empty day.\n\nBefore starting, close unrelated tabs and silence nonessential alerts. Keep a note nearby for distractions that can be handled later.\n\nWhen the block ends, record what you completed and what to do next. This makes it easier to resume after meetings or interruptions.",
    ],
    [
        'A Small-Tasks System That Keeps Projects Moving',
        'Projects often stall because the next action is unclear. Convert broad goals into steps that can be started in one sitting and keep the current action somewhere visible.',
        'Turn vague project goals into manageable next steps and steady progress.',
        "Write down the outcome you want, then identify the next physical action: schedule a call, draft an outline, or review a specific document.\n\nKeep a short list of actions you can complete in fifteen minutes. Use it when time is limited instead of abandoning the project until a free afternoon.\n\nReview the list weekly and remove steps that no longer matter.",
    ],
    [
        'Create a Calmer and More Useful Home Workspace',
        'A comfortable workspace does not require a full renovation. Good lighting, a supportive chair, fewer visual distractions, and a place for everyday tools can make a noticeable difference.',
        'Small improvements that make a home workspace more comfortable and focused.',
        "Start with the basics: adjust the screen to a comfortable height, position light to reduce glare, and make sure frequently used items are within reach.\n\nClear only the surface area you need for the next task. A small tray or drawer can hold items that otherwise collect around the desk.\n\nTake short movement breaks and adjust the setup as you learn what works for your body and routine.",
    ],
    [
        'Simple Ways to Spend More Time Outdoors',
        'Outdoor time is easier to maintain when it is attached to something already in your day. A short walk, a meal outside, or a nearby green route can be a useful starting point.',
        'Low-pressure ideas for adding a little more time outside to an ordinary week.',
        "Look for a nearby route that fits into an existing break or commute. Keep the plan short enough that it does not require special equipment or a large block of free time.\n\nTry taking a phone call while walking or eating lunch outside when the weather allows. Small repeated moments often work better than ambitious plans.\n\nChoose an accessible option that feels enjoyable, and adapt it to local conditions.",
    ],
    [
        'Make Reading a Relaxing Part of Your Day',
        'A book becomes easier to pick up when it is visible and the reading goal is modest. A few quiet pages at a regular time can be enough to build a lasting routine.',
        'Create a pleasant reading habit with a book you enjoy and an easy daily cue.',
        "Choose something you genuinely want to read, not something you feel you ought to finish. Leave it near a comfortable place where you already spend time.\n\nConnect reading to an existing cue, such as a cup of tea or the last few minutes before bed. Start with five pages or ten minutes.\n\nIf a book is not holding your interest, it is fine to choose another. The habit matters more than completing a particular title.",
    ],
];

$created = 0;
$skipped = 0;

try {
    $pdo->beginTransaction();

    $categoryIds = [];
    foreach (array_keys($categories) as $name) {
        $slug = slugify($name);
        $stmt = $pdo->prepare('SELECT id FROM categories WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        $categoryId = (int) $stmt->fetchColumn();
        if ($categoryId < 1) {
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
            $stmt->execute([$name, $slug]);
            $categoryId = (int) $pdo->lastInsertId();
        }
        $categoryIds[$name] = $categoryId;
    }

    $insert = $pdo->prepare(
        'INSERT INTO posts (category_id, author_id, title, slug, excerpt, body, status, published_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $exists = $pdo->prepare('SELECT id FROM posts WHERE slug = ? LIMIT 1');

    foreach ($articles as $index => [$title, $excerpt, $body]) {
        $categoryName = array_keys($categories)[intdiv($index, 3)];
        $baseSlug = slugify($title);
        $exists->execute([$baseSlug]);
        if ($exists->fetch()) {
            $skipped++;
            continue;
        }

        $slug = unique_post_slug($pdo, $title);
        $status = $index < 10 ? 'published' : 'draft';
        $publishedAt = $status === 'published'
            ? date('Y-m-d H:i:s', strtotime('-' . (12 - $index) . ' days'))
            : null;

        $insert->execute([
            $categoryIds[$categoryName],
            $authorId,
            $title,
            $slug,
            $excerpt,
            $body,
            $status,
            $publishedAt,
        ]);
        $created++;
    }

    $pdo->commit();
    echo "Seed complete: {$created} posts created, {$skipped} existing posts skipped.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Seed failed: {$e->getMessage()}\n");
    exit(1);
}
