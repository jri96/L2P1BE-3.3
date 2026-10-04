<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllergenenInformatieTest extends TestCase
{
    use RefreshDatabase;

    public function test_allergeneninformatie_is_afgeschermd_achter_authenticatie(): void
    {
        $product = Product::where('Naam', 'Zoute Ruitjes')->firstOrFail();

        $this->get(route('magazijn.allergenen', $product))->assertRedirect('/login');
    }

    public function test_scenario_1_toont_productgegevens_en_allergenen_gesorteerd_op_naam_oplopend(): void
    {
        $gebruiker = User::factory()->create();
        $product = Product::where('Naam', 'Zoute Ruitjes')->firstOrFail();

        $response = $this->actingAs($gebruiker)->get(route('magazijn.allergenen', $product));

        $response->assertOk();
        $response->assertSee('Overzicht Allergenen');

        // Boven de tabel: naam product en barcode.
        $response->assertSee('Naam Product');
        $response->assertSee('Zoute Ruitjes');
        $response->assertSee('Barcode');
        $response->assertSee('8719587323256');

        // Tabel met alle allergenen, gesorteerd op naam oplopend.
        $response->assertSee('Naam');
        $response->assertSee('Omschrijving');
        $response->assertSee('Gluten');
        $response->assertSee('Lactose');
        $response->assertSee('Soja');

        $inhoud = $response->getContent();
        $this->assertLessThan(strpos($inhoud, 'Lactose'), strpos($inhoud, 'Gluten'));
        $this->assertLessThan(strpos($inhoud, 'Soja'), strpos($inhoud, 'Lactose'));

        // Geen automatische doorverwijzing bij de happy path.
        $this->assertStringNotContainsString('setTimeout', $inhoud);
    }

    public function test_scenario_2_toont_exacte_melding_en_wordt_na_vier_seconden_doorgestuurd(): void
    {
        $gebruiker = User::factory()->create();
        $product = Product::where('Naam', 'Cola Flesjes')->firstOrFail();

        $response = $this->actingAs($gebruiker)->get(route('magazijn.allergenen', $product));

        $response->assertOk();
        $response->assertSee('Overzicht Allergenen');
        $response->assertSee(
            'In dit product zitten geen stoffen die een allergische reactie kunnen veroorzaken'
        );

        $inhoud = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<td[^>]*>\s*In dit product zitten geen stoffen/',
            $inhoud,
            'De melding staat niet in de tabel.'
        );
        $this->assertStringContainsString('}, 4000);', $inhoud, 'De redirect na 4 seconden ontbreekt.');
        $this->assertStringContainsString(route('magazijn.index'), $inhoud, 'De redirect verwijst niet naar het magazijnoverzicht.');
    }
}
