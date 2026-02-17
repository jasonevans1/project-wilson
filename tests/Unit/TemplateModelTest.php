<?php

use App\Enums\AssetCategory;
use App\Models\AssetTemplate;
use App\Models\TemplateGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ─── TemplateGroup model ─────────────────────────────────────────────────────

test('TemplateGroup has correct fillable fields', function () {
    $group = new TemplateGroup;

    expect($group->getFillable())->toBe([
        'name',
        'slug',
        'icon',
        'display_order',
    ]);
});

test('TemplateGroup templates relationship returns HasMany of AssetTemplate', function () {
    $group = TemplateGroup::factory()->create();
    AssetTemplate::factory(3)->create(['template_group_id' => $group->id]);

    expect($group->templates)->toHaveCount(3);
    expect($group->templates->first())->toBeInstanceOf(AssetTemplate::class);
});

test('TemplateGroup factory creates valid group with slug', function () {
    $group = TemplateGroup::factory()->create();

    expect($group->name)->toBeString();
    expect($group->slug)->toBeString();
    expect($group->display_order)->toBeInt();
});

test('TemplateGroup slug must be unique', function () {
    TemplateGroup::factory()->create(['slug' => 'kitchen']);

    expect(fn () => TemplateGroup::factory()->create(['slug' => 'kitchen']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});

// ─── AssetTemplate model ─────────────────────────────────────────────────────

test('AssetTemplate has correct fillable fields', function () {
    $template = new AssetTemplate;

    expect($template->getFillable())->toBe([
        'template_group_id',
        'name',
        'description',
        'category',
        'location',
        'display_order',
    ]);
});

test('AssetTemplate group relationship returns BelongsTo TemplateGroup', function () {
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    expect($template->group)->toBeInstanceOf(TemplateGroup::class);
    expect($template->group->id)->toBe($group->id);
});

test('AssetTemplate casts category to AssetCategory enum', function () {
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    expect($template->category)->toBeInstanceOf(AssetCategory::class);
});

test('AssetTemplate factory creates valid template', function () {
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    expect($template->name)->toBeString();
    expect($template->category)->toBeInstanceOf(AssetCategory::class);
    expect($template->location)->toBeString();
    expect($template->display_order)->toBeInt();
});

test('AssetTemplate display_order defaults to zero', function () {
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'display_order' => 0,
    ]);

    expect($template->display_order)->toBe(0);
});

test('TemplateGroup cascades delete to its templates', function () {
    $group = TemplateGroup::factory()->create();
    AssetTemplate::factory(3)->create(['template_group_id' => $group->id]);

    expect(AssetTemplate::count())->toBe(3);

    $group->delete();

    expect(AssetTemplate::count())->toBe(0);
});
