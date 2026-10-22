<?php
 
namespace Tests\Feature\Admin;
 
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
 
/**
 * Version sans authentification admin (plus de actingAs).
 *
 * Hypothèses : routes nommées admin.menu.* (restore, force-delete, toggle-availability),
 * les routes restore/force-delete utilisent ->withTrashed(), table `menus`.
 */
class MenuControllerTest extends TestCase
{
    use RefreshDatabase;
 
    private int $seq = 0;
 
    protected function setUp(): void
    {
        parent::setUp();
 
        Storage::fake('public');
    }
 
    /** Crée un article sans dépendre d'une factory. */
    private function menu(array $override = []): Menu
    {
        $n = ++$this->seq;
 
        return Menu::create(array_merge([
            'nom'          => "Article {$n}",
            'description'  => null,
            'prix'         => 1000,
            'categorie'    => Menu::CATEGORIES[0],
            'image'        => null,
            'disponible'   => true,
            'plat_du_jour' => false,
        ], $override));
    }
 
    private function payload(array $override = []): array
    {
        return array_merge([
            'nom'          => 'Thieboudienne',
            'description'  => 'Riz au poisson',
            'prix'         => 1500,
            'categorie'    => Menu::CATEGORIES[0],
            'disponible'   => '1',
            'plat_du_jour' => '1',
        ], $override);
    }
 
    // ---------- index ----------
 
    public function test_index_liste_les_articles_pagines(): void
    {
        foreach (range(1, 20) as $_) {
            $this->menu();
        }
 
        $response = $this->get(route('admin.menu.index'));
 
        $response->assertOk()->assertViewIs('admin.menu.index');
        $this->assertCount(15, $response->viewData('items'));
        $this->assertSame(Menu::CATEGORIES, $response->viewData('categories'));
        $this->assertFalse($response->viewData('corbeille'));
    }
 
    public function test_index_naffiche_pas_les_articles_supprimes_par_defaut(): void
    {
        $this->menu();
        $this->menu()->delete();
 
        $this->assertCount(1, $this->get(route('admin.menu.index'))->viewData('items'));
    }
 
    public function test_index_corbeille_affiche_uniquement_les_articles_supprimes(): void
    {
        $this->menu();
        $supprime = $this->menu();
        $supprime->delete();
 
        $response = $this->get(route('admin.menu.index', ['corbeille' => 1]));
 
        $this->assertTrue($response->viewData('corbeille'));
        $this->assertCount(1, $response->viewData('items'));
        $this->assertTrue($response->viewData('items')->first()->is($supprime));
    }
 
    public function test_index_recherche_par_nom(): void
    {
        $this->menu(['nom' => 'Pizza royale']);
        $this->menu(['nom' => 'Salade']);
 
        $items = $this->get(route('admin.menu.index', ['recherche' => 'pizza']))->viewData('items');
 
        $this->assertCount(1, $items);
        $this->assertSame('Pizza royale', $items->first()->nom);
    }
 
    public function test_index_filtre_par_categorie(): void
    {
        $this->menu(['categorie' => Menu::CATEGORIES[0]]);
        $this->menu(['categorie' => Menu::CATEGORIES[1]]);
        $this->menu(['categorie' => Menu::CATEGORIES[1]]);
 
        $items = $this->get(route('admin.menu.index', ['categorie' => Menu::CATEGORIES[1]]))
            ->viewData('items');
 
        $this->assertCount(2, $items);
    }
 
    public function test_index_rejette_une_categorie_invalide(): void
    {
        $this->get(route('admin.menu.index', ['categorie' => 'inconnue']))
            ->assertSessionHasErrors('categorie');
    }
 
    public function test_index_trie_par_categorie_puis_par_nom(): void
    {
        $this->menu(['nom' => 'B', 'categorie' => Menu::CATEGORIES[0]]);
        $this->menu(['nom' => 'A', 'categorie' => Menu::CATEGORIES[0]]);
 
        $noms = $this->get(route('admin.menu.index'))->viewData('items')->pluck('nom')->all();
 
        $this->assertSame(['A', 'B'], $noms);
    }
 
    // ---------- create / edit ----------
 
    public function test_create_affiche_le_formulaire(): void
    {
        $this->get(route('admin.menu.create'))
            ->assertOk()
            ->assertViewIs('admin.menu.create')
            ->assertViewHas('categories', Menu::CATEGORIES);
    }
 
    public function test_edit_affiche_le_formulaire(): void
    {
        $menu = $this->menu();
 
        $this->get(route('admin.menu.edit', $menu))
            ->assertOk()
            ->assertViewIs('admin.menu.edit')
            ->assertViewHas('item', fn ($i) => $i->is($menu))
            ->assertViewHas('categories', Menu::CATEGORIES);
    }
 
    // ---------- store ----------
 
