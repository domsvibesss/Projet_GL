<?php

namespace Tests\Feature\Admin;

use App\Models\Administrateur;
use App\Models\Commande;
use App\Models\Employe;
use App\Models\Etudiant;
use App\Models\Plat;
use App\Models\Promotion;
use App\Models\Reclamation;
use App\Models\Reponse;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Les pages admin communiquent avec la base : affichage, ajout, modification, suppression.
 * Les envois imitent les formulaires des vues (action="#" = URL de la page).
 */
class PagesAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    /** Une vraie image PNG 1x1 (UploadedFile::fake()->image() exige l'extension GD). */
    private function image(string $nom = 'photo.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        return UploadedFile::fake()->createWithContent($nom, $png);
    }

    private function employe(array $override = []): Employe
    {
        return Employe::create(array_merge([
            'nom' => 'Mballa', 'prenom' => 'Jean', 'poste' => 'Cuisinier', 'tel' => '+237690112233',
        ], $override));
    }

    private function plat(array $override = []): Plat
    {
        return Plat::create(array_merge([
            'nom' => 'Poisson braisé', 'prix' => 3000, 'description' => 'Avec bâton de manioc', 'quantite' => 5,
        ], $override));
    }

    // ---------- Employés ----------

    public function test_la_liste_des_employes_vient_de_la_base(): void
    {
        $this->employe(['prenom' => 'Ruth', 'nom' => 'Ngono']);

        $this->get('/admin/employes')
            ->assertOk()
            ->assertSee('Ruth Ngono - Cuisinier')
            ->assertSee('images/admin/cuisinier.png')   // pas de photo : image du rôle
            ->assertDontSee('Tchamba');                  // plus les données de démo
    }

    public function test_le_formulaire_ajoute_un_employe(): void
    {
        $this->post('/admin/employes/ajouter', [
            'nom' => 'Ngo', 'prenom' => 'Marie', 'poste' => 'Livreur',
            'contact' => '+237 6 77 11 22 33', 'photo' => $this->image(),
        ])->assertRedirect('/admin/employes/ajouter');

        $employe = Employe::firstOrFail();
        $this->assertSame('+237 6 77 11 22 33', $employe->tel);
        $this->assertSame('actif', $employe->statut);
        Storage::disk('public')->assertExists($employe->photo);

        // Le message s'affiche dans le paragraphe de confirmation de la vue
        $this->get('/admin/employes/ajouter')->assertSee('Employé ajouté avec succès.');
    }

    public function test_un_contact_invalide_est_refuse(): void
    {
        $this->from('/admin/employes/ajouter')
            ->post('/admin/employes/ajouter', ['nom' => 'Ngo', 'prenom' => 'Marie', 'poste' => 'Livreur', 'contact' => '123'])
            ->assertRedirect('/admin/employes/ajouter')
            ->assertSessionHasErrors('contact');

        $this->assertDatabaseCount('employes', 0);
    }

    public function test_le_formulaire_modifie_un_employe(): void
    {
        $employe = $this->employe();

        $this->get("/admin/employes/{$employe->id}/modifier")->assertOk()->assertSee('Mballa');

        $this->put("/admin/employes/{$employe->id}/modifier", [
            'nom' => 'Mballa', 'prenom' => 'Jean-Paul', 'poste' => 'Manager', 'contact' => '+237690112233',
        ])->assertRedirect("/admin/employes/{$employe->id}/modifier");

        $this->assertSame('Jean-Paul', $employe->refresh()->prenom);
        $this->assertSame('Manager', $employe->poste);
    }

    public function test_le_bouton_supprimer_efface_lemploye(): void
    {
        $employe = $this->employe();

        $this->delete("/admin/employes/{$employe->id}")->assertRedirect('/admin/employes');

        $this->assertModelMissing($employe);
    }

    // ---------- Menus ----------

    public function test_la_liste_des_menus_vient_de_la_base(): void
    {
        $this->plat();

        $this->get('/admin/menus')
            ->assertOk()
            ->assertSee('Poisson braisé')
            ->assertSee('3 000 F')
            ->assertDontSee('Okok');
    }

    public function test_le_formulaire_ajoute_un_plat(): void
    {
        $this->post('/admin/menus/ajouter', [
            'nom' => 'Okok', 'prix' => 1000, 'description' => 'Feuilles d\'okok', 'photo' => $this->image(),
        ])->assertRedirect('/admin/menus/ajouter');

        $plat = Plat::firstOrFail();
        $this->assertSame('Okok', $plat->nom);
        Storage::disk('public')->assertExists($plat->image);
    }

    public function test_le_formulaire_modifie_un_plat_et_remplace_la_photo(): void
    {
        $ancienne = $this->image('a.png')->store('plats', 'public');
        $plat     = $this->plat(['image' => $ancienne]);

        $this->get("/admin/menus/{$plat->id}/modifier")->assertOk()->assertSee('Poisson braisé');

        $this->put("/admin/menus/{$plat->id}/modifier", [
            'nom' => 'Porc braisé', 'prix' => 3500, 'description' => 'Avec plantains', 'photo' => $this->image('b.png'),
        ])->assertRedirect("/admin/menus/{$plat->id}/modifier");

        $plat->refresh();
        $this->assertSame('Porc braisé', $plat->nom);
        Storage::disk('public')->assertMissing($ancienne);
        Storage::disk('public')->assertExists($plat->image);
    }

    public function test_le_bouton_supprimer_efface_le_plat(): void
    {
        $plat = $this->plat();

        $this->delete("/admin/menus/{$plat->id}")->assertRedirect('/admin/menus');

        $this->assertModelMissing($plat);
    }

    // ---------- Promotions ----------

    public function test_les_promotions_viennent_de_la_base(): void
    {
        $admin = Administrateur::create([
            'poste' => 'PDG', 'nom' => 'Tchamba', 'prenom' => 'Eric', 'tel' => '+237690000201',
            'email' => 'admin1@zeduc.test', 'mdp' => 'Password1', 'date_naissance' => '1975-03-20',
        ]);

        Promotion::create([
            'titre' => 'Full Poulet', 'prix_initial' => 1000, 'prix_promo' => 800,
            'date_debut' => now()->subDay(), 'date_fin' => now()->addDay(),
            'administrateur_id' => $admin->id,
        ]);

        $this->get('/admin/promotions')
            ->assertOk()
            ->assertSee('Full Poulet')
            ->assertSee('-20%')
            ->assertDontSee('Vendredrink');
    }

    // ---------- Statistiques ----------

    public function test_les_statistiques_admin_viennent_de_la_base(): void
    {
        $this->employe();
        $this->reclamation();   // crée aussi un étudiant actif

        $this->get('/admin/statistiques')
            ->assertOk()
            ->assertSee('Humphrey Mbeng')        // layout admin
            ->assertDontSee('185 000 F')         // plus les chiffres de démo
            ->assertDontSee('Dominique Daphne');

        // La page du gérant affiche les mêmes chiffres, avec sa propre barre de navigation
        $this->get('/statistiques')->assertOk()->assertDontSee('185 000 F')->assertDontSee('Humphrey Mbeng');
    }

    public function test_les_seeders_remplissent_les_pages_admin(): void
    {
        $this->seed();

        $this->get('/admin/promotions')->assertOk()->assertSee('Vendredrink !!!')->assertSee('-50%');
        $this->get('/admin/menus')->assertOk()->assertSee('Poulet DG')->assertSee('images/plats/poulet-dg.png');
        $this->get('/admin/statistiques')->assertOk()->assertSee('Alice Mbarga')->assertDontSee('Dominique Daphne');
        $this->get('/admin/reclamations')->assertOk()->assertSee('REC-0001');

        // Table commande_plat : on sait ce que chaque client a pris
        $commande = Commande::where('etat', Commande::ETAT_LIVREE)->oldest('id')->firstOrFail();
        $this->assertSame('1 × Poulet DG, 1 × Jus de gingembre', $commande->articles);
        $this->assertSame('CMD-0001', $commande->numero);

        // Les 4 statuts de la supervision existent dans les données de test
        $this->assertEqualsCanonicalizing(Commande::ETATS, Commande::distinct()->pluck('etat')->all());
    }

    // ---------- Réclamations ----------

    private function reclamation(): Reclamation
    {
        $etudiant = Etudiant::create([
            'nom' => 'Ngono', 'prenom' => 'Aïcha', 'tel' => '+237690000401',
            'email' => 'aicha@zeduc.test', 'mdp' => 'Password1',
        ]);

        return Reclamation::create([
            'type_reclamation' => 'Plainte',
            'description'      => 'Mon plat est arrivé froid.',
            'etudiant_id'      => $etudiant->id,
        ]);
    }

    public function test_les_reclamations_viennent_de_la_base(): void
    {
        $this->reclamation();

        $this->get('/admin/reclamations')
            ->assertOk()
            ->assertSee('Aïcha Ngono')
            ->assertSee('Mon plat est arrivé froid.')
            ->assertDontSee('Paul Essomba');
    }

    public function test_la_fenetre_traiter_enregistre_le_statut_et_la_reponse(): void
    {
        $reclamation = $this->reclamation();

        $this->put("/admin/reclamations/{$reclamation->id}", [
            'statut'  => 'resolue',
            'reponse' => 'Un jus vous est offert.',
        ])->assertRedirect('/admin/reclamations');

        $this->assertSame(Reclamation::STATUT_ACCEPTE, $reclamation->refresh()->statut);
        $this->assertSame('Un jus vous est offert.', $reclamation->reponses()->firstOrFail()->contenu);
        $this->assertSame(Reponse::STATUT_VALIDEE, $reclamation->reponses()->firstOrFail()->statut);
    }

    // ---------- Paramètres ----------

    public function test_les_parametres_sont_enregistres_et_reaffiches(): void
    {
        $this->get('/admin/parametres')->assertOk()->assertSee('16:45');   // valeurs de la maquette

        $horaires = array_fill_keys(Setting::JOURS, ['ouverture' => '08:00', 'fermeture' => '20:00', 'ouvert' => '1']);
        $horaires['dimanche'] = ['ouverture' => '', 'fermeture' => '', 'ouvert' => '0'];

        $this->post('/admin/parametres', [
            'horaires'                => $horaires,
            'taux_point'              => 500,
            'validite_mois'           => 6,
            'politique_fidelite'      => 'Nouvelle politique de fidélité',
            'politique_parrainage'    => 'Parrainage',
            'politique_remboursement' => 'Remboursement',
        ])->assertRedirect('/admin/parametres');

        $this->get('/admin/parametres')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('Nouvelle politique de fidélité')
            ->assertSee('12 500 FCFA');   // aperçu : 25 points x 500
    }
}
