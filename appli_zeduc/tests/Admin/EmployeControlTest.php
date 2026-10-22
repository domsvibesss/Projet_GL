<?php
 
namespace Tests\Feature\Admin;
 
use App\Models\Administrateur;
use App\Models\Employe;
use App\Models\Etudiant;
use App\Models\Gerant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class EmployeControllerTest extends TestCase
{
    use RefreshDatabase;
 
    private int $seq = 0;
 
    protected function setUp(): void
    {
        parent::setUp();
 
        // Connexion via le guard 'admin', comme AuthController::loginAs()
        $this->actingAs(Administrateur::factory()->create(), 'admin');
    }
 
    /** Crée un employé sans dépendre d'une factory. */
    private function employe(array $override = []): Employe
    {
        $n = ++$this->seq;
 
        return Employe::create(array_merge([
            'poste'          => 'Serveur',
            'nom'            => "Nom{$n}",
            'prenom'         => "Prenom{$n}",
            'tel'            => '77000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'email'          => "employe{$n}@example.com",
            'date_naissance' => '1995-01-01',
            'mdp'            => 'Password1',      // haché par le cast
            'status'         => 'actif',
        ], $override));
    }
 
    private function payload(array $override = []): array
    {
        return array_merge([
            'poste'          => 'Caissier',
            'nom'            => 'Diop',
            'prenom'         => 'Awa',
            'tel'            => '770000001',
            'email'          => 'awa@example.com',
            'date_naissance' => '1995-05-10',
            'password'       => 'Secret123',
        ], $override);
    }
 
    // ---------- Accès / guards ----------
 
    public function test_un_visiteur_non_connecte_est_redirige_vers_la_connexion(): void
    {
        Auth::guard('admin')->logout();
 
        $this->get(route('admin.employees.index'))->assertRedirect(route('login'));
        $this->post(route('admin.employees.store'), $this->payload())->assertRedirect(route('login'));
        $this->assertDatabaseCount('employes', 0);
    }
 
    public function test_un_employe_connecte_na_pas_acces_a_ladministration(): void
    {
        Auth::guard('admin')->logout();
        $this->actingAs($this->employe(), 'employe');
 
        $status = $this->get(route('admin.employees.index'))->getStatusCode();
 
        $this->assertContains($status, [302, 403]);
    }
 
    public function test_un_etudiant_connecte_na_pas_acces_a_ladministration(): void
    {
        Auth::guard('admin')->logout();
        $this->actingAs(Etudiant::factory()->create(), 'etudiant');
 
        $this->assertContains($this->get(route('admin.employees.index'))->getStatusCode(), [302, 403]);
    }
 
    // ---------- index ----------
 
    public function test_index_affiche_la_liste_paginee(): void
    {
        foreach (range(1, 20) as $_) {
            $this->employe();
        }
 
        $response = $this->get(route('admin.employees.index'));
 
        $response->assertOk()->assertViewIs('admin.employees.index');
        $this->assertCount(15, $response->viewData('employees'));
        $this->assertSame(['actif', 'inactif'], $response->viewData('statuses'));
    }
 
    public function test_index_filtre_par_statut(): void
    {
        $this->employe(['status' => 'actif']);
        $this->employe(['status' => 'inactif']);
        $this->employe(['status' => 'inactif']);
 
        $response = $this->get(route('admin.employees.index', ['status' => 'inactif']));
 
        $this->assertCount(2, $response->viewData('employees'));
    }
 
    public function test_index_rejette_un_statut_invalide(): void
    {
        $this->get(route('admin.employees.index', ['status' => 'bidon']))
            ->assertSessionHasErrors('status');
    }
 
    public function test_index_recherche_sur_nom_prenom_email_tel_et_poste(): void
    {
        $this->employe(['nom' => 'Zebulon']);
        $this->employe(['prenom' => 'Zebulon']);
        $this->employe(['email' => 'zebulon@test.com']);
        $this->employe(['tel' => '99zebulon']);
        $this->employe(['poste' => 'Zebulon chef']);
        $this->employe(['nom' => 'Autre', 'prenom' => 'Autre', 'poste' => 'Autre']);
 
        $response = $this->get(route('admin.employees.index', ['search' => 'zebulon']));
 
        $this->assertCount(5, $response->viewData('employees'));
    }
 
    // ---------- create ----------
 
    public function test_create_affiche_le_formulaire(): void
    {
        $this->get(route('admin.employees.create'))
            ->assertOk()->assertViewIs('admin.employees.create');
    }
 
    // ---------- store ----------
 
    public function test_store_cree_un_employe_actif_avec_mot_de_passe_hache(): void
    {
        $this->post(route('admin.employees.store'), $this->payload())
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success', 'Compte employé créé.');
 
        $employe = Employe::where('email', 'awa@example.com')->firstOrFail();
 
        $this->assertSame('actif', $employe->status);
        $this->assertNotSame('Secret123', $employe->mdp);
        $this->assertTrue(Hash::check('Secret123', $employe->mdp));
    }
 
    public function test_un_employe_cree_peut_se_connecter_via_le_formulaire_de_connexion(): void
    {
        $this->post(route('admin.employees.store'), $this->payload());
 
        Auth::guard('admin')->logout();
        $this->app['session']->flush();
 
        $this->post('/login', ['email' => 'awa@example.com', 'password' => 'Secret123'])
            ->assertRedirect(route('employe.commandes.index'));
 
        $this->assertAuthenticated('employe');
    }
 
    public function test_store_exige_les_champs_obligatoires(): void
    {
        $this->post(route('admin.employees.store'), [])
            ->assertSessionHasErrors([
                'poste', 'nom', 'prenom', 'tel', 'email', 'date_naissance', 'password',
            ]);
 
        $this->assertDatabaseCount('employes', 0);
    }
 
    /** @dataProvider motsDePasseInvalides */
    public function test_store_rejette_les_mots_de_passe_faibles(string $password): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['password' => $password]))
            ->assertSessionHasErrors('password');
    }
 
    public static function motsDePasseInvalides(): array
    {
        return [
            'trop court'     => ['Ab1'],
            'sans majuscule' => ['secret123'],
            'sans chiffre'   => ['SecretPass'],
        ];
    }
 
    public function test_store_affiche_le_message_personnalise_pour_le_mot_de_passe(): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['password' => 'secret123']))
            ->assertSessionHasErrors([
                'password' => 'Le mot de passe doit contenir au moins une majuscule et un chiffre.',
            ]);
    }
 
    public function test_store_rejette_une_date_de_naissance_du_jour_ou_future(): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['date_naissance' => now()->toDateString()]))
            ->assertSessionHasErrors('date_naissance');
 
        $this->post(route('admin.employees.store'), $this->payload(['date_naissance' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors('date_naissance');
    }
 
    public function test_store_rejette_un_email_invalide(): void
    {
        $this->post(route('admin.employees.store'), $this->payload(['email' => 'pas-un-email']))
            ->assertSessionHasErrors('email');
    }
 
    public function test_store_rejette_un_telephone_deja_utilise(): void
    {
        $this->employe(['tel' => '770000001']);
 
        $this->post(route('admin.employees.store'), $this->payload())
            ->assertSessionHasErrors('tel');
    }
 
    public function test_store_rejette_un_email_deja_utilise_par_un_administrateur(): void
    {
        Administrateur::factory()->create(['email' => 'awa@example.com']);
 
        $this->post(route('admin.employees.store'), $this->payload())
            ->assertSessionHasErrors(['email' => 'Cette adresse email est déjà utilisée.']);
    }
 
    public function test_store_rejette_un_email_deja_utilise_par_un_gerant(): void
    {
        Gerant::factory()->create(['email' => 'awa@example.com']);
 
        $this->post(route('admin.employees.store'), $this->payload())
            ->assertSessionHasErrors('email');
    }
 
    public function test_store_rejette_un_email_deja_utilise_par_un_etudiant(): void
    {
        Etudiant::factory()->create(['email' => 'awa@example.com']);
 
        $this->post(route('admin.employees.store'), $this->payload())
            ->assertSessionHasErrors('email');
    }
 
    public function test_store_rejette_un_email_deja_utilise_par_un_employe(): void
    {
        $this->employe(['email' => 'awa@example.com']);
 
        $this->post(route('admin.employees.store'), $this->payload(['tel' => '770009999']))
            ->assertSessionHasErrors('email');
 
        $this->assertDatabaseCount('employes', 1);
    }
 
    // ---------- edit / update ----------
 
    public function test_edit_affiche_le_formulaire(): void
    {
        $employe = $this->employe();
 
        $this->get(route('admin.employees.edit', $employe))
            ->assertOk()
            ->assertViewIs('admin.employees.edit')
            ->assertViewHas('employee', fn ($e) => $e->is($employe))
            ->assertViewHas('statuses', ['actif', 'inactif']);
    }
 
    public function test_update_modifie_lemploye_et_conserve_lancien_mot_de_passe(): void
    {
        $employe = $this->employe(['mdp' => 'Ancien123']);
        $ancienHash = $employe->fresh()->mdp;
 
        $this->put(route('admin.employees.update', $employe), $this->payload([
            'nom' => 'Nouveau', 'password' => '', 'status' => 'inactif',
        ]))
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success', 'Compte employé mis à jour.');
 
        $employe->refresh();
        $this->assertSame('Nouveau', $employe->nom);
        $this->assertSame('inactif', $employe->status);
        $this->assertSame($ancienHash, $employe->mdp);
        $this->assertTrue(Hash::check('Ancien123', $employe->mdp));
    }
 
    public function test_update_change_le_mot_de_passe_quand_il_est_fourni(): void
    {
        $employe = $this->employe();
 
        $this->put(route('admin.employees.update', $employe), $this->payload([
            'password' => 'Nouveau456', 'status' => 'actif',
        ]))->assertRedirect();
 
        $this->assertTrue(Hash::check('Nouveau456', $employe->fresh()->mdp));
    }
 
    public function test_update_rejette_un_nouveau_mot_de_passe_faible(): void
    {
        $employe = $this->employe();
 
        $this->put(route('admin.employees.update', $employe), $this->payload([
            'password' => 'faible', 'status' => 'actif',
        ]))->assertSessionHasErrors('password');
    }
 
    public function test_update_autorise_de_garder_son_propre_email_et_telephone(): void
    {
        $employe = $this->employe(['email' => 'awa@example.com', 'tel' => '770000001']);
 
        $this->put(route('admin.employees.update', $employe), $this->payload([
            'password' => null, 'status' => 'actif',
        ]))->assertSessionHasNoErrors();
    }
 
    public function test_update_rejette_lemail_ou_le_tel_dun_autre_employe(): void
    {
        $this->employe(['email' => 'autre@example.com', 'tel' => '780000000']);
        $employe = $this->employe();
 
        $this->put(route('admin.employees.update', $employe), $this->payload([
            'email' => 'autre@example.com', 'tel' => '780000000', 'status' => 'actif', 'password' => null,
        ]))->assertSessionHasErrors(['email', 'tel']);
    }
 
    public function test_update_rejette_lemail_dun_compte_dune_autre_table(): void
    {
        Gerant::factory()->create(['email' => 'gerant@example.com']);
        $employe = $this->employe();
 
        $this->put(route('admin.employees.update', $employe), $this->payload([
            'email' => 'gerant@example.com', 'status' => 'actif', 'password' => null,
        ]))->assertSessionHasErrors('email');
    }
 
    public function test_update_exige_un_statut_valide(): void
    {
        $employe = $this->employe();
 
        $this->put(route('admin.employees.update', $employe), $this->payload(['status' => 'bidon', 'password' => null]))
            ->assertSessionHasErrors('status');
 
        $this->put(route('admin.employees.update', $employe), $this->payload(['password' => null]))
            ->assertSessionHasErrors('status');
    }
 
    // ---------- toggleStatus ----------
 
    public function test_toggle_status_desactive_un_compte_actif(): void
    {
        $employe = $this->employe(['status' => 'actif']);
 
        $this->from(route('admin.employees.index'))
            ->patch(route('admin.employees.toggle-status', $employe))
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success', 'Compte désactivé.');
 
        $this->assertSame('inactif', $employe->fresh()->status);
    }
 
    public function test_toggle_status_active_un_compte_inactif(): void
    {
        $employe = $this->employe(['status' => 'inactif']);
 
        $this->patch(route('admin.employees.toggle-status', $employe))
            ->assertSessionHas('success', 'Compte activé.');
 
        $this->assertSame('actif', $employe->fresh()->status);
    }
 
    public function test_un_employe_desactive_ne_peut_plus_se_connecter(): void
    {
        $employe = $this->employe(['email' => 'bob@example.com', 'mdp' => 'Password1']);
 
        $this->patch(route('admin.employees.toggle-status', $employe));
 
        Auth::guard('admin')->logout();
 
        $this->post('/login', ['email' => 'bob@example.com', 'password' => 'Password1'])
            ->assertSessionHasErrors(['email' => 'Ce compte est désactivé. Contactez le restaurant.']);
 
        $this->assertGuest('employe');
    }
 
    // ---------- destroy ----------
 
    public function test_destroy_supprime_lemploye(): void
    {
        $employe = $this->employe();
 
        $this->delete(route('admin.employees.destroy', $employe))
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHas('success', 'Compte employé supprimé.');
 
        $this->assertModelMissing($employe);
    }
}