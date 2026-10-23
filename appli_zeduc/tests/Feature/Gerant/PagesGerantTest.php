<?php

namespace Tests\Feature\Gerant;

use App\Models\Employe;
use App\Models\Reclamation;
use App\Models\Reponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Les pages du gérant communiquent avec la base (la même que l'administrateur).
 * Les données viennent des seeders de l'équipe.
 */
class PagesGerantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_la_supervision_affiche_les_commandes_de_la_base(): void
    {
        $this->get('/supervision')
            ->assertOk()
            ->assertSee('CMD-0001')
            ->assertSee('1 × Poulet DG, 1 × Jus de gingembre')   // table commande_plat
            ->assertSee('Alice Mbarga')
            ->assertDontSee('Marie K.');                          // plus les données de démo
    }

    public function test_la_liste_des_employes_vient_de_la_base(): void
    {
        $this->get('/employes')
            ->assertOk()
            ->assertSee('EMP-0001')
            ->assertSee('employe1@zeduc.test')
            ->assertSee('EMP-0005')        // identifiant proposé pour le prochain employé
            ->assertDontSee('Sandrine');
    }

    public function test_le_gerant_cree_un_employe(): void
    {
        $this->post('/employes', [
            'identifiant'    => 'EMP-0005',
            'nom'            => 'Mballa',
            'prenom'         => 'Ruth',
            'telephone'      => '6 77 12 40 18',
            'email'          => 'ruth.mballa@exemple.com',
            'poste'          => 'Livreur',
            'date_naissance' => '1999-04-12',
            'mot_de_passe'   => 'Provisoire1',
        ])->assertRedirect('/employes');

        $employe = Employe::where('email', 'ruth.mballa@exemple.com')->firstOrFail();
        $this->assertSame('6 77 12 40 18', $employe->tel);
        $this->assertTrue(Hash::check('Provisoire1', $employe->mdp));

        // Même base : l'administrateur le voit aussi
        $this->get('/admin/employes')->assertSee('Ruth Mballa - Livreur');
    }

    public function test_un_mot_de_passe_sans_majuscule_est_refuse(): void
    {
        $this->from('/employes')->post('/employes', [
            'nom' => 'Mballa', 'prenom' => 'Ruth', 'telephone' => '677124018', 'email' => 'ruth@exemple.com',
            'poste' => 'Livreur', 'date_naissance' => '1999-04-12', 'mot_de_passe' => 'provisoire1',
        ])->assertRedirect('/employes')->assertSessionHasErrors('mot_de_passe');
    }

    public function test_les_statistiques_du_gerant_viennent_de_la_base(): void
    {
        $this->get('/statistiques')
            ->assertOk()
            ->assertSee('Supervision des commandes')   // barre de navigation du gérant
            ->assertDontSee('185 000 F')
            ->assertSee('Alice Mbarga');
    }

    public function test_le_gerant_repond_a_une_reclamation(): void
    {
        $this->get('/reclamations')->assertOk()->assertSee('REC-0001')->assertDontSee('Jennifer Bela');

        $reclamation = Reclamation::firstOrFail();

        $this->post("/reclamations/{$reclamation->id}/reponse", ['contenu' => 'Nous sommes désolés.'])
            ->assertRedirect('/reclamations');

        $reponse = $reclamation->reponses()->latest('id')->firstOrFail();
        $this->assertSame('Nous sommes désolés.', $reponse->contenu);
        $this->assertSame(Reponse::STATUT_VALIDEE, $reponse->statut);

        $this->get('/reclamations')->assertSee('Réponse envoyée à');
    }
}
