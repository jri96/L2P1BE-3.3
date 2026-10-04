<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeveringInformatieTest extends TestCase
{
    use RefreshDatabase;

    public function test_leveringinformatie_is_afgeschermd_achter_authenticatie(): void
    {
        $product = Product::where('Naam', 'Mintnopjes')->firstOrFail();

        $this->get(route('magazijn.levering', $product))->assertRedirect('/login');
    }

    public function test_scenario_1_toont_leverancier_en_leverdata_gesorteerd_op_datum_oplopend(): void
    {
        $gebruiker = User::factory()->create();
        $product = Product::where('Naam', 'Mintnopjes')->firstOrFail();

        $response = $this->actingAs($gebruiker)->get(route('magazijn.levering', $product));

        $response->assertOk();
        $response->assertSee('Levering Informatie');
        $response->assertSee('Mintnopjes');
        $response->assertSee('8719587231278');

        // Boven de tabel: leveranciersgegevens.
        $response->assertSee('Naam leverancier');
        $response->assertSee('Venco');
        $response->assertSee('Contactpersoon leverancier');
        $response->assertSee('Bert van Linge');
        $response->assertSee('Leveranciernummer');
        $response->assertSee('L1029384719');
        $response->assertSee('Mobiel');
        $response->assertSee('06-28493827');

        // Kolomkoppen van de tabel, zoals op het wireframe.
        $response->assertSee('Naam Product');
        $response->assertSee('Datum laatste levering');
        $response->assertSee('Aantal');
        $response->assertSee('Eerstvolgende levering');

        // Tabel met leverdata, gesorteerd op datum van de laatste levering oplopend.
        $response->assertSee('09-10-2024');
        $response->assertSee('18-10-2024');
        $response->assertSee('25-10-2024');

        $inhoud = $response->getContent();
        $this->assertLessThan(
            strpos($inhoud, '18-10-2024'),
            strpos($inhoud, '09-10-2024'),
            'De leverdata staan niet oplopend op datum gesorteerd.'
        );

        // Geen automatische doorverwijzing bij de happy path.
        $this->assertStringNotContainsString('setTimeout', $inhoud);
    }

    public function test_scenario_2_toont_exacte_melding_en_wordt_na_vier_seconden_doorgestuurd(): void
    {
        $gebruiker = User::factory()->create();
        $product = Product::where('Naam', 'Winegums')->firstOrFail();

        $response = $this->actingAs($gebruiker)->get(route('magazijn.levering', $product));

        $response->assertOk();
        $response->assertSee('Levering Informatie');
        $response->assertSee(
            'Er is van dit product op dit moment geen voorraad aanwezig, de verwachte eerstvolgende levering is: 30-04-2023'
        );

        $inhoud = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<td[^>]*>\s*Er is van dit product op dit moment geen voorraad aanwezig/',
            $inhoud,
            'De melding staat niet in de tabel.'
        );
        $this->assertStringContainsString('}, 4000);', $inhoud, 'De redirect na 4 seconden ontbreekt.');
        $this->assertStringContainsString(route('magazijn.index'), $inhoud, 'De redirect verwijst niet naar het magazijnoverzicht.');
    }
}
