<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
    }

    public function test_duplicate_category_name_is_rejected_by_validation(): void
    {
        MenuCategory::create(['name' => 'Rice Meals', 'sort_order' => 1, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->post('/menu-categories', [
            'name' => 'Rice Meals',
            'sort_order' => 2,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, MenuCategory::where('name', 'Rice Meals')->count());
    }

    public function test_duplicate_category_name_is_rejected_at_the_database_level(): void
    {
        MenuCategory::create(['name' => 'Rice Meals', 'sort_order' => 1, 'is_active' => true]);

        $this->expectException(QueryException::class);

        MenuCategory::create(['name' => 'Rice Meals', 'sort_order' => 2, 'is_active' => true]);
    }

    public function test_updating_a_category_to_its_own_unchanged_name_is_allowed(): void
    {
        $category = MenuCategory::create(['name' => 'Rice Meals', 'sort_order' => 1, 'is_active' => true]);

        $response = $this->actingAs($this->admin)->put("/menu-categories/{$category->id}", [
            'name' => 'Rice Meals',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors('name');
    }

    public function test_inactive_categories_are_excluded_from_the_new_item_category_dropdown(): void
    {
        $active = MenuCategory::create(['name' => 'Rice Meals', 'sort_order' => 1, 'is_active' => true]);
        $inactive = MenuCategory::create(['name' => 'Discontinued', 'sort_order' => 2, 'is_active' => false]);

        $response = $this->actingAs($this->admin)->get('/menu-items/create');

        $response->assertViewHas('categories', function ($categories) use ($active, $inactive) {
            return $categories->contains('id', $active->id) && ! $categories->contains('id', $inactive->id);
        });
    }

    public function test_submitting_an_inactive_category_when_creating_an_item_is_rejected(): void
    {
        $inactive = MenuCategory::create(['name' => 'Discontinued', 'sort_order' => 1, 'is_active' => false]);

        $response = $this->actingAs($this->admin)->post('/menu-items', [
            'menu_category_id' => $inactive->id,
            'name' => 'Old Special',
            'price' => '100.00',
        ]);

        $response->assertSessionHasErrors('menu_category_id');
        $this->assertSame(0, MenuItem::where('name', 'Old Special')->count());
    }

    public function test_editing_an_item_still_shows_its_own_now_inactive_category_and_keeps_it_on_save(): void
    {
        $inactive = MenuCategory::create(['name' => 'Discontinued', 'sort_order' => 1, 'is_active' => false]);
        $item = MenuItem::create([
            'menu_category_id' => $inactive->id,
            'name' => 'Old Special',
            'price' => '100.00',
        ]);

        $editResponse = $this->actingAs($this->admin)->get("/menu-items/{$item->id}/edit");
        $editResponse->assertViewHas('categories', fn ($categories) => $categories->contains('id', $inactive->id));

        $updateResponse = $this->actingAs($this->admin)->put("/menu-items/{$item->id}", [
            'menu_category_id' => $inactive->id,
            'name' => 'Old Special',
            'price' => '120.00',
        ]);

        $updateResponse->assertSessionDoesntHaveErrors('menu_category_id');
        $this->assertSame('120.00', $item->fresh()->price);
    }
}
