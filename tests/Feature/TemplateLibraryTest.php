<?php

use App\Enums\AssetCategory;
use App\Livewire\Assets\TemplateLibrary;
use App\Models\Asset;
use App\Models\AssetTemplate;
use App\Models\TemplateGroup;
use App\Models\User;
use Livewire\Livewire;

// ─── US1: Browse and Select Templates ────────────────────────────────────────

test('authenticated user can access template library page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('assets.templates'))
        ->assertSuccessful();

    Livewire::test(TemplateLibrary::class)
        ->assertSuccessful();
});

test('template library displays all template groups with their templates', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create(['name' => 'Kitchen', 'slug' => 'kitchen']);
    AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
    ]);
    AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Dishwasher',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->assertSee('Kitchen')
        ->assertSee('Refrigerator')
        ->assertSee('Dishwasher');
});

test('user can select a template and selectedTemplateIds updates', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->assertSet('selectedTemplateIds', [])
        ->call('toggleTemplate', $template->id)
        ->assertSet('selectedTemplateIds', [$template->id]);
});

test('user can select multiple templates and selectedCount reflects correct count', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template1 = AssetTemplate::factory()->create(['template_group_id' => $group->id]);
    $template2 = AssetTemplate::factory()->create(['template_group_id' => $group->id]);
    $template3 = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template1->id)
        ->call('toggleTemplate', $template2->id)
        ->call('toggleTemplate', $template3->id)
        ->assertSet('selectedTemplateIds', [$template1->id, $template2->id, $template3->id])
        ->assertSee('3 selected');
});

test('user can deselect a template via toggleTemplate', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->assertSet('selectedTemplateIds', [$template->id])
        ->call('toggleTemplate', $template->id)
        ->assertSet('selectedTemplateIds', []);
});

test('already owned badge shows when user has an asset with same name as a template', function () {
    $user = User::factory()->create();
    Asset::factory()->create([
        'user_id' => $user->id,
        'name' => 'Refrigerator',
    ]);
    $group = TemplateGroup::factory()->create();
    AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->assertSee('Already owned');
});

test('unauthenticated user is redirected from template library page', function () {
    $this->get(route('assets.templates'))
        ->assertRedirect();
});

// ─── US2: Customize and Convert Templates to Assets ──────────────────────────

test('user can proceed to review step with selected templates and see editable fields', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->call('proceedToReview')
        ->assertSet('step', 2)
        ->assertSet('customizedItems.0.name', 'Refrigerator')
        ->assertSet('customizedItems.0.category', 'appliance')
        ->assertSet('customizedItems.0.location', 'Kitchen')
        ->assertSee('Review');
});

test('user can edit a customized item field during review', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->call('proceedToReview')
        ->set('customizedItems.0.name', 'My Fridge')
        ->assertSet('customizedItems.0.name', 'My Fridge');
});

test('user can remove an item from the review step', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template1 = AssetTemplate::factory()->create(['template_group_id' => $group->id]);
    $template2 = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template1->id)
        ->call('toggleTemplate', $template2->id)
        ->call('proceedToReview')
        ->assertCount('customizedItems', 2)
        ->call('removeCustomizedItem', 0)
        ->assertCount('customizedItems', 1);
});

test('confirmConversion creates assets in database with correct field values', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->call('proceedToReview')
        ->call('confirmConversion')
        ->assertSet('step', 3)
        ->assertSet('createdCount', 1);

    expect(Asset::count())->toBe(1);

    $asset = Asset::first();
    expect($asset->name)->toBe('Refrigerator');
    expect($asset->category)->toBe(AssetCategory::Appliance);
    expect($asset->location)->toBe('Kitchen');
    expect($asset->user_id)->toBe($user->id);
});

test('confirmConversion with customized values creates asset with those values', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->call('proceedToReview')
        ->set('customizedItems.0.name', 'My Custom Fridge')
        ->set('customizedItems.0.location', 'Garage')
        ->call('confirmConversion')
        ->assertSet('step', 3);

    $asset = Asset::first();
    expect($asset->name)->toBe('My Custom Fridge');
    expect($asset->location)->toBe('Garage');
});

test('confirmConversion with multiple items creates all as separate active assets', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template1 = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);
    $template2 = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Dishwasher',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template1->id)
        ->call('toggleTemplate', $template2->id)
        ->call('proceedToReview')
        ->call('confirmConversion')
        ->assertSet('createdCount', 2)
        ->assertDispatched('assets-created');

    expect(Asset::count())->toBe(2);
    expect(Asset::where('user_id', $user->id)->count())->toBe(2);
});

test('confirmConversion validates all items and shows errors if required field cleared', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->call('proceedToReview')
        ->set('customizedItems.0.name', '')
        ->call('confirmConversion')
        ->assertHasErrors(['customizedItems.0.name'])
        ->assertSet('step', 2);

    expect(Asset::count())->toBe(0);
});

test('confirmConversion is atomic — no assets created if any item fails validation', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template1 = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Refrigerator',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);
    $template2 = AssetTemplate::factory()->create([
        'template_group_id' => $group->id,
        'name' => 'Dishwasher',
        'category' => AssetCategory::Appliance,
        'location' => 'Kitchen',
    ]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template1->id)
        ->call('toggleTemplate', $template2->id)
        ->call('proceedToReview')
        ->set('customizedItems.1.name', '')
        ->call('confirmConversion')
        ->assertHasErrors(['customizedItems.1.name']);

    expect(Asset::count())->toBe(0);
});

test('user can go back from review to browse without losing selectedTemplateIds', function () {
    $user = User::factory()->create();
    $group = TemplateGroup::factory()->create();
    $template = AssetTemplate::factory()->create(['template_group_id' => $group->id]);

    $this->actingAs($user);

    Livewire::test(TemplateLibrary::class)
        ->call('toggleTemplate', $template->id)
        ->call('proceedToReview')
        ->assertSet('step', 2)
        ->call('backToBrowse')
        ->assertSet('step', 1)
        ->assertSet('selectedTemplateIds', [$template->id]);
});
