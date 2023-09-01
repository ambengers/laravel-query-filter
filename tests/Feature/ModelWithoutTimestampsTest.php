<?php

namespace Ambengers\QueryFilter\Tests\Feature;

use Ambengers\QueryFilter\Tests\FeatureTest;
use Ambengers\QueryFilter\Tests\Models\ModelWithoutTimestamp;

class ModelWithoutTimestampsTest extends FeatureTest
{
    /** @test */
    public function no_issues_if_filtered_models_do_not_have_timestamps()
    {
        $this->withoutExceptionHandling();

        $model1 = factory(ModelWithoutTimestamp::class)->create();
        $model2 = factory(ModelWithoutTimestamp::class)->create();

        $response = $this->getJson(route('model-without-timestamps.index'))
            ->assertSuccessful();

        $results = collect($response->json());

        $this->assertTrue($results->first()['id'] === $model1->id);
        $this->assertTrue($results->last()['id'] === $model2->id);
    }
}