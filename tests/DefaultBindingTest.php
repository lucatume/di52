<?php

use lucatume\DI52\Container;
use PHPUnit\Framework\TestCase;

class PullRequest
{
    /**
     * @var Committer
     */
    public $committer;
    /**
     * @var Reviewer
     */
    public $reviewer;

    public function __construct(Committer $committer, Reviewer  $reviewer)
    {
        $this->committer = $committer;
        $this->reviewer = $reviewer;
    }
}

class Reviewer
{
}

class Committer
{
}

class DefaultBindingTest extends TestCase
{
    /**
     * It should resolve bindings as bound with bind by default
     *
     * @test
     */
    public function should_resolve_bindings_as_bound_with_bind_by_default()
    {
        $container = new Container();

        $pullRequestOne = $container->get(PullRequest::class);
        $pullRequestTwo = $container->get(PullRequest::class);

        $this->assertInstanceOf(PullRequest::class, $pullRequestOne);
        $this->assertInstanceOf(Committer::class, $pullRequestOne->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestOne->reviewer);
        $this->assertInstanceOf(PullRequest::class, $pullRequestTwo);
        $this->assertInstanceOf(Committer::class, $pullRequestTwo->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestTwo->reviewer);
        $this->assertNotSame($pullRequestOne, $pullRequestTwo);
        $this->assertNotSame($pullRequestOne->reviewer, $pullRequestTwo->reviewer);
        $this->assertNotSame($pullRequestOne->committer, $pullRequestTwo->committer);
    }

    /**
     * It should allow setting bindings to be resolved as singletons by default
     *
     * @test
     */
    public function should_allow_setting_bindings_to_be_resolved_as_singletons_by_default()
    {
        $container = new Container(true);

        $pullRequestOne = $container->get(PullRequest::class);
        $pullRequestTwo = $container->get(PullRequest::class);

        $this->assertInstanceOf(PullRequest::class, $pullRequestOne);
        $this->assertInstanceOf(PullRequest::class, $pullRequestTwo);
        $this->assertInstanceOf(Committer::class, $pullRequestOne->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestOne->reviewer);
        $this->assertInstanceOf(Committer::class, $pullRequestTwo->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestTwo->reviewer);
        $this->assertSame($pullRequestOne, $pullRequestTwo);
        $this->assertSame($pullRequestOne->reviewer, $pullRequestTwo->reviewer);
        $this->assertSame($pullRequestOne->committer, $pullRequestTwo->committer);
    }

    /**
     * It should allow defaulting to singleton and still binding as non singleton
     *
     * @test
     */
    public function should_allow_defaulting_to_singleton_and_still_binding_as_non_singleton()
    {
        $container = new Container(true);

        $container->bind(PullRequest::class);
        $pullRequestOne = $container->get(PullRequest::class);
        $pullRequestTwo = $container->get(PullRequest::class);

        $this->assertNotSame($pullRequestOne, $pullRequestTwo);
        $this->assertInstanceOf(PullRequest::class, $pullRequestOne);
        $this->assertInstanceOf(PullRequest::class, $pullRequestTwo);
        $this->assertInstanceOf(Committer::class, $pullRequestOne->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestOne->reviewer);
        $this->assertInstanceOf(Committer::class, $pullRequestTwo->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestTwo->reviewer);
        $this->assertSame($pullRequestOne->reviewer, $pullRequestTwo->reviewer);
        $this->assertSame($pullRequestOne->committer, $pullRequestTwo->committer);
    }

    /**
     * It should allow defaulting to singleton and still binding as non singleton middle tree
     *
     * @test
     */
    public function should_allow_defaulting_to_singleton_and_still_binding_as_non_singleton_middle_tree()
    {
        $container = new Container(true);

        $container->bind(Committer::class);
        $pullRequestOne = $container->get(PullRequest::class);
        $pullRequestTwo = $container->get(PullRequest::class);

        $this->assertSame($pullRequestOne, $pullRequestTwo);
        $this->assertInstanceOf(PullRequest::class, $pullRequestOne);
        $this->assertInstanceOf(PullRequest::class, $pullRequestTwo);
        $this->assertInstanceOf(Committer::class, $pullRequestOne->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestOne->reviewer);
        $this->assertInstanceOf(Committer::class, $pullRequestTwo->committer);
        $this->assertInstanceOf(Reviewer::class, $pullRequestTwo->reviewer);
        $this->assertSame($pullRequestOne->reviewer, $pullRequestTwo->reviewer);
        $this->assertSame($pullRequestOne->committer, $pullRequestTwo->committer);
        $this->assertNotSame($container->get(Committer::class), $pullRequestOne->committer);
        $this->assertNotSame($container->get(Committer::class), $pullRequestTwo->committer);
    }
}
