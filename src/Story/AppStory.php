<?php

namespace App\Story;

use App\Factory\AuthorFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        AuthorFactory::createMany(50);
    }
}
