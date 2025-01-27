<?php

namespace WebDevEtc\BlogEtc\Tests\Unit;

use Mockery;
use Str;
use View;
use WebDevEtc\BlogEtc\Models\Post;
use WebDevEtc\BlogEtc\Services\PostsService;
use WebDevEtc\BlogEtc\Tests\TestCase;

/**
 * Class PostsControllerTest.
 *
 * Test the posts controller.
 */
class PostsControllerTest extends TestCase
{
    /**
     * Setup the feature test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->featureSetUp();
    }

    /**
     * Test the index page loads.
     *
     * This is a basic test that just checks the correct view is loaded, correct status is returned.
     */
    public function testIndex(): void
    {
        $url = route('blogetc.index');

        $this->withoutExceptionHandling();

        // As this package does not include layouts.app, it is easier to just mock the whole View part, and concentrate
        // only on the package code in the controller. Would be interested if anyone has a suggestion on better way
        // to test this.
        View::shouldReceive('share')
            ->once()
            ->shouldReceive('make')
            ->once()
            ->with('blogetc::index', Mockery::type('array'), Mockery::type('array'));

        // also see assertions made in the mocked view.
        $response = $this->get($url);

        $response->assertOk();
    }

    /**
     * Test the show page loads.
     *
     * It is a bit awkward to test this as a package.
     * This will get refactored into a neater test.
     */
    public function testShow(): void
    {
        $slug = Str::random();
        $post = new Post(['slug' => $slug]);
        $post->save();
        $url = route('blogetc.show', $post->slug);

        $this->mock(PostsService::class, static function ($mock) use ($post) {
            $mock->shouldReceive('findBySlug')->once()->andReturn($post);
        });
        $this->withoutExceptionHandling();

        // As this package does not include layouts.app, it is easier to just mock the whole View part, and concentrate
        // only on the package code in the controller. Would be interested if anyone has a suggestion on better way
        // to test this.
        $viewMock = $this->mock(\Illuminate\View\View::class);
        $viewMock->shouldReceive('render');
        View::shouldReceive('share')
            ->once()
            ->shouldReceive('make')
            ->once()
            ->with('blogetc::single_post', Mockery::type('array'), Mockery::type('array'))
            ->andReturn($viewMock)
            ->shouldReceive('exists')
            ->shouldReceive('replaceNamespace');

        // also see assertions made in the mocked view.
        $response = $this->get($url);

        $response->assertOk();
    }
}
