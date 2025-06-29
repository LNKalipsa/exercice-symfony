<?php

namespace App\Story;

use App\Factory\AuthorFactory;
use App\Factory\CommentFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        AuthorFactory::createMany(50);
        CommentFactory::createMany(30);
    }
}
