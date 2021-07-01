<?php

namespace Laravel\Scout\Tests\Unit;

use Illuminate\Support\Facades\Config;
use Laravel\Scout\ModelObserver;
use Laravel\Scout\Tests\Fixtures\SearchableModelWithSoftDeletes;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ModelObserverWithSoftDeletesTest extends TestCase
{
    protected function setUp(): void
    {
        Config::clearResolvedInstances();
        Config::shouldReceive('get')->with('scout.after_commit', m::any())->andReturn(false);
        Config::shouldReceive('get')->with('scout.soft_delete', m::any())->andReturn(true);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function test_deleted_handler_makes_model_unsearchable_when_it_should_not_be_searchable()
    {
        $observer = new ModelObserver;
        $model = m::mock(SearchableModelWithSoftDeletes::class);
        $model->shouldReceive('searchShouldUpdate')->never(); // The saved event is forced
        $model->shouldReceive('shouldBeSearchable')->andReturn(false); // Should not be searchable
        $model->shouldReceive('searchable')->never();
        $model->shouldReceive('unsearchable')->once();
        $observer->deleted($model);
    }

    public function test_deleted_handler_makes_model_searchable_when_it_should_be_searchable()
    {
        $observer = new ModelObserver;
        $model = m::mock(SearchableModelWithSoftDeletes::class);
        $model->shouldReceive('searchShouldUpdate')->never(); // The saved event is forced
        $model->shouldReceive('shouldBeSearchable')->andReturn(true); // Should be searchable
        $model->shouldReceive('searchable')->once();
        $model->shouldReceive('unsearchable')->never();
        $observer->deleted($model);
    }

    public function test_restored_handler_makes_model_searchable()
    {
        $observer = new ModelObserver;
        $model = m::mock(SearchableModelWithSoftDeletes::class);
        $model->shouldReceive('searchShouldUpdate')->never();
        $model->shouldReceive('shouldBeSearchable')->andReturn(true);
        $model->shouldReceive('searchable')->once();
        $model->shouldReceive('unsearchable')->never();
        $observer->restored($model);
    }
}
