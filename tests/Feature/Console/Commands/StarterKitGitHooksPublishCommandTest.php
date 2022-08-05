<?php

namespace Fligno\StarterKit\Feature\Console\Commands;

use Tests\TestCase;

/**
 * Class StarterKitGitHooksPublishCommandTest
 *
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 */
class StarterKitGitHooksPublishCommandTest extends TestCase
{
    /**
     * Example Test
     *
     * @test
     */
    public function example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