    public function test_store_cree_un_article_sans_image(): void
    {
        $this->post(route('admin.menu.store'), $this->payload())
            ->assertRedirect(route('admin.menu.index'))
            ->assertSessionHas('success', 'Article ajouté au menu.');
 
        $this->assertDatabaseHas('menus', [
            'nom' => 'Thieboudienne', 'prix' => 1500, 'disponible' => true, 'plat_du_jour' => true,
        ]);
    }
 
    public function test_store_met_les_cases_decochees_a_false(): void
    {
        $data = $this->payload();
        unset($data['disponible'], $data['plat_du_jour']);
 
        $this->post(route('admin.menu.store'), $data)->assertRedirect();
 
        $menu = Menu::firstOrFail();
        $this->assertFalse((bool) $menu->disponible);
        $this->assertFalse((bool) $menu->plat_du_jour);
    }
 
    public function test_store_accepte_un_prix_a_zero_et_une_description_vide(): void
    {
        $this->post(route('admin.menu.store'), $this->payload(['prix' => 0, 'description' => null]))
            ->assertSessionHasNoErrors();
 
        $this->assertDatabaseHas('menus', ['nom' => 'Thieboudienne', 'prix' => 0]);
    }
 
    public function test_store_exige_les_champs_obligatoires(): void
    {
        $this->post(route('admin.menu.store'), [])
            ->assertSessionHasErrors(['nom', 'prix', 'categorie']);
    }
 
    /** @dataProvider donneesInvalides */
    public function test_store_valide_les_donnees(string $champ, mixed $valeur): void
    {
        $this->post(route('admin.menu.store'), $this->payload([$champ => $valeur]))
            ->assertSessionHasErrors($champ);
 
        $this->assertDatabaseCount('menus', 0);
    }
 
    public static function donneesInvalides(): array
    {
        return [
            'prix négatif'       => ['prix', -1],
            'prix décimal'       => ['prix', 12.5],
            'prix non numérique' => ['prix', 'abc'],
            'catégorie inconnue' => ['categorie', 'inconnue'],
            'nom trop long'      => ['nom', str_repeat('a', 256)],
            'description longue' => ['description', str_repeat('a', 1001)],
        ];
    }
 
    public function test_store_rejette_un_fichier_non_image(): void
    {
        $this->post(route('admin.menu.store'), $this->payload([
            'image' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('image');
    }
 
    public function test_store_rejette_une_image_de_plus_de_2_mo(): void
    {
        $this->post(route('admin.menu.store'), $this->payload([
            'image' => UploadedFile::fake()->image('gros.jpg')->size(3000),
        ]))->assertSessionHasErrors('image');
    }
 
    // ---------- update ----------
 
    public function test_update_modifie_larticle(): void
    {
        $menu = $this->menu(['nom' => 'Ancien']);
 
        $this->put(route('admin.menu.update', $menu), $this->payload(['nom' => 'Nouveau']))
            ->assertRedirect(route('admin.menu.index'))
            ->assertSessionHas('success', 'Article mis à jour.');
 
        $this->assertSame('Nouveau', $menu->fresh()->nom);
    }
 
    public function test_update_decoche_disponible_et_plat_du_jour(): void
    {
        $menu = $this->menu(['disponible' => true, 'plat_du_jour' => true]);
 
        $data = $this->payload();
        unset($data['disponible'], $data['plat_du_jour']);
 
        $this->put(route('admin.menu.update', $menu), $data)->assertRedirect();
 
        $menu->refresh();
        $this->assertFalse((bool) $menu->disponible);
        $this->assertFalse((bool) $menu->plat_du_jour);
    }
    public function test_force_delete_fonctionne_sans_image(): void
    {
        $menu = $this->menu(['image' => null]);
        $menu->delete();
 
        $this->delete(route('admin.menu.force-delete', $menu->id))->assertRedirect();
 
        $this->assertDatabaseMissing('menus', ['id' => $menu->id]);
    }
 
    // ---------- toggleAvailability ----------
 
    public function test_toggle_availability_marque_un_article_epuise(): void
    {
        $menu = $this->menu(['disponible' => true]);
 
        $this->from(route('admin.menu.index'))
            ->patch(route('admin.menu.toggle-availability', $menu))
            ->assertRedirect(route('admin.menu.index'))
            ->assertSessionHas('success', 'Article marqué comme épuisé.');
 
        $this->assertFalse((bool) $menu->fresh()->disponible);
    }
 
    public function test_toggle_availability_remet_un_article_disponible(): void
    {
        $menu = $this->menu(['disponible' => false]);
 
        $this->patch(route('admin.menu.toggle-availability', $menu))
            ->assertSessionHas('success', 'Article de nouveau disponible.');
 
        $this->assertTrue((bool) $menu->fresh()->disponible);
    }
}
 